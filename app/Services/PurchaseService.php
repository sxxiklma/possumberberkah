<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CashTransaction;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected FifoInventoryService $fifoService,
        protected UnitConversionService $conversionService
    ) {}

    /**
     * Create a purchase order (Barang Masuk)
     */
    public function createPurchase(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user) {
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new Exception("Daftar barang pembelian tidak boleh kosong.");
            }

            // Generate PO number
            $datePrefix = now()->format('Ymd');
            $count = Purchase::whereDate('created_at', now()->toDateString())->lockForUpdate()->count() + 1;
            $poNumber = 'PO-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            while (Purchase::where('purchase_number', $poNumber)->exists()) {
                $count++;
                $poNumber = 'PO-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $subtotal = 0.0;
            $preparedItems = [];

            foreach ($itemsData as $row) {
                $product = Product::findOrFail($row['product_id']);
                $unitId = (int) $row['unit_id'];
                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];
                $discount = (float) ($row['discount_amount'] ?? 0);

                $conversionFactor = $this->conversionService->getConversionFactor($product, $unitId);
                $baseQuantity = round($quantity * $conversionFactor, 4);
                $baseUnitCost = $conversionFactor > 0 ? round($unitPrice / $conversionFactor, 2) : $unitPrice;

                $itemSubtotal = round(($quantity * $unitPrice) - $discount, 2);
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'base_unit_cost' => $baseUnitCost,
                    'discount_amount' => $discount,
                    'subtotal' => $itemSubtotal,
                    'batch_number' => $row['batch_number'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'warehouse_location_id' => $row['warehouse_location_id'] ?? $product->warehouse_location_id,
                ];
            }

            $overallDiscount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = max(0, round($subtotal - $overallDiscount + $taxAmount, 2));
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            $paymentStatus = $data['payment_status'] ?? null;
            if (!$paymentStatus) {
                if ($paidAmount >= $totalAmount && $totalAmount > 0) {
                    $paymentStatus = 'paid';
                } elseif ($paidAmount > 0) {
                    $paymentStatus = 'partial';
                } else {
                    $paymentStatus = 'unpaid';
                }
            } else {
                if ($paymentStatus === 'paid' && $paidAmount <= 0) {
                    $paidAmount = $totalAmount;
                } elseif ($paymentStatus === 'unpaid') {
                    $paidAmount = 0;
                }
            }

            $purchase = Purchase::create([
                'purchase_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'] ?? 1,
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_image' => $data['invoice_image'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $overallDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'due_date' => $data['due_date'] ?? ($paymentStatus !== 'paid' ? now()->addDays(30)->toDateString() : null),
                'status' => 'draft',
                'user_id' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($preparedItems as $item) {
                $purchase->items()->create($item);
            }

            ActivityLog::record(
                action: 'create_purchase_draft',
                module: 'Purchases',
                description: "Draft pembelian {$poNumber} senilai Rp " . number_format($totalAmount, 0, ',', '.') . " dibuat oleh {$user->name}",
                properties: ['purchase_id' => $purchase->id, 'total' => $totalAmount]
            );

            // Auto-receive if requested
            if (!empty($data['auto_receive'])) {
                $this->receivePurchase($purchase, $user);
            }

            return $purchase->fresh(['items.product', 'supplier', 'warehouse']);
        });
    }

    /**
     * Confirm goods receipt: creates inventory batches via FIFO and registers payables.
     */
    public function receivePurchase(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $user) {
            if ($purchase->status === 'received') {
                throw new Exception("Pembelian {$purchase->purchase_number} sudah diterima sebelumnya.");
            }

            $warehouse = $purchase->warehouse ?: Warehouse::first();

            foreach ($purchase->items as $item) {
                $batchNo = $item->batch_number ?: 'BATCH-' . $purchase->purchase_number . '-' . $item->id;
                
                $batch = $this->fifoService->receiveStock(
                    product: $item->product,
                    warehouse: $warehouse,
                    quantityInBaseUnit: (float) $item->base_quantity,
                    unitCost: (float) $item->base_unit_cost,
                    batchNumber: $batchNo,
                    expiryDate: $item->expiry_date?->toDateString(),
                    locationId: $item->warehouse_location_id,
                    purchaseItemId: $item->id,
                    reference: $purchase->purchase_number,
                    notes: "Penerimaan PO {$purchase->purchase_number} dari {$purchase->supplier?->name}",
                    userId: $user->id
                );

                $item->batch_number = $batchNo;
                $item->save();
            }

            // Create payable if remaining balance > 0
            $remaining = round($purchase->total_amount - $purchase->paid_amount, 2);
            if ($remaining > 0) {
                Payable::updateOrCreate(
                    ['purchase_id' => $purchase->id],
                    [
                        'supplier_id' => $purchase->supplier_id,
                        'total_amount' => $purchase->total_amount,
                        'paid_amount' => $purchase->paid_amount,
                        'remaining_balance' => $remaining,
                        'due_date' => $purchase->due_date ?: now()->addDays(30)->toDateString(),
                        'status' => $purchase->payment_status,
                    ]
                );
            }

            // Record initial payment if any
            if ($purchase->paid_amount > 0) {
                $payNumber = 'PAY-SUP-' . now()->format('Ymd') . '-' . str_pad(Payment::count() + 1, 4, '0', STR_PAD_LEFT);
                Payment::create([
                    'payment_number' => $payNumber,
                    'payment_type' => 'purchase_payment',
                    'reference_id' => $purchase->id,
                    'reference_type' => Purchase::class,
                    'supplier_id' => $purchase->supplier_id,
                    'amount' => $purchase->paid_amount,
                    'payment_method' => $purchase->payment_method ?: 'cash',
                    'payment_date' => now()->toDateString(),
                    'notes' => "Pembayaran awal PO {$purchase->purchase_number}",
                    'user_id' => $user->id,
                ]);

                CashTransaction::create([
                    'transaction_number' => 'KAS-OUT-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT),
                    'type' => 'cash_out',
                    'category' => 'Pembayaran Pembelian Barang Masuk',
                    'amount' => $purchase->paid_amount,
                    'transaction_date' => now()->toDateString(),
                    'payment_method' => $purchase->payment_method ?: 'cash',
                    'description' => "Pembayaran awal PO {$purchase->purchase_number} kepada " . ($purchase->supplier?->name ?? 'Supplier'),
                    'user_id' => $user->id,
                ]);
            }

            $purchase->status = 'received';
            $purchase->received_at = now();
            $purchase->save();

            ActivityLog::record(
                action: 'receive_purchase',
                module: 'Purchases',
                description: "Konfirmasi penerimaan barang untuk PO {$purchase->purchase_number} oleh {$user->name}",
                properties: ['purchase_id' => $purchase->id]
            );

            return $purchase;
        });
    }
}

