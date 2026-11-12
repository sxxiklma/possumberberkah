<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\PosSaleService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        protected PosSaleService $posSaleService,
        protected UnitConversionService $conversionService
    ) {}

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        // Products for POS with complete units and pricing
        $products = Product::with(['category', 'baseUnit', 'productUnits.unit', 'batches'])
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                    'current_stock' => $product->current_stock,
                    'stock_formatted' => $product->stock_formatted,
                    'base_unit_id' => $product->base_unit_id,
                    'base_unit_code' => $product->baseUnit?->code ?? 'PCS',
                    'buy_price' => (float) $product->buy_price,
                    'selling_price' => (float) $product->selling_price,
                    'wholesale_price' => (float) $product->wholesale_price,
                    'units' => $this->conversionService->getProductUnitsWithPricing($product),
                ];
            });

        $taxPercentage = (float) Setting::get('tax_percentage', 0);
        $storeName = Setting::get('store_name', 'TOKO SUMBER BERKAH');

        return view('pos.index', compact('categories', 'customers', 'products', 'taxPercentage', 'storeName'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'price_type' => ['required', 'in:retail,wholesale'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $sale = $this->posSaleService->createSale($validated, auth()->user());

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$sale->invoice_number} berhasil diproses!",
                'sale' => $sale,
                'receipt_url' => route('pos.receipt', $sale),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product.baseUnit', 'items.unit', 'customer', 'user']);
        $store = [
            'name' => Setting::get('store_name', 'TOKO SUMBER BERKAH'),
            'address' => Setting::get('store_address', 'Jl. Pasar Besar No. 88, Cibinong'),
            'phone' => Setting::get('store_phone', '0812-3456-7890'),
            'footer' => Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!'),
        ];

        return view('pos.receipt', compact('sale', 'store'));
    }

    public function history(Request $request)
    {
        $sales = Sale::with(['customer', 'user'])->latest('sale_date')->paginate(15);
        return view('pos.history', compact('sales'));
    }

    public function void(Request $request, Sale $sale)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $this->posSaleService->voidSale($sale, auth()->user(), $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => "Faktur {$sale->invoice_number} berhasil dibatalkan dan stok telah dikembalikan ke gudang.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

