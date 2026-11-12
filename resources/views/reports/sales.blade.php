@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">
    <!-- Header & Export Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Penjualan (Sales Report)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Analisis omset penjualan, HPP FIFO per transaksi, dan perolehan laba kotor toko.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV / Excel
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omset Penjualan</p>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Penjualan berstatus selesai</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total HPP FIFO</p>
            <p class="text-2xl font-black text-slate-700 mt-1">Rp {{ number_format($totalCogs, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Harga pokok modal barang terjual</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Laba Kotor</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">Omset Penjualan - HPP FIFO</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Margin Laba Kotor</p>
            <p class="text-2xl font-black text-indigo-600 mt-1">
                {{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 1) : 0 }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">Persentase laba terhadap omset</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Customer</label>
                <select name="customer_id" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Bayar</label>
                <select name="payment_method" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash / Tunai</option>
                    <option value="transfer" {{ $paymentMethod === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="debit" {{ $paymentMethod === 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="credit" {{ $paymentMethod === 'credit' ? 'selected' : '' }}>Kredit</option>
                    <option value="tempo" {{ $paymentMethod === 'tempo' ? 'selected' : '' }}>Tempo / Piutang</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('reports.sales') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Rincian Faktur Penjualan</h2>
            <span class="text-xs text-slate-500">Total {{ $sales->total() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No Faktur</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Item & Satuan</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-right">Total (Rp)</th>
                        <th class="py-3 px-4 text-right">HPP FIFO (Rp)</th>
                        <th class="py-3 px-4 text-right">Laba Kotor (Rp)</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $s)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">{{ $s->invoice_number }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $s->sale_date ? $s->sale_date->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">{{ $s->customer?->name ?? 'Pelanggan Umum' }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $s->user?->name ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-xs text-slate-600">
                            @foreach($s->items->take(2) as $item)
                                <div>{{ $item->product?->name }} ({{ (float)$item->quantity }} {{ $item->unit_name }})</div>
                            @endforeach
                            @if($s->items->count() > 2)
                                <span class="text-slate-400 font-medium">+{{ $s->items->count() - 2 }} item lainnya</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase
                                {{ $s->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700' : ($s->payment_method === 'tempo' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $s->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($s->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600">
                            Rp {{ number_format($s->total_cogs, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold {{ $s->gross_profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($s->gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('pos.receipt', $s->id) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Struk
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Tidak ada transaksi penjualan pada kriteria filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

