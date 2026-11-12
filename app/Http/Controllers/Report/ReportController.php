<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\FinancialService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Laporan Penjualan (Sales Report)
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $customerId = $request->get('customer_id');
        $paymentMethod = $request->get('payment_method');

        $query = Sale::with(['customer', 'user', 'items.product'])
            ->whereBetween('sale_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->latest('sale_date')->paginate(20)->withQueryString();

        $totalSales = (float) (clone $query)->where('status', 'completed')->sum('total_amount');
        $totalCogs = (float) (clone $query)->where('status', 'completed')->sum('total_cogs');
        $totalProfit = $totalSales - $totalCogs;

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        if ($request->get('export') === 'pdf') {
            $allSales = (clone $query)->get();
            $pdf = Pdf::loadView('reports.sales_pdf', compact('allSales', 'startDate', 'endDate', 'totalSales', 'totalCogs', 'totalProfit'));
            return $pdf->download("laporan_penjualan_{$startDate}_{$endDate}.pdf");
        }

        if ($request->get('export') === 'csv') {
            return $this->exportSalesCsv($query->get(), $startDate, $endDate, $totalSales, $totalProfit);
        }

        return view('reports.sales', compact('sales', 'customers', 'startDate', 'endDate', 'customerId', 'paymentMethod', 'totalSales', 'totalCogs', 'totalProfit'));
    }

    /**
     * Laporan Pembelian (Purchases Report)
     */
    public function purchases(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $supplierId = $request->get('supplier_id');

        $query = Purchase::with(['supplier', 'warehouse', 'user', 'items.product'])
            ->whereBetween('purchase_date', [$startDate, $endDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->latest('purchase_date')->paginate(20)->withQueryString();

        $totalPurchases = (float) (clone $query)->where('status', 'received')->sum('total_amount');
        $totalPaid = (float) (clone $query)->where('status', 'received')->sum('paid_amount');
        $totalUnpaid = max(0, $totalPurchases - $totalPaid);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        if ($request->get('export') === 'pdf') {
            $allPurchases = (clone $query)->get();
            $pdf = Pdf::loadView('reports.purchases_pdf', compact('allPurchases', 'startDate', 'endDate', 'totalPurchases', 'totalPaid', 'totalUnpaid'));
            return $pdf->download("laporan_pembelian_{$startDate}_{$endDate}.pdf");
        }

        return view('reports.purchases', compact('purchases', 'suppliers', 'startDate', 'endDate', 'supplierId', 'totalPurchases', 'totalPaid', 'totalUnpaid'));
    }

    /**
     * Laporan Persediaan & Nilai Stok
     */
    public function inventory(Request $request)
    {
        $products = Product::with(['category', 'baseUnit', 'batches', 'location.warehouse'])
            ->where('is_active', true)
            ->get();

        $totalValuation = $products->sum('inventory_value');
        $totalQuantity = $products->sum('current_stock');

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.inventory_pdf', compact('products', 'totalValuation', 'totalQuantity'));
            return $pdf->download("laporan_persediaan_" . date('Ymd') . ".pdf");
        }

        return view('reports.inventory', compact('products', 'totalValuation', 'totalQuantity'));
    }

    /**
     * Laporan Analisis Produk: Fast Moving, Slow Moving, Terlaris, Margin Tertinggi
     */
    public function products(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        // Sales aggregation by product
        $soldAgg = SaleItem::select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.base_quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.cogs_total) as total_cogs')
            )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        $allProducts = Product::with(['baseUnit', 'category'])->where('is_active', true)->get();

        $fastMovingThreshold = 20; // e.g. sold > 20 in period
        $slowMovingThreshold = 5;  // e.g. sold between 1 and 5 in period

        $fastMoving = [];
        $slowMoving = [];
        $noMovement = [];
        $profitableProducts = [];

        foreach ($allProducts as $p) {
            $agg = $soldAgg->get($p->id);
            $soldQty = $agg ? (float) $agg->total_qty : 0;
            $revenue = $agg ? (float) $agg->total_revenue : 0;
            $cogs = $agg ? (float) $agg->total_cogs : 0;
            $profit = $revenue - $cogs;

            $itemData = [
                'product' => $p,
                'sold_qty' => $soldQty,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
            ];

            if ($soldQty >= $fastMovingThreshold) {
                $fastMoving[] = $itemData;
            } elseif ($soldQty > 0 && $soldQty <= $slowMovingThreshold) {
                $slowMoving[] = $itemData;
            } elseif ($soldQty == 0) {
                $noMovement[] = $itemData;
            }

            if ($profit > 0) {
                $profitableProducts[] = $itemData;
            }
        }

        // Sort
        usort($fastMoving, fn($a, $b) => $b['sold_qty'] <=> $a['sold_qty']);
        usort($profitableProducts, fn($a, $b) => $b['profit'] <=> $a['profit']);

        return view('reports.products', compact('fastMoving', 'slowMoving', 'noMovement', 'profitableProducts', 'startDate', 'endDate'));
    }

    /**
     * Laporan Keuangan & Laba Rugi Komprehensif
     */
    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $report = $this->financialService->getProfitAndLoss($startDate, $endDate);

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.financial_pdf', compact('report'));
            return $pdf->download("laporan_keuangan_laba_rugi_{$startDate}_{$endDate}.pdf");
        }

        return view('reports.financial', compact('report', 'startDate', 'endDate'));
    }

    protected function exportSalesCsv($sales, $startDate, $endDate, $totalSales, $totalProfit)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"laporan_penjualan_{$startDate}_{$endDate}.csv\"",
        ];

        $callback = function () use ($sales, $totalSales, $totalProfit) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No Faktur', 'Tanggal', 'Customer', 'Kasir', 'Metode Bayar', 'Status Bayar', 'Total (Rp)', 'Total HPP (Rp)', 'Laba Kotor (Rp)']);

            foreach ($sales as $s) {
                fputcsv($file, [
                    $s->invoice_number,
                    $s->sale_date->format('d/m/Y H:i'),
                    $s->customer?->name ?? 'Umum',
                    $s->user?->name,
                    strtoupper($s->payment_method),
                    strtoupper($s->payment_status),
                    $s->total_amount,
                    $s->total_cogs,
                    $s->gross_profit,
                ]);
            }
            fputcsv($file, ['', '', '', '', '', 'TOTAL', $totalSales, '', $totalProfit]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

