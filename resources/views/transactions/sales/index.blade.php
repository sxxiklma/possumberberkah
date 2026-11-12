@extends('layouts.app', ['title' => 'Transaksi Penjualan', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Transaksi Penjualan</h1>
        <p class="text-xs text-slate-500 mt-1">Daftar seluruh transaksi penjualan toko (Kasir Pintar & Pencatatan Langsung) terintegrasi FIFO</p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-2 shadow-2xs">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Buka Kasir Pintar (POS)
        </a>
        <a href="{{ route('transactions.sales.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Catat Penjualan Baru</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="salePaymentManager()">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold">Total Nilai Penjualan</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Akumulasi penjualan sukses non-void</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold">Total Transaksi</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900">{{ number_format($totalSalesCount, 0, ',', '.') }} Transaksi</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Faktur penjualan yang tercatat</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold">Kas Diterima (Lunas/DP)</span>
                <span class="p-1.5 rounded-lg bg-teal-50 text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="text-lg font-bold text-teal-700">Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}</div>
            <p class="text-[11px] text-teal-600 mt-0.5">Uang riil masuk kas/rekening</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold">Sisa Piutang Penjualan</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-lg font-bold text-amber-700">Rp {{ number_format($totalReceivableAmount, 0, ',', '.') }}</div>
            <p class="text-[11px] text-amber-600 mt-0.5">Tempo pelanggan yang belum lunas</p>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('transactions.sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="No Faktur / Pelanggan..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-1">Pelanggan</label>
                <select name="customer_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Semua Pelanggan --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-1">Status Pembayaran</label>
                <select name="payment_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Semua Status --</option>
                    <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>LUNAS</option>
                    <option value="dp" {{ $paymentStatus === 'dp' ? 'selected' : '' }}>DP (Uang Muka)</option>
                    <option value="partial" {{ $paymentStatus === 'partial' ? 'selected' : '' }}>SEBAGIAN (Tempo)</option>
                    <option value="unpaid" {{ $paymentStatus === 'unpaid' ? 'selected' : '' }}>BELUM BAYAR (Tempo)</option>
                    <option value="void" {{ $paymentStatus === 'void' ? 'selected' : '' }}>DIBATALKAN (Void)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition">
                    Filter
                </button>
                @if($search || $customerId || $paymentStatus || $startDate || $endDate)
                    <a href="{{ route('transactions.sales.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold transition" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">No Faktur</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Pelanggan</th>
                        <th class="p-3.5">Petugas / Kasir</th>
                        <th class="p-3.5">Rincian Item</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5 text-right">Total Transaksi</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/60 {{ $sale->status === 'void' ? 'bg-red-50/30' : '' }}">
                            <td class="p-3.5 font-bold font-mono text-slate-800">
                                <a href="{{ route('transactions.sales.show', $sale) }}" class="text-emerald-700 hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                                @if($sale->price_type === 'wholesale')
                                    <span class="block text-[9px] font-semibold text-purple-600">Harga Grosir</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-500 whitespace-nowrap">
                                <span class="font-medium text-slate-700">{{ $sale->sale_date->format('d/m/Y') }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $sale->sale_date->format('H:i') }} WIB</span>
                            </td>
                            <td class="p-3.5 text-slate-700">
                                <div class="font-semibold text-slate-800">{{ $sale->customer?->name ?? 'Customer Umum (Walk-In)' }}</div>
                                @if($sale->customer?->phone && $sale->customer?->phone !== '-')
                                    <span class="text-[10px] text-slate-400">{{ $sale->customer->phone }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-600 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $sale->user?->name ?? 'Kasir' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-500 max-w-[220px]">
                                <div class="truncate text-slate-700 font-medium">
                                    {{ $sale->items->count() }} Produk
                                    <span class="text-slate-400 font-normal">({{ $sale->items->pluck('product.name')->filter()->take(2)->implode(', ') }}...)</span>
                                </div>
                            </td>
                            <td class="p-3.5 uppercase font-bold text-[10px] text-slate-600">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right font-bold text-slate-900 whitespace-nowrap">
                                <div>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</div>
                                @if($sale->payment_status !== 'paid' && $sale->status !== 'void')
                                    <div class="text-[10px] font-semibold text-amber-600">
                                        Sisa: Rp {{ number_format(max(0, $sale->total_amount - $sale->paid_amount), 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                @if($sale->status === 'void')
                                    <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">
                                        BATAL (VOID)
                                    </span>
                                @elseif($sale->payment_status === 'paid')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">
                                        LUNAS
                                    </span>
                                @elseif($sale->payment_status === 'dp')
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">
                                        DP (UANG MUKA)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                                        TEMPO (PIUTANG)
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('transactions.sales.show', $sale) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition" title="Lihat Detail Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @if($sale->status !== 'void')
                                        <button type="button" 
                                                @click="openModal({
                                                    id: '{{ $sale->id }}',
                                                    invoice: '{{ $sale->invoice_number }}',
                                                    customer: '{{ addslashes($sale->customer?->name ?? 'Customer Umum (Walk-In)') }}',
                                                    total: {{ (float)$sale->total_amount }},
                                                    paid: {{ (float)$sale->paid_amount }},
                                                    remaining: {{ max(0, (float)$sale->total_amount - (float)$sale->paid_amount) }},
                                                    currentStatus: '{{ $sale->payment_status }}',
                                                    method: '{{ $sale->payment_method }}'
                                                })"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" 
                                                title="Edit / Ubah Status Pembayaran & Pelunasan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @endif

                                    <a href="{{ route('transactions.sales.print', $sale) }}" target="_blank"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Cetak Faktur Nota Resmi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>

                                    <a href="{{ route('pos.receipt', $sale) }}" target="_blank" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Cetak Struk Thermal Kasir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>

                                    @if($sale->status !== 'void')
                                        @can('pos.void-sale')
                                            <button type="button" onclick="voidTransaction('{{ $sale->id }}', '{{ $sale->invoice_number }}')"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Batalkan Faktur (Void Reversal)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="font-semibold text-slate-600">Belum ada transaksi penjualan yang tercatat</p>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan tombol "+ Catat Penjualan Baru" atau "Buka Kasir Pintar" untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
    </div>

    <!-- Modal Ubah Status Pembayaran / Pelunasan -->
    <div x-show="isOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden"
             @click.away="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Ubah Status Pembayaran
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Faktur: <span class="font-mono font-bold text-slate-700" x-text="sale.invoice"></span> &bull; <span x-text="sale.customer"></span>
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form :action="'{{ url('transactions/sales') }}/' + sale.id + '/payment'" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- Ringkasan Nilai Tagihan -->
                <div class="grid grid-cols-3 gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Faktur</span>
                        <span class="text-xs font-bold text-slate-800" x-text="formatRupiah(sale.total)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Sudah Dibayar</span>
                        <span class="text-xs font-bold text-emerald-600" x-text="formatRupiah(sale.paid)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Sisa Tagihan</span>
                        <span class="text-xs font-bold text-amber-600" x-text="formatRupiah(sale.remaining)"></span>
                    </div>
                </div>

                <!-- Pilihan Status Pembayaran Baru -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Status Pembayaran Baru <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- LUNAS -->
                        <button type="button" 
                                @click="onStatusChange('paid')"
                                :class="form.payment_status === 'paid' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-2 ring-emerald-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold">LUNAS</span>
                        </button>

                        <!-- DP -->
                        <button type="button" 
                                @click="onStatusChange('dp')"
                                :class="form.payment_status === 'dp' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold">DP</span>
                        </button>

                        <!-- TEMPO -->
                        <button type="button" 
                                @click="onStatusChange('pending')"
                                :class="form.payment_status === 'pending' ? 'bg-amber-50 border-amber-500 text-amber-800 ring-2 ring-amber-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold">TEMPO</span>
                        </button>
                    </div>
                    <input type="hidden" name="payment_status" :value="form.payment_status">
                </div>

                <!-- Input Nominal Yang Dibayarkan Sekarang -->
                <div x-show="form.payment_status !== 'pending'" class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">
                            Nominal Pembayaran Diterima (Rp) <span class="text-red-500">*</span>
                        </label>
                        <button type="button" 
                                x-show="sale.remaining > 0"
                                @click="setFullAmount()" 
                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                            + Lunasi Penuh (<span x-text="formatRupiah(sale.remaining)"></span>)
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" 
                               name="amount" 
                               x-model.number="form.amount"
                               :max="sale.remaining"
                               min="0"
                               step="100"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Pratinjau Sisa Piutang Setelah Simpan:</span>
                        <span class="font-bold" :class="remainingPreview <= 0 ? 'text-emerald-600' : 'text-amber-600'" 
                              x-text="remainingPreview <= 0 ? 'Rp 0 (LUNAS)' : formatRupiah(remainingPreview)"></span>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" 
                            x-model="form.payment_method" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="tempo">Tempo (Piutang)</option>
                    </select>
                </div>

                <!-- Catatan / Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pelunasan / Keterangan (Opsional)</label>
                    <input type="text" 
                           name="notes" 
                           x-model="form.notes"
                           placeholder="Contoh: Pelunasan sisa oleh pembeli di kasir..." 
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeModal()" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function salePaymentManager() {
        return {
            isOpen: false,
            sale: {
                id: null,
                invoice: '',
                customer: '',
                total: 0,
                paid: 0,
                remaining: 0,
                currentStatus: '',
                method: 'cash'
            },
            form: {
                payment_status: 'paid',
                amount: 0,
                payment_method: 'cash',
                notes: ''
            },
            openModal(data) {
                this.sale = { ...data };
                this.form.payment_method = data.method === 'tempo' ? 'cash' : (data.method || 'cash');
                this.form.notes = '';

                if (data.currentStatus === 'paid') {
                    this.form.payment_status = 'paid';
                    this.form.amount = 0;
                } else {
                    this.form.payment_status = 'paid';
                    this.form.amount = data.remaining;
                }
                this.isOpen = true;
            },
            closeModal() {
                this.isOpen = false;
            },
            onStatusChange(newStatus) {
                this.form.payment_status = newStatus;
                if (newStatus === 'paid') {
                    this.form.amount = this.sale.remaining;
                    if (this.form.payment_method === 'tempo') {
                        this.form.payment_method = 'cash';
                    }
                } else if (newStatus === 'dp') {
                    if (this.form.amount <= 0 || this.form.amount >= this.sale.remaining) {
                        this.form.amount = Math.round(this.sale.remaining / 2);
                    }
                    if (this.form.payment_method === 'tempo') {
                        this.form.payment_method = 'cash';
                    }
                } else if (newStatus === 'pending') {
                    this.form.amount = 0;
                    this.form.payment_method = 'tempo';
                }
            },
            setFullAmount() {
                this.form.payment_status = 'paid';
                this.form.amount = this.sale.remaining;
                if (this.form.payment_method === 'tempo') {
                    this.form.payment_method = 'cash';
                }
            },
            get remainingPreview() {
                if (this.form.payment_status === 'paid') return 0;
                if (this.form.payment_status === 'pending') return this.sale.remaining;
                const pay = parseFloat(this.form.amount) || 0;
                return Math.max(0, this.sale.remaining - pay);
            },
            formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
            }
        }
    }

    async function voidTransaction(saleId, invoiceNumber) {
        const { value: reason } = await window.Swal.fire({
            title: `Batalkan Faktur ${invoiceNumber}?`,
            text: "Stok produk akan otomatis dikembalikan ke batch gudang asal (FIFO Reversal).",
            input: 'text',
            inputLabel: 'Alasan Pembatalan Faktur:',
            inputPlaceholder: 'Contoh: Salah input pesanan / Barang batal dibeli...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan Transaksi!',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan pembatalan faktur wajib diisi!';
                }
            }
        });

        if (reason) {
            try {
                const res = await fetch(`/pos/void/${saleId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const data = await res.json();
                if (data.success) {
                    window.Swal.fire('Faktur Dibatalkan', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    window.Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (err) {
                window.Swal.fire('Error', 'Gagal memproses pembatalan faktur.', 'error');
            }
        }
    }
</script>
@endsection

