<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\InventoryBatch;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\StockReturn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Calculate comprehensive Profit & Loss (Laba Rugi) according to accounting standards:
     * - Gross Sales
     * - Sales Returns
     * - Net Sales = Gross Sales - Sales Returns
     * - Actual FIFO COGS (HPP)
     * - Gross Profit = Net Sales - FIFO COGS
     * - Operational Expenses (Beban Operasional Toko)
     * - Net Profit = Gross Profit - Operational Expenses
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Sales
        $salesQuery = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', 'completed');

        $grossSales = (float) $salesQuery->sum('total_amount');
        $totalCogs = (float) $salesQuery->sum('total_cogs');

        // 2. Sales Returns
        $returnsQuery = StockReturn::whereBetween('return_date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'sale_return')
            ->where('status', 'completed');
        $salesReturns = (float) $returnsQuery->sum('total_amount');

        // 3. Net Sales
        $netSales = max(0, $grossSales - $salesReturns);

        // 4. Gross Profit
        $grossProfit = $netSales - $totalCogs;

        // 5. Operational Expenses (Beban Operasional)
        $expensesQuery = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
        $totalExpenses = (float) $expensesQuery->sum('amount');

        $expensesByCategory = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // 6. Net Profit
        $netProfit = $grossProfit - $totalExpenses;

        // 7. Cash Flow (Kas Masuk & Kas Keluar)
        $cashIn = (float) CashTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) CashTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'cash_out')
            ->sum('amount');

        // 8. Receivables & Payables balances
        $totalReceivables = (float) Receivable::where('remaining_balance', '>', 0)->sum('remaining_balance');
        $totalPayables = (float) Payable::where('remaining_balance', '>', 0)->sum('remaining_balance');

        // 9. Current inventory valuation
        $inventoryValuation = (float) InventoryBatch::where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->selectRaw('SUM(available_quantity * cost_price) as val')
            ->value('val') ?? 0;

        return [
            'period' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'gross_sales' => $grossSales,
            'sales_returns' => $salesReturns,
            'net_sales' => $netSales,
            'fifo_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'gross_margin_percentage' => $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0,
            'operational_expenses' => $totalExpenses,
            'expenses_by_category' => $expensesByCategory,
            'net_profit' => $netProfit,
            'net_margin_percentage' => $netSales > 0 ? round(($netProfit / $netSales) * 100, 2) : 0,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'total_receivables' => $totalReceivables,
            'total_payables' => $totalPayables,
            'inventory_valuation' => $inventoryValuation,
        ];
    }

    /**
     * Record an operational expense and automatically create a cash transaction.
     */
    public function recordExpense(array $data, User $user): Expense
    {
        return DB::transaction(function () use ($data, $user) {
            $datePrefix = now()->format('Ymd');
            $count = Expense::whereDate('created_at', now()->toDateString())->count() + 1;
            $expNumber = 'EXP-' . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Record cash out
            $cashCount = CashTransaction::whereDate('created_at', now()->toDateString())->count() + 1;
            $cashNumber = 'KAS-OUT-' . $datePrefix . '-' . str_pad($cashCount, 4, '0', STR_PAD_LEFT);

            $cash = CashTransaction::create([
                'transaction_number' => $cashNumber,
                'type' => 'cash_out',
                'category' => 'Beban: ' . $data['category'],
                'amount' => $data['amount'],
                'transaction_date' => $data['expense_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'description' => $data['notes'] ?? ('Beban Operasional: ' . $data['category']),
                'user_id' => $user->id,
            ]);

            $expense = Expense::create([
                'expense_number' => $expNumber,
                'category' => $data['category'],
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'cash_transaction_id' => $cash->id,
                'notes' => $data['notes'] ?? null,
                'user_id' => $user->id,
            ]);

            return $expense;
        });
    }

    /**
     * Record an installment payment for a Customer Receivable.
     */
    public function recordReceivablePayment(Receivable $receivable, float $amount, string $method, ?string $notes, User $user): Payment
    {
        return DB::transaction(function () use ($receivable, $amount, $method, $notes, $user) {
            $amount = min($amount, (float) $receivable->remaining_balance);

            $payCount = Payment::whereDate('created_at', now()->toDateString())->count() + 1;
            $payNumber = 'PAY-REC-' . now()->format('Ymd') . '-' . str_pad($payCount, 4, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'payment_number' => $payNumber,
                'payment_type' => 'receivable_installment',
                'reference_id' => $receivable->sale_id,
                'reference_type' => Sale::class,
                'customer_id' => $receivable->customer_id,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->toDateString(),
                'notes' => $notes ?? "Cicilan piutang faktur {$receivable->sale?->invoice_number}",
                'user_id' => $user->id,
            ]);

            // Record cash in
            CashTransaction::create([
                'transaction_number' => 'KAS-IN-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT),
                'type' => 'cash_in',
                'category' => 'Pelunasan Piutang',
                'amount' => $amount,
                'transaction_date' => now()->toDateString(),
                'payment_method' => $method,
                'description' => "Pelunasan piutang faktur {$receivable->sale?->invoice_number} dari {$receivable->customer?->name}",
                'user_id' => $user->id,
            ]);

            $newPaid = $receivable->paid_amount + $amount;
            $newRemaining = max(0, $receivable->total_amount - $newPaid);

            $receivable->paid_amount = $newPaid;
            $receivable->remaining_balance = $newRemaining;
            $receivable->status = $newRemaining <= 0 ? 'paid' : 'partial';
            $receivable->save();

            // Update parent sale paid_amount and payment_status
            if ($receivable->sale) {
                $receivable->sale->paid_amount += $amount;
                if ($newRemaining <= 0) {
                    $receivable->sale->payment_status = 'paid';
                }
                $receivable->sale->save();
            }

            return $payment;
        });
    }

    /**
     * Record an installment payment for a Supplier Payable.
     */
    public function recordPayablePayment(Payable $payable, float $amount, string $method, ?string $notes, User $user): Payment
    {
        return DB::transaction(function () use ($payable, $amount, $method, $notes, $user) {
            $amount = min($amount, (float) $payable->remaining_balance);

            $payCount = Payment::whereDate('created_at', now()->toDateString())->count() + 1;
            $payNumber = 'PAY-SUP-' . now()->format('Ymd') . '-' . str_pad($payCount, 4, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'payment_number' => $payNumber,
                'payment_type' => 'payable_installment',
                'reference_id' => $payable->purchase_id,
                'reference_type' => Purchase::class,
                'supplier_id' => $payable->supplier_id,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->toDateString(),
                'notes' => $notes ?? "Pembayaran hutang PO {$payable->purchase?->purchase_number}",
                'user_id' => $user->id,
            ]);

            // Record cash out
            CashTransaction::create([
                'transaction_number' => 'KAS-OUT-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT),
                'type' => 'cash_out',
                'category' => 'Pembayaran Hutang Supplier',
                'amount' => $amount,
                'transaction_date' => now()->toDateString(),
                'payment_method' => $method,
                'description' => "Pembayaran hutang PO {$payable->purchase?->purchase_number} kepada {$payable->supplier?->name}",
                'user_id' => $user->id,
            ]);

            $newPaid = $payable->paid_amount + $amount;
            $newRemaining = max(0, $payable->total_amount - $newPaid);

            $payable->paid_amount = $newPaid;
            $payable->remaining_balance = $newRemaining;
            $payable->status = $newRemaining <= 0 ? 'paid' : 'partial';
            $payable->save();

            // Update parent purchase
            if ($payable->purchase) {
                $payable->purchase->paid_amount += $amount;
                if ($newRemaining <= 0) {
                    $payable->purchase->payment_status = 'paid';
                }
                $payable->purchase->save();
            }

            return $payment;
        });
    }
}

