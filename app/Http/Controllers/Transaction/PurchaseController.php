<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected UnitConversionService $conversionService
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');
        $supplierId = $request->get('supplier_id');
        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');

        $query = Purchase::with(['supplier', 'warehouse', 'user'])->withCount('items');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        $purchases = $query->latest('purchase_date')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('transactions.purchases.index', compact('purchases', 'suppliers', 'search', 'supplierId', 'status', 'paymentStatus'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'productUnits.unit', 'location'])->where('is_active', true)->get();

        return view('transactions.purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'string', 'in:paid,partial,unpaid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'auto_receive' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ], [
            'invoice_image.image' => 'File bukti invoice harus berupa file gambar valid.',
            'invoice_image.mimes' => 'Format file bukti invoice yang didukung: JPG, JPEG, PNG, atau WEBP.',
            'invoice_image.max' => 'Ukuran file bukti invoice tidak boleh melebihi 10 MB.',
        ]);

        if ($request->hasFile('invoice_image')) {
            $validated['invoice_image'] = $request->file('invoice_image')->store('purchases/invoices', 'public');
        }

        try {
            $purchase = $this->purchaseService->createPurchase($validated, auth()->user());

            return redirect()->route('transactions.purchases.show', $purchase)
                ->with('success', "Transaksi Barang Masuk {$purchase->purchase_number} berhasil dibuat!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function uploadInvoice(Request $request, Purchase $purchase)
    {
        $request->validate([
            'invoice_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ], [
            'invoice_image.required' => 'Pilih file foto bukti invoice terlebih dahulu.',
            'invoice_image.image' => 'File bukti invoice harus berupa file gambar valid.',
            'invoice_image.mimes' => 'Format file bukti invoice yang didukung: JPG, JPEG, PNG, atau WEBP.',
            'invoice_image.max' => 'Ukuran file bukti invoice tidak boleh melebihi 10 MB.',
        ]);

        if ($purchase->invoice_image && Storage::disk('public')->exists($purchase->invoice_image)) {
            Storage::disk('public')->delete($purchase->invoice_image);
        }

        $imagePath = $request->file('invoice_image')->store('purchases/invoices', 'public');
        $purchase->invoice_image = $imagePath;
        $purchase->save();

        return back()->with('success', 'Foto bukti invoice berhasil diunggah!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'warehouse',
            'user',
            'items.product.baseUnit',
            'items.unit',
            'items.location',
            'payable',
            'payments.user'
        ]);

        return view('transactions.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase)
    {
        try {
            $this->purchaseService->receivePurchase($purchase, auth()->user());

            return back()->with('success', "Penerimaan barang PO {$purchase->purchase_number} berhasil dikonfirmasi! Stok dan batch FIFO telah diperbarui.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'warehouse', 'user', 'items.product.baseUnit', 'items.unit']);
        return view('transactions.purchases.print', compact('purchase'));
    }
}

