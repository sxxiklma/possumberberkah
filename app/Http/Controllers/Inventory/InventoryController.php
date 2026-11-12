<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Ringkasan Stok Gudang (Multi-Satuan & Nilai Persediaan)
     */
    public function stockSummary(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id');

        $query = Product::with(['category', 'baseUnit', 'productUnits.unit', 'location.warehouse', 'batches'])
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        // Total inventory value
        $totalValuation = (float) InventoryBatch::where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->selectRaw('SUM(available_quantity * cost_price) as val')
            ->value('val') ?? 0;

        return view('inventory.stock_summary', compact('products', 'categories', 'warehouses', 'search', 'categoryId', 'warehouseId', 'totalValuation'));
    }

    /**
     * Kartu Stok (Stock Card Ledger)
     */
    public function stockCard(Request $request, ?int $productId = null)
    {
        $selectedProductId = $productId ?? $request->get('product_id');
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProduct = null;
        $movements = collect();

        if ($selectedProductId) {
            $selectedProduct = Product::with(['baseUnit', 'productUnits.unit', 'location.warehouse'])
                ->findOrFail($selectedProductId);

            $movements = StockMovement::with(['user', 'warehouse', 'batch'])
                ->where('product_id', $selectedProductId)
                ->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->latest()
                ->paginate(25)
                ->withQueryString();
        }

        return view('inventory.stock_card', compact('products', 'selectedProduct', 'selectedProductId', 'movements', 'startDate', 'endDate'));
    }

    /**
     * Stok Minimum (Peringatan Stok Menipis & Habis)
     */
    public function minStock(Request $request)
    {
        $products = Product::with(['category', 'baseUnit', 'productUnits.unit', 'location.warehouse'])
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->current_stock <= $p->min_stock)
            ->sortBy('current_stock');

        return view('inventory.min_stock', compact('products'));
    }

    /**
     * Stok Kedaluwarsa (Expired & Near Expiry Batches)
     */
    public function expiredStock(Request $request)
    {
        $today = now()->toDateString();
        $nearExpiryThreshold = now()->addDays(60)->toDateString();

        // Expired batches
        $expiredBatches = InventoryBatch::with(['product.baseUnit', 'warehouse', 'location'])
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Expiring soon batches (within 60 days)
        $expiringSoonBatches = InventoryBatch::with(['product.baseUnit', 'warehouse', 'location'])
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $nearExpiryThreshold])
            ->orderBy('expiry_date', 'asc')
            ->get();

        return view('inventory.expired_stock', compact('expiredBatches', 'expiringSoonBatches'));
    }
}

