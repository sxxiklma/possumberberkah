<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class PosSaleService
{
    public function __construct(
        protected FifoInventoryService $fifoService,
        protected UnitConversionService $conversionService
    ) {}

    /**
     * Create a complete POS sale transaction with atomic safety, FIFO batch deduction, and receivables.
     *
     * @param array $data
     *   - customer_id: ?int
     *   - price_type: 'retail'|'wholesale'
     *   - discount_amount: float
     *   - tax_amount: float
     *   - paid_amount: float
     *   - payment_method: string
     *   - due_date: ?string
     *   - notes: ?string
     *   - items: array of [ 'product_id', 'unit_id', 'quantity', 'unit_price', 'discount_amount' ]
     * @param User $cashier
     * @return Sale
     * @throws Exception
     */
    public function createSale(array $data, User $cashier): Sale
    {
        return DB::transaction(function () use ($data, $cashier) {
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new Exception("Keranjang belanja tidak boleh kosong.");
            }

            // Generate unique invoice number: INV-YYYYMMDD-0001
            $datePrefix = now()->format('Ymd');
            $todayCount = Sale::whereDate('created_at', now()->toDateString())->lockForUpdate()->count() + 1;
            $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            // Double check uniqueness
            while (Sale::where('invoice_number', $invoiceNumber)->exists()) {
                $todayCount++;
                $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
            }

            $subtotal = 0.0;
            $totalCogs = 0.0;
            $preparedItems = [];

            // 1. First pass: validate items, calculate subtotal and convert units
            foreach ($itemsData as $row) {
                $product = Product::lockForUpdate()->findOrFail($row['product_id']);
                $unitId = (int) $row['unit_id'];
                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];
                $itemDiscount = (float) ($row['discount_amount'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception("Jumlah produk {$product->name} harus lebih dari 0.");
                }

                $conversionFactor = $this->conversionService->getConversionFactor($product, $unitId);
                $baseQuantity = round($quantity * $conversionFactor, 4);

                $itemSubtotal = round(($quantity * $unitPrice) - $itemDiscount, 2);
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'conversion_factor' => $conversionFactor,
                    'base_quantity' => $baseQuantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $overallDiscount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = max(0, round($subtotal - $overallDiscount + $taxAmount, 2));

            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            $changeAmount = 0.0;
            $paymentMethod = $data['payment_method'] ?? 'cash';

            // Determine payment status
            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
                $changeAmount = round($paidAmount - $totalAmount, 2);
            } elseif ($paidAmount > 0) {
                $paymentStatus = ($paymentMethod === 'dp' || $paidAmount < ($totalAmount * 0.5)) ? 'dp' : 'partial';
            } else {
                $paymentStatus = 'unpaid';
            }

            // Create Sale record
            $saleDate = now();
            if (!empty($data['sale_date'])) {
                $saleDate = strlen($data['sale_date']) === 10
                    ? \Carbon\Carbon::parse($data['sale_date'])->setTime(now()->hour, now()->minute, now()->second)
                    : \Carbon\Carbon::parse($data['sale_date']);
            }

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => $cashier->id,
                'sale_date' => $saleDate,
                'price_type' => $data['price_type'] ?? 'retail',
                'subtotal' => $subtotal,
                'discount_amount' => $overallDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'total_cogs' => 0, // will update after batch deductions
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'due_date' => $data['due_date'] ?? ($paymentStatus !== 'paid' ? now()->addDays(30)->toDateString() : null),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Second pass: Deduct FIFO batches for each item and record
            foreach ($preparedItems as $item) {
                $product = $item['product'];
                $baseQty = $item['base_quantity'];

                // Deduct via FIFO
                $fifoResult = $this->fifoService->deductStockFifo(
                    product: $product,
                    requiredBaseQuantity: $baseQty,
                    referenceNumber: $invoiceNumber,
                    movementType: 'sale',
                    userId: $cashier->id,
                    notes: "Penjualan kasir faktur {$invoiceNumber}"
                );

                $itemCogs = $fifoResult['total_cogs'];
                $totalCogs += $itemCogs;

                // Create SaleItem
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'conversion_factor' => $item['conversion_factor'],
                    'base_quantity' => $baseQty,
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'subtotal' => $item['subtotal'],
                    'cogs_total' => $itemCogs,
                ]);

                // Record batch details
                foreach ($fifoResult['batches_used'] as $batchUsed) {
                    SaleItemBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'inventory_batch_id' => $batchUsed['batch_id'],
                        'quantity_taken' => $batchUsed['quantity'],
                        'cost_price' => $batchUsed['cost_price'],
                        'subtotal_cogs' => $batchUsed['subtotal_cogs'],
                    ]);
                }
            }

            // Update sale total_cogs
            $sale->total_cogs = round($totalCogs, 2);
            $sale->save();

            // 3. Payment record
            if ($paidAmount > 0) {
                $actualPaid = min($paidAmount, $totalAmount);
                $payNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad(Payment::count() + 1, 4, '0', STR_PAD_LEFT);
                Payment::create([
                    'payment_number' => $payNumber,
                    'payment_type' => 'sale_payment',
                    'reference_id' => $sale->id,
                    'reference_type' => Sale::class,
                    'customer_id' => $sale->customer_id,
                    'amount' => $actualPaid,
                    'payment_method' => $paymentMethod,
                    'payment_date' => now()->toDateString(),
                    'notes' => "Pembayaran awal transaksi {$invoiceNumber}",
                    'user_id' => $cashier->id,
                ]);
            }

            // 4. Receivables record if remaining balance > 0
            $remaining = round($totalAmount - min($paidAmount, $totalAmount), 2);
            if ($remaining > 0) {
                if (!$sale->customer_id) {
                    $defaultCustomer = Customer::where('name', 'like', '%Pelanggan Umum%')
                        ->orWhere('category', 'umum')
                        ->first() ?? Customer::first();

                    if (!$defaultCustomer) {
                        $defaultCustomer = Customer::create([
                            'code' => 'CUST-UMUM',
                            'name' => 'Pelanggan Umum Toko (Walk-In)',
                            'category' => 'umum',
                            'is_active' => true,
                        ]);
                    }
                    $sale->customer_id = $defaultCustomer->id;
                    $sale->save();
                }

                Receivable::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'total_amount' => $totalAmount,
                    'paid_amount' => min($paidAmount, $totalAmount),
                    'remaining_balance' => $remaining,
                    'due_date' => $sale->due_date ?? now()->addDays(30)->toDateString(),
                    'status' => $paidAmount > 0 ? 'partial' : 'unpaid',
                ]);
            }

            // 5. Activity Log
            ActivityLog::record(
                action: 'create_sale',
                module: 'POS',
                description: "Penjualan kasir faktur {$invoiceNumber} total Rp " . number_format($totalAmount, 0, ',', '.') . " oleh {$cashier->name}",
                properties: [
                    'sale_id' => $sale->id,
                    'invoice_number' => $invoiceNumber,
                    'total' => $totalAmount,
                    'total_cogs' => $totalCogs,
                    'payment_method' => $paymentMethod,
                ]
            );

            return $sale->load(['items.product.baseUnit', 'items.unit', 'customer', 'user', 'receivable']);
        });
    }

    /**
     * Void a sale: Reverses stock batches via FIFO reversal, cancels receivable, and updates sale status.
     *
     * @param Sale $sale
     * @param User $user
     * @param string $reason
     * @return Sale
     * @throws Exception
     */
    public function voidSale(Sale $sale, User $user, string $reason): Sale
    {
        return DB::transaction(function () use ($sale, $user, $reason) {
            if ($sale->status === 'void') {
                throw new Exception("Transaksi faktur {$sale->invoice_number} sudah dibatalkan sebelumnya.");
            }

            $sale->load(['items.batches', 'items.product']);

            // Restore batches for every item
            foreach ($sale->items as $item) {
                $batchesUsed = $item->batches->map(function ($b) {
                    return [
                        'batch_id' => $b->inventory_batch_id,
                        'quantity' => (float) $b->quantity_taken,
                        'cost_price' => (float) $b->cost_price,
                    ];
                })->toArray();

                $this->fifoService->restoreFifoBatches(
                    batchesUsed: $batchesUsed,
                    product: $item->product,
                    referenceNumber: $sale->invoice_number,
                    movementType: 'void_reversal',
                    userId: $user->id,
                    notes: "Pembatalan faktur {$sale->invoice_number}: {$reason}"
                );
            }

            // Cancel receivable if exists
            if ($sale->receivable) {
                $sale->receivable->status = 'paid'; // or zeroed out
                $sale->receivable->remaining_balance = 0;
                $sale->receivable->save();
            }

            // Mark sale as void
            $sale->status = 'void';
            $sale->void_by_user_id = $user->id;
            $sale->void_at = now();
            $sale->void_reason = $reason;
            $sale->save();

            // Activity Log
            ActivityLog::record(
                action: 'void_sale',
                module: 'POS',
                description: "Pembatalan (Void) transaksi faktur {$sale->invoice_number} oleh {$user->name}. Alasan: {$reason}",
                properties: [
                    'sale_id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'reason' => $reason,
                ]
            );

            return $sale;
        });
    }
}
