@extends('layouts.app')

@section('title', 'Laporan Pembelian Barang')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Pembelian (Purchase Report)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Monitoring pengadaan barang dari supplier, realisasi pembayaran, dan status hutang dagang.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.purchases', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Nilai Pembelian</p>
            <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Pembelian barang yang telah diterima</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Telah Dibayar</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Realisasi kas keluar ke supplier</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sisa Hutang Dagang</p>
            <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Hutang tempo yang belum lunas</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.purchases') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('reports.purchases') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Rincian Pembelian Barang Masuk</h2>
            <span class="text-xs text-slate-500">Total {{ $purchases->total() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No PO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">Status Penerimaan</th>
                        <th class="py-3 px-4">Status Bayar</th>
                        <th class="py-3 px-4 text-right">Total (Rp)</th>
                        <th class="py-3 px-4 text-right">Dibayar (Rp)</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">{{ $p->purchase_number }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $p->purchase_date ? \Carbon\Carbon::parse($p->purchase_date)->format('d/m/Y') : '-' }}</td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">{{ $p->supplier?->name }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $p->warehouse?->name ?? 'Gudang Utama' }}</td>
                        <td class="py-3.5 px-4">
                            @if($p->status === 'received')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Diterima</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($p->payment_status === 'paid')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Lunas</span>
                            @elseif($p->payment_status === 'partial')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">Sebagian</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-700">
                            Rp {{ number_format($p->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('purchases.show', $p->id) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Tidak ada data pembelian pada periode terpilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

