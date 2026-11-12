<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Unit;
use App\Services\FinancialService;
use App\Services\PosSaleService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(
        protected PosSaleService $posSaleService,
        protected UnitConversionService $conversionService,
        protected FinancialService $financialService
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');
        $customerId = $request->get('customer_id');
        $paymentStatus = $request->get('payment_status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Sale::with(['customer', 'user', 'items.product.baseUnit', 'items.unit'])->latest('sale_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($paymentStatus) {
            if ($paymentStatus === 'void') {
                $query->where('status', 'void');
            } else {
                $query->where('status', '!=', 'void')->where('payment_status', $paymentStatus);
            }
        }

        if ($startDate) {
            $query->whereDate('sale_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('sale_date', '<=', $endDate);
        }

        // Summary Statistics (calculated from non-void sales)
        $statsQuery = Sale::where('status', '!=', 'void');
        if ($startDate) {
            $statsQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $statsQuery->whereDate('sale_date', '<=', $endDate);
        }

        $totalSalesAmount = (float) (clone $statsQuery)->sum('total_amount');
        $totalSalesCount = (int) (clone $statsQuery)->count();
        $totalPaidAmount = (float) (clone $statsQuery)->sum('paid_amount');
        $totalReceivableAmount = max(0, $totalSalesAmount - $totalPaidAmount);

        $sales = $query->paginate(15)->withQueryString();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('transactions.sales.index', compact(
            'sales',
            'customers',
            'search',
            'customerId',
            'paymentStatus',
            'startDate',
            'endDate',
            'totalSalesAmount',
            'totalSalesCount',
            'totalPaidAmount',
            'totalReceivableAmount'
        ));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['category', 'baseUnit', 'productUnits.unit', 'batches'])
            ->where('is_active', true)
            ->get();
        $taxPercentage = (float) Setting::get('tax_percentage', 0);

        // Preview next invoice number
        $datePrefix = now()->format('Ymd');
        $todayCount = Sale::whereDate('created_at', now()->toDateString())->count() + 1;
        $previewInvoice = 'INV-' . $datePrefix . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

        return view('transactions.sales.create', compact('customers', 'products', 'taxPercentage', 'previewInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'price_type' => ['required', 'in:retail,wholesale'],
            'payment_method' => ['required', 'string'],
            'due_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Calculate expected net total
        $itemsTotal = collect($validated['items'])->sum(function ($it) {
            $sub = ((float)$it['quantity'] * (float)$it['unit_price']) - (float)($it['discount_amount'] ?? 0);
            return max(0, $sub);
        });
        $netTotal = max(0, $itemsTotal - (float)($validated['discount_amount'] ?? 0) + (float)($validated['tax_amount'] ?? 0));

        // If payment method is cash, transfer, or qris and paid_amount is empty or 0, default to paid in full (Lunas)
        if (in_array(strtolower($validated['payment_method']), ['cash', 'transfer', 'qris', 'tunai'])) {
            if (!isset($validated['paid_amount']) || (float)$validated['paid_amount'] <= 0) {
                $validated['paid_amount'] = $netTotal;
            }
        } else {
            $validated['paid_amount'] = (float)($validated['paid_amount'] ?? 0);
        }

        try {
            $sale = $this->posSaleService->createSale($validated, auth()->user());

            ActivityLog::record('create_sale_manual', 'Transaksi Penjualan', "Mencatat transaksi penjualan no faktur {$sale->invoice_number} (Total: Rp " . number_format($sale->total_amount, 0, ',', '.') . ")");

            return redirect()->route('transactions.sales.show', $sale)
                ->with('success', "Transaksi Penjualan {$sale->invoice_number} berhasil dicatat dan stok gudang FIFO telah diperbarui!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'user',
            'items.product.baseUnit',
            'items.unit',
            'items.batches.inventoryBatch.warehouse',
            'receivable',
            'payments'
        ]);

        return view('transactions.sales.show', compact('sale'));
    }

    public function print(Sale $sale)
    {
        $sale->load(['items.product.baseUnit', 'items.unit', 'customer', 'user']);
        $store = [
            'name' => Setting::get('store_name', 'TOKO SUMBER BERKAH'),
            'tagline' => Setting::get('store_tagline', 'Pusat Bahan Kue & Perlengkapan Roti Terlengkap'),
            'address' => Setting::get('store_address', 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Kabupaten Jombang, Jawa Timur 64181'),
            'phone' => Setting::get('store_phone', '0812-3456-7890'),
            'email' => Setting::get('store_email', 'tobaku.sumberberkah@gmail.com'),
            'footer' => Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda di TOKO SUMBER BERKAH!'),
        ];

        return view('transactions.sales.print', compact('sale', 'store'));
    }

    /**
     * Update payment status or record payment / pelunasan for a sale.
     */
    public function updatePayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:paid,dp,pending'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($sale, $validated) {
                $status = $validated['payment_status'];
                $method = $validated['payment_method'];
                $notes = $validated['notes'] ?? null;
                $user = auth()->user();

                $currentPaid = (float) $sale->paid_amount;
                $total = (float) $sale->total_amount;
                $remaining = max(0, $total - $currentPaid);

                $payAmount = isset($validated['amount']) ? (float) $validated['amount'] : 0;
                if ($status === 'paid' && $payAmount <= 0) {
                    $payAmount = $remaining;
                }

                // Cap payment amount at remaining balance
                $payAmount = min($payAmount, $remaining);

                if ($payAmount > 0) {
                    if ($sale->receivable) {
                        $this->financialService->recordReceivablePayment(
                            receivable: $sale->receivable,
                            amount: $payAmount,
                            method: $method,
                            notes: $notes ?? "Pelunasan faktur {$sale->invoice_number}",
                            user: $user
                        );
                    } else {
                        // Create payment record
                        $payCount = Payment::whereDate('created_at', now()->toDateString())->count() + 1;
                        $payNumber = 'PAY-REC-' . now()->format('Ymd') . '-' . str_pad($payCount, 4, '0', STR_PAD_LEFT);

                        Payment::create([
                            'payment_number' => $payNumber,
                            'payment_type' => 'receivable_installment',
                            'reference_id' => $sale->id,
                            'reference_type' => Sale::class,
                            'customer_id' => $sale->customer_id,
                            'amount' => $payAmount,
                            'payment_method' => $method,
                            'payment_date' => now()->toDateString(),
                            'notes' => $notes ?? "Pelunasan faktur {$sale->invoice_number}",
                            'user_id' => $user->id,
                        ]);

                        // Record cash transaction
                        CashTransaction::create([
                            'transaction_number' => 'KAS-IN-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::count() + 1, 4, '0', STR_PAD_LEFT),
                            'type' => 'cash_in',
                            'category' => 'Pelunasan Piutang',
                            'amount' => $payAmount,
                            'transaction_date' => now()->toDateString(),
                            'payment_method' => $method,
                            'description' => "Pelunasan faktur {$sale->invoice_number} dari " . ($sale->customer?->name ?? 'Walk-In'),
                            'user_id' => $user->id,
                        ]);

                        $sale->paid_amount = $currentPaid + $payAmount;
                    }
                }

                $sale->refresh();

                // Finalize status
                if ($status === 'paid' || $sale->paid_amount >= $sale->total_amount) {
                    $sale->payment_status = 'paid';
                    $sale->paid_amount = $sale->total_amount;
                    if ($sale->receivable) {
                        $sale->receivable->paid_amount = $sale->total_amount;
                        $sale->receivable->remaining_balance = 0;
                        $sale->receivable->status = 'paid';
                        $sale->receivable->save();
                    }
                } elseif ($status === 'dp') {
                    $sale->payment_status = 'dp';
                } else {
                    $sale->payment_status = ($sale->paid_amount > 0) ? 'dp' : 'pending';
                }

                $sale->payment_method = $method;
                $sale->save();

                ActivityLog::record(
                    'update_sale_payment',
                    'Transaksi Penjualan',
                    "Memperbarui status pembayaran faktur {$sale->invoice_number} menjadi " . strtoupper($sale->payment_status) . " (Dibayar: Rp " . number_format($sale->paid_amount, 0, ',', '.') . ")"
                );
            });

            return back()->with('success', "Status pembayaran faktur {$sale->invoice_number} berhasil diperbarui!");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui status pembayaran: ' . $e->getMessage());
        }
    }
}

