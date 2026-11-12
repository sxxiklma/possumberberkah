<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\Warehouse;
use App\Services\FifoInventoryService;
use App\Services\UnitConversionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function __construct(
        protected FifoInventoryService $fifoService,
        protected UnitConversionService $conversionService
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');
        $reason = $request->get('reason');

        $query = StockOut::with(['warehouse', 'user'])->withCount('items');

        if ($search) {
            $query->where('reference_number', 'like', "%{$search}%");
        }

        if ($reason) {
            $query->where('reason', $reason);
        }

        $stockOuts = $query->latest('date')->paginate(15)->withQueryString();
        return view('transactions.stock_outs.index', compact('stockOuts', 'search', 'reason'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'productUnits.unit'])->where('is_active', true)->get();

        return view('transactions.stock_outs.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'date' => ['required', 'date'],
            'reason' => ['required', 'in:damaged,lost,internal_use,sample,adjustment,expired'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $datePrefix = now()->format('Ymd');
            $count = StockOut::whereDate('created_at', now()->toDateString())->count() + 1;
            $refNumber = 'OUT-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $stockOut = StockOut::create([
                'reference_number' => $refNumber,
                'date' => $validated['date'],
                'reason' => $validated['reason'],
                'warehouse_id' => $validated['warehouse_id'],
                'user_id' => $user->id,
                'total_cost' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalCost = 0.0;

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unitId = (int) $itemData['unit_id'];
                $quantity = (float) $itemData['quantity'];

                $conversionFactor = $this->conversionService->getConversionFactor($product, $unitId);
                $baseQuantity = round($quantity * $conversionFactor, 4);

                // Deduct via FIFO
                $fifoResult = $this->fifoService->deductStockFifo(
                    product: $product,
                    requiredBaseQuantity: $baseQuantity,
                    referenceNumber: $refNumber,
                    movementType: 'stock_out',
                    userId: $user->id,
                    notes: "Barang keluar ({$stockOut->reason}) ref: {$refNumber}",
                    warehouseId: $validated['warehouse_id']
                );

                $itemCogs = $fifoResult['total_cogs'];
                $totalCost += $itemCogs;

                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'cogs_total' => $itemCogs,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            $stockOut->total_cost = $totalCost;
            $stockOut->save();

            ActivityLog::record(
                action: 'create_stock_out',
                module: 'Inventory',
                description: "Mencatat barang keluar {$refNumber} ({$stockOut->reason}) total nilai Rp " . number_format($totalCost, 0, ',', '.')
            );

            return redirect()->route('transactions.stock-outs.show', $stockOut)
                ->with('success', "Barang keluar {$refNumber} berhasil dicatat!");
        });
    }

    public function show(StockOut $stockOut)
    {
        $stockOut->load(['warehouse', 'user', 'items.product.baseUnit', 'items.unit']);
        return view('transactions.stock_outs.show', compact('stockOut'));
    }

    public function exportPdf(StockOut $stockOut)
    {
        $stockOut->load(['warehouse', 'user', 'items.product.baseUnit', 'items.unit']);
        $pdf = Pdf::loadView('transactions.stock_outs.pdf', compact('stockOut'));
        return $pdf->download("bukti_barang_keluar_{$stockOut->reference_number}.pdf");
    }

    public function exportCsv(StockOut $stockOut)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"barang_keluar_{$stockOut->reference_number}.csv\"",
        ];

        $stockOut->load(['items.product', 'items.unit']);

        $callback = function () use ($stockOut) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No Referensi', $stockOut->reference_number]);
            fputcsv($file, ['Tanggal', $stockOut->date->format('d/m/Y')]);
            fputcsv($file, ['Alasan', $stockOut->reason]);
            fputcsv($file, ['Gudang', $stockOut->warehouse?->name]);
            fputcsv($file, ['Petugas', $stockOut->user?->name]);
            fputcsv($file, []);
            fputcsv($file, ['SKU', 'Nama Produk', 'Jumlah', 'Satuan', 'Total Nilai (HPP)', 'Keterangan']);

            foreach ($stockOut->items as $item) {
                fputcsv($file, [
                    $item->product?->sku,
                    $item->product?->name,
                    $item->quantity,
                    $item->unit?->code,
                    $item->cogs_total,
                    $item->notes ?? '-',
                ]);
            }
            fputcsv($file, ['', '', '', 'TOTAL', $stockOut->total_cost, '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

