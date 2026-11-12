<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['warehouse', 'user'])->withCount('items')->latest()->paginate(15);
        return view('inventory.opname.index', compact('opnames'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'batches'])->where('is_active', true)->get();

        return view('inventory.opname.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $datePrefix = now()->format('Ymd');
            $count = StockOpname::whereDate('created_at', now()->toDateString())->count() + 1;
            $opNumber = 'SO-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $opname = StockOpname::create([
                'opname_number' => $opNumber,
                'opname_date' => $validated['opname_date'],
                'warehouse_id' => $validated['warehouse_id'],
                'user_id' => $user->id,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $systemQty = $product->current_stock;
                $physicalQty = (float) $itemData['physical_quantity'];
                $diff = $physicalQty - $systemQty;

                $latestCost = (float) ($product->activeBatches()->latest()->value('cost_price') ?? $product->buy_price);

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'system_quantity' => $systemQty,
                    'physical_quantity' => $physicalQty,
                    'difference' => $diff,
                    'unit_cost' => $latestCost,
                    'total_difference_cost' => round($diff * $latestCost, 2),
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            ActivityLog::record(
                action: 'create_stock_opname',
                module: 'Inventory',
                description: "Membuat draft Stock Opname {$opNumber}"
            );

            return redirect()->route('inventory.opname.show', $opname)
                ->with('success', "Stock Opname {$opNumber} berhasil dibuat!");
        });
    }

    public function show(StockOpname $opname)
    {
        $opname->load(['warehouse', 'user', 'items.product.baseUnit']);
        return view('inventory.opname.show', compact('opname'));
    }

    /**
     * Apply stock adjustment from opname results
     */
    public function adjust(StockOpname $opname)
    {
        if ($opname->status === 'adjusted') {
            return back()->with('error', 'Stock Opname ini sudah disesuaikan sebelumnya.');
        }

        return DB::transaction(function () use ($opname) {
            $user = auth()->user();

            foreach ($opname->items as $item) {
                $diff = (float) $item->difference;
                if ($diff == 0) {
                    continue;
                }

                $product = $item->product;
                $currentStock = $product->current_stock;

                if ($diff > 0) {
                    // Surplus: Create adjustment batch
                    $batchNumber = 'BATCH-ADJ-' . $opname->opname_number . '-' . $product->id;
                    $batch = InventoryBatch::create([
                        'batch_number' => $batchNumber,
                        'product_id' => $product->id,
                        'warehouse_id' => $opname->warehouse_id,
                        'initial_quantity' => $diff,
                        'available_quantity' => $diff,
                        'cost_price' => $item->unit_cost,
                        'received_date' => now()->toDateString(),
                        'status' => 'active',
                    ]);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $opname->warehouse_id,
                        'inventory_batch_id' => $batch->id,
                        'type' => 'opname_adjustment',
                        'reference_number' => $opname->opname_number,
                        'quantity' => $diff,
                        'quantity_before' => $currentStock,
                        'quantity_after' => $currentStock + $diff,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_difference_cost,
                        'user_id' => $user->id,
                        'notes' => "Penyesuaian lebih fisik Opname {$opname->opname_number}",
                    ]);
                } else {
                    // Deficit: deduct from batches
                    $absDiff = abs($diff);
                    $remaining = $absDiff;
                    $batches = InventoryBatch::where('product_id', $product->id)
                        ->where('status', 'active')
                        ->where('available_quantity', '>', 0)
                        ->orderBy('received_date', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($batches as $b) {
                        if ($remaining <= 0) break;
                        $take = min($remaining, (float) $b->available_quantity);
                        $b->available_quantity -= $take;
                        if ($b->available_quantity <= 0) {
                            $b->status = 'depleted';
                        }
                        $b->save();
                        $remaining -= $take;
                    }

                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $opname->warehouse_id,
                        'type' => 'opname_adjustment',
                        'reference_number' => $opname->opname_number,
                        'quantity' => $diff,
                        'quantity_before' => $currentStock,
                        'quantity_after' => max(0, $currentStock - $absDiff),
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_difference_cost,
                        'user_id' => $user->id,
                        'notes' => "Penyesuaian kurang fisik Opname {$opname->opname_number}",
                    ]);
                }
            }

            $opname->status = 'adjusted';
            $opname->save();

            ActivityLog::record(
                action: 'adjust_stock_opname',
                module: 'Inventory',
                description: "Menyetujui dan menerapkan penyesuaian stok Opname {$opname->opname_number}"
            );

            return back()->with('success', "Penyesuaian stok Opname {$opname->opname_number} berhasil diterapkan ke kartu stok dan gudang!");
        });
    }
}

