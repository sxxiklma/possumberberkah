<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today'); // today, this_week, this_month, this_year, custom
        $today = Carbon::today();

        [$startDate, $endDate] = match ($period) {
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom' => [
                Carbon::parse($request->get('start_date', $today->toDateString()))->startOfDay(),
                Carbon::parse($request->get('end_date', $today->toDateString()))->endOfDay()
            ],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
        };

        // 1. Overall Catalog & Stock Metrics
        $totalProducts = Product::where('is_active', true)->count();
        $totalCategories = Category::where('is_active', true)->count();

        $activeBatches = InventoryBatch::where('status', 'active')->where('available_quantity', '>', 0);
        $totalStock = (float) $activeBatches->sum('available_quantity');
        $totalInventoryValuation = (float) (clone $activeBatches)
            ->selectRaw('SUM(available_quantity * cost_price) as val')
            ->value('val') ?? 0;

        // 2. Sales in Period
        $salesInPeriod = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');
        $totalSalesAmount = (float) $salesInPeriod->sum('total_amount');
        $totalSalesCogs = (float) $salesInPeriod->sum('total_cogs');
        $grossProfit = max(0, $totalSalesAmount - $totalSalesCogs);

        // 3. Purchases in Period
        $totalPurchasesAmount = (float) Purchase::whereBetween('purchase_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'received')
            ->sum('total_amount');

        // 4. Receivables & Payables
        $totalReceivables = (float) Receivable::where('remaining_balance', '>', 0)->sum('remaining_balance');
        $totalPayables = (float) Payable::where('remaining_balance', '>', 0)->sum('remaining_balance');

        // 5. Low stock alerts (<= min_stock)
        $lowStockProducts = Product::with(['baseUnit', 'productUnits.unit'])
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) {
                return $product->current_stock <= $product->min_stock;
            });

        // 6. Expiring batches alert (within 30 days)
        $expiringBatches = InventoryBatch::with('product')
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('expiry_date', 'asc')
            ->take(5)
            ->get();

        // 7. Recent Transactions (Completed Sales)
        $recentSales = Sale::with(['customer', 'user'])
            ->latest('sale_date')
            ->take(6)
            ->get();

        // 8. Recent Activities
        $recentActivities = ActivityLog::latest()
            ->take(6)
            ->get();

        // 9. Chart Data: Last 7 Days Sales & Purchases
        $chartDates = [];
        $salesChartData = [];
        $purchasesChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->isoFormat('D MMM');
            $chartDates[] = $label;

            $salesChartData[] = (float) Sale::whereDate('sale_date', $dateStr)
                ->where('status', 'completed')
                ->sum('total_amount');

            $purchasesChartData[] = (float) Purchase::whereDate('purchase_date', $dateStr)
                ->where('status', 'received')
                ->sum('total_amount');
        }

        // 10. Chart Data: Payment Methods Breakdown
        $paymentMethodsData = Sale::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        // 11. Top Selling Products
        $topProducts = SaleItem::select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.base_quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_qty')
            ->with('product.baseUnit')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalProducts',
            'totalCategories',
            'totalStock',
            'totalInventoryValuation',
            'totalSalesAmount',
            'totalPurchasesAmount',
            'grossProfit',
            'totalReceivables',
            'totalPayables',
            'lowStockProducts',
            'expiringBatches',
            'recentSales',
            'recentActivities',
            'chartDates',
            'salesChartData',
            'purchasesChartData',
            'paymentMethodsData',
            'topProducts'
        ));
    }
}

