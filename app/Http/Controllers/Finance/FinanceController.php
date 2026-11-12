<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\Receivable;
use App\Services\FinancialService;
use Exception;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Riwayat Seluruh Pembayaran
     */
    public function payments(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');

        $query = Payment::with(['customer', 'supplier', 'user']);

        if ($search) {
            $query->where('payment_number', 'like', "%{$search}%");
        }

        if ($type) {
            $query->where('payment_type', $type);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();
        return view('finance.payments', compact('payments', 'search', 'type'));
    }

    /**
     * Buku Piutang Pelanggan
     */
    public function receivables(Request $request)
    {
        $status = $request->get('status');
        $query = Receivable::with(['customer', 'sale.items.product']);

        if ($status) {
            $query->where('status', $status);
        }

        $receivables = $query->latest()->paginate(15)->withQueryString();
        $totalOutstanding = (float) Receivable::where('remaining_balance', '>', 0)->sum('remaining_balance');
        $overdueCount = Receivable::where('status', '!=', 'paid')->where('due_date', '<', now()->toDateString())->count();

        return view('finance.receivables', compact('receivables', 'status', 'totalOutstanding', 'overdueCount'));
    }

    /**
     * Bayar Cicilan / Pelunasan Piutang
     */
    public function payReceivable(Request $request, Receivable $receivable)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:' . $receivable->remaining_balance],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->financialService->recordReceivablePayment(
                receivable: $receivable,
                amount: (float) $validated['amount'],
                method: $validated['payment_method'],
                notes: $validated['notes'] ?? null,
                user: auth()->user()
            );

            return back()->with('success', "Pembayaran piutang Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Buku Hutang Supplier
     */
    public function payables(Request $request)
    {
        $status = $request->get('status');
        $query = Payable::with(['supplier', 'purchase.items.product']);

        if ($status) {
            $query->where('status', $status);
        }

        $payables = $query->latest()->paginate(15)->withQueryString();
        $totalOutstanding = (float) Payable::where('remaining_balance', '>', 0)->sum('remaining_balance');
        $overdueCount = Payable::where('status', '!=', 'paid')->where('due_date', '<', now()->toDateString())->count();

        return view('finance.payables', compact('payables', 'status', 'totalOutstanding', 'overdueCount'));
    }

    /**
     * Bayar Cicilan / Pelunasan Hutang
     */
    public function payPayable(Request $request, Payable $payable)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:' . $payable->remaining_balance],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->financialService->recordPayablePayment(
                payable: $payable,
                amount: (float) $validated['amount'],
                method: $validated['payment_method'],
                notes: $validated['notes'] ?? null,
                user: auth()->user()
            );

            return back()->with('success', "Pembayaran hutang Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Buku Kas (Kas Masuk & Kas Keluar)
     */
    public function cash(Request $request)
    {
        $type = $request->get('type');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $query = CashTransaction::with('user')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->latest('transaction_date')->paginate(20)->withQueryString();

        $totalCashIn = (float) CashTransaction::where('type', 'cash_in')->sum('amount');
        $totalCashOut = (float) CashTransaction::where('type', 'cash_out')->sum('amount');
        $balance = $totalCashIn - $totalCashOut;

        return view('finance.cash', compact('transactions', 'totalCashIn', 'totalCashOut', 'balance', 'type', 'startDate', 'endDate'));
    }

    /**
     * Tambah Transaksi Kas Masuk / Kas Keluar Manual
     */
    public function storeCash(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cash_in,cash_out'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $datePrefix = now()->format('Ymd');
        $prefix = $validated['type'] === 'cash_in' ? 'KAS-IN-' : 'KAS-OUT-';
        $count = CashTransaction::whereDate('created_at', now()->toDateString())->count() + 1;
        $num = $prefix . $datePrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        CashTransaction::create([
            'transaction_number' => $num,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', "Transaksi buku kas berhasil dicatat!");
    }

    /**
     * Beban Operasional Toko
     */
    public function expenses(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $expenses = Expense::with('user')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $totalExpenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        return view('finance.expenses', compact('expenses', 'totalExpenses', 'startDate', 'endDate'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->financialService->recordExpense($validated, auth()->user());
        return back()->with('success', "Beban operasional sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat!");
    }
}

