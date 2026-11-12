<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class FifoInventoryService
{
    /**
     * Add received inventory into FIFO batches and record stock movement.
     */
    public function receiveStock(
        Product $product,
        Warehouse $warehouse,
        float $quantityInBaseUnit,
        float $unitCost,
        string $batchNumber,
        ?string $expiryDate = null,
        ?int $locationId = null,
        ?int $purchaseItemId = null,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryBatch {
        return DB::transaction(function () use (
            $product,
            $warehouse,
            $quantityInBaseUnit,
            $unitCost,
            $batchNumber,
            $expiryDate,
            $locationId,
            $purchaseItemId,
            $reference,
            $notes,
            $userId
        ) {
            $stockBefore = (float) InventoryBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->where('available_quantity', '>', 0)
                ->sum('available_quantity');

            // Create batch
            $batch = InventoryBatch::create([
                'batch_number' => $batchNumber,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'warehouse_location_id' => $locationId ?? $product->warehouse_location_id,
                'purchase_item_id' => $purchaseItemId,
                'initial_quantity' => $quantityInBaseUnit,
                'available_quantity' => $quantityInBaseUnit,
                'cost_price' => $unitCost,
                'received_date' => now()->toDateString(),
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);

            $stockAfter = $stockBefore + $quantityInBaseUnit;

            // Record stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'purchase_receive',
                'reference_number' => $reference ?? $batchNumber,
                'quantity' => $quantityInBaseUnit,
                'quantity_before' => $stockBefore,
                'quantity_after' => $stockAfter,
                'unit_cost' => $unitCost,
                'total_cost' => round($quantityInBaseUnit * $unitCost, 2),
                'user_id' => $userId ?? auth()->id(),
                'notes' => $notes ?? 'Penerimaan stok ke dalam gudang (FIFO Batch)',
            ]);

            return $batch;
        });
    }

    /**
     * Deduct inventory using FIFO (First In, First Out).
     * Locks active batches with lockForUpdate to prevent race conditions and overselling.
     * Skips expired batches for automatic sales.
     *
     * @return array [ 'total_cogs' => float, 'batches_used' => [ ['batch_id', 'quantity', 'cost_price', 'subtotal_cogs'] ] ]
     * @throws Exception
     */
    public function deductStockFifo(
        Product $product,
        float $requiredBaseQuantity,
        string $referenceNumber,
        string $movementType = 'sale',
        ?int $userId = null,
        ?string $notes = null,
        ?int $warehouseId = null
    ): array {
        if ($requiredBaseQuantity <= 0) {
            return ['total_cogs' => 0.0, 'batches_used' => []];
        }

        // Query active batches with available quantity
        $query = InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->where('available_quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        // For sales, do not automatically sell expired batches
        if ($movementType === 'sale') {
            $query->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now()->toDateString());
            });
        }

        // Order FIFO: oldest received_date first, then lowest id
        $activeBatches = $query->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = (float) $activeBatches->sum('available_quantity');

        if ($totalAvailable < $requiredBaseQuantity) {
            throw new Exception(
                "Stok tidak mencukupi untuk {$product->name} (SKU: {$product->sku}). Dibutuhkan: {$requiredBaseQuantity}, Tersedia: {$totalAvailable}"
            );
        }

        $stockBefore = (float) InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->sum('available_quantity');

        $remainingToDeduct = $requiredBaseQuantity;
        $totalCogs = 0.0;
        $batchesUsed = [];
        $primaryWarehouseId = $activeBatches->first()?->warehouse_id;

        foreach ($activeBatches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $availableInBatch = (float) $batch->available_quantity;
            $deductFromThisBatch = min($remainingToDeduct, $availableInBatch);
            $batchCost = (float) $batch->cost_price;
            $subtotalCogs = round($deductFromThisBatch * $batchCost, 2);

            // Update batch quantity
            $newAvailable = $availableInBatch - $deductFromThisBatch;
            $batch->available_quantity = $newAvailable;
            if ($newAvailable <= 0) {
                $batch->status = 'depleted';
            }
            $batch->save();

            $batchesUsed[] = [
                'batch_id' => $batch->id,
                'quantity' => $deductFromThisBatch,
                'cost_price' => $batchCost,
                'subtotal_cogs' => $subtotalCogs,
            ];

            $totalCogs += $subtotalCogs;
            $remainingToDeduct -= $deductFromThisBatch;
        }

        $stockAfter = $stockBefore - $requiredBaseQuantity;

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $primaryWarehouseId ?? 1,
            'inventory_batch_id' => $batchesUsed[0]['batch_id'] ?? null,
            'type' => $movementType,
            'reference_number' => $referenceNumber,
            'quantity' => -$requiredBaseQuantity,
            'quantity_before' => $stockBefore,
            'quantity_after' => $stockAfter,
            'unit_cost' => $requiredBaseQuantity > 0 ? round($totalCogs / $requiredBaseQuantity, 2) : 0,
            'total_cost' => $totalCogs,
            'user_id' => $userId ?? auth()->id(),
            'notes' => $notes ?? "Pengurangan stok FIFO untuk {$referenceNumber}",
        ]);

        return [
            'total_cogs' => round($totalCogs, 2),
            'batches_used' => $batchesUsed,
        ];
    }

    /**
     * Restore batches when a sale or transaction is voided or returned.
     */
    public function restoreFifoBatches(
        array $batchesUsed,
        Product $product,
        string $referenceNumber,
        string $movementType = 'void_reversal',
        ?int $userId = null,
        ?string $notes = null
    ): void {
        if (empty($batchesUsed)) {
            return;
        }

        $stockBefore = (float) InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->sum('available_quantity');

        $totalRestoredQty = 0.0;
        $totalCost = 0.0;
        $primaryWarehouseId = null;

        foreach ($batchesUsed as $item) {
            $batchId = $item['batch_id'] ?? $item['inventory_batch_id'] ?? null;
            $qty = (float) ($item['quantity'] ?? $item['quantity_taken'] ?? 0);
            $cost = (float) ($item['cost_price'] ?? 0);

            if (!$batchId || $qty <= 0) {
                continue;
            }

            $batch = InventoryBatch::lockForUpdate()->find($batchId);
            if ($batch) {
                $batch->available_quantity += $qty;
                if ($batch->status === 'depleted') {
                    $batch->status = 'active';
                }
                $batch->save();
                $primaryWarehouseId = $batch->warehouse_id;
            }

            $totalRestoredQty += $qty;
            $totalCost += ($qty * $cost);
        }

        $stockAfter = $stockBefore + $totalRestoredQty;

        // Record stock movement for reversal
        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $primaryWarehouseId ?? 1,
            'inventory_batch_id' => $batchesUsed[0]['batch_id'] ?? null,
            'type' => $movementType,
            'reference_number' => $referenceNumber,
            'quantity' => $totalRestoredQty,
            'quantity_before' => $stockBefore,
            'quantity_after' => $stockAfter,
            'unit_cost' => $totalRestoredQty > 0 ? round($totalCost / $totalRestoredQty, 2) : 0,
            'total_cost' => round($totalCost, 2),
            'user_id' => $userId ?? auth()->id(),
            'notes' => $notes ?? "Pembalikan stok FIFO untuk {$referenceNumber}",
        ]);
    }
}

