@extends('layouts.app')

@section('title', 'Laporan Keuangan & Laba Rugi')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Keuangan & Laba Rugi (P&L)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Analisis komprehensif kinerja keuangan: Penjualan Bersih, HPP FIFO, Laba Kotor, Beban Toko, dan Laba Bersih.</p>
        </div>
        <div>
            <a href="{{ route('reports.financial', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF Resmi
            </a>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.financial') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Terapkan Periode
                </button>
                <a href="{{ route('reports.financial') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Executive Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penjualan Bersih</p>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($report['net_sales'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Bruto - Retur Penjualan</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Laba Kotor (Gross Profit)</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-700 mt-1 font-semibold">Margin {{ $report['gross_margin_percentage'] }}%</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Beban Operasional</p>
            <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($report['operational_expenses'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Biaya listrik, gaji, utilitas, dll</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-5 rounded-2xl shadow-sm text-white">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Laba Bersih (Net Profit)</p>
            <p class="text-2xl font-black mt-1">Rp {{ number_format($report['net_profit'], 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-100 mt-1 font-semibold">Net Margin {{ $report['net_margin_percentage'] }}%</p>
        </div>
    </div>

    <!-- Financial Statement Table (Buku Laporan Laba Rugi Resmi) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Laporan Laba Rugi Komprehensif</h2>
                <p class="text-xs text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Standar Akuntansi Keuangan
            </span>
        </div>

        <div class="divide-y divide-slate-100 text-sm">
            <!-- 1. PENDAPATAN USAHA -->
            <div class="p-6 space-y-3">
                <div class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-2">1. PENDAPATAN OPERASIONAL USAHA</div>
                <div class="flex justify-between items-center text-slate-700">
                    <span class="pl-4">Penjualan Kotor (Gross Sales)</span>
                    <span class="font-mono">Rp {{ number_format($report['gross_sales'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-700">
                    <span class="pl-4">Retur Penjualan (Sales Returns)</span>
                    <span class="font-mono text-rose-600">(Rp {{ number_format($report['sales_returns'], 0, ',', '.') }})</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100 font-bold text-slate-900">
                    <span>PENJUALAN BERSIH (NET SALES)</span>
                    <span class="font-mono text-base">Rp {{ number_format($report['net_sales'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 2. HARGA POKOK PENJUALAN (COGS FIFO) -->
            <div class="p-6 space-y-3 bg-slate-50/50">
                <div class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-2">2. HARGA POKOK PENJUALAN (HPP)</div>
                <div class="flex justify-between items-center text-slate-700">
                    <span class="pl-4">Harga Pokok Penjualan Metode FIFO (Actual Cost)</span>
                    <span class="font-mono text-rose-600">(Rp {{ number_format($report['fifo_cogs'], 0, ',', '.') }})</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-200 font-bold text-emerald-700">
                    <span>LABA KOTOR USAHA (GROSS PROFIT)</span>
                    <span class="font-mono text-lg font-black">Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 3. BEBAN OPERASIONAL -->
            <div class="p-6 space-y-3">
                <div class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-2">3. BEBAN OPERASIONAL TOKO</div>
                @forelse($report['expenses_by_category'] as $cat => $amount)
                <div class="flex justify-between items-center text-slate-700">
                    <span class="pl-4">{{ $cat }}</span>
                    <span class="font-mono">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="pl-4 text-xs text-slate-400 italic">Belum ada pengeluaran beban operasional tercatat pada periode ini.</div>
                @endforelse

                <div class="flex justify-between items-center pt-2 border-t border-slate-100 font-bold text-slate-900">
                    <span>TOTAL BEBAN OPERASIONAL</span>
                    <span class="font-mono text-rose-600">(Rp {{ number_format($report['operational_expenses'], 0, ',', '.') }})</span>
                </div>
            </div>

            <!-- 4. LABA BERSIH AKHIR -->
            <div class="p-6 bg-emerald-50/60 flex justify-between items-center font-black text-slate-900 border-t-2 border-emerald-500">
                <div>
                    <span class="text-base uppercase tracking-tight text-emerald-950">LABA BERSIH USAHA (NET PROFIT)</span>
                    <p class="text-xs font-normal text-emerald-800 mt-0.5">Laba Kotor - Total Beban Operasional</p>
                </div>
                <span class="font-mono text-2xl text-emerald-700">Rp {{ number_format($report['net_profit'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Balance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Piutang Usaha Belum Lunas</p>
                <p class="text-xl font-black text-amber-600 mt-1">Rp {{ number_format($report['total_receivables'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Hak tagih toko ke pelanggan</p>
            </div>
            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Hutang Usaha Belum Lunas</p>
                <p class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($report['total_payables'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Kewajiban toko kepada supplier</p>
            </div>
            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nilai Aset Stok Persediaan</p>
                <p class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($report['inventory_valuation'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Modal barang di gudang & toko</p>
            </div>
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>
</div>
@endsection

