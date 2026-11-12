@extends('layouts.app')

@section('title', 'Laporan Persediaan & Nilai Stok')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Persediaan Barang (Inventory Valuation)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Penilaian aset persediaan fisik toko berdasarkan metode penentuan harga perolehan batch FIFO.</p>
        </div>
        <div>
            <a href="{{ route('reports.inventory', ['export' => 'pdf']) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Nilai Persediaan (Aset Stok)</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Dihitung otomatis dari batch FIFO aktif</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kuantitas Unit Fisik</p>
            <p class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($totalQuantity, 0, ',', '.') }} Item</p>
            <p class="text-xs text-slate-400 mt-1">Dalam satuan dasar masing-masing</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jumlah Variasi Produk</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $products->count() }} SKU</p>
            <p class="text-xs text-slate-400 mt-1">Master produk aktif</p>
        </div>
    </div>

    <!-- Table of Inventory Valuation -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Rincian Nilai Persediaan per Produk</h2>
            <span class="text-xs text-slate-500">Kondisi per {{ date('d F Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">SKU / Barcode</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Lokasi Gudang</th>
                        <th class="py-3 px-4 text-right">Stok Fisik</th>
                        <th class="py-3 px-4">Format Multi-Satuan</th>
                        <th class="py-3 px-4 text-right">Harga Modal Dasar</th>
                        <th class="py-3 px-4 text-right">Total Nilai Aset (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-medium text-slate-800 text-xs">
                            <div>{{ $p->sku }}</div>
                            @if($p->barcode)<div class="text-slate-400">{{ $p->barcode }}</div>@endif
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $p->name }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $p->category?->name ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">
                            {{ $p->location ? $p->location->warehouse?->name . ' - ' . $p->location->rack_code : 'Gudang Utama' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            {{ (float)$p->current_stock }} {{ $p->baseUnit?->symbol }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-100">
                                {{ $p->composite_stock }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600">
                            Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600">
                            Rp {{ number_format($p->inventory_value, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            Tidak ada data produk dalam persediaan.
                        </td>
                    </tr>
                    @endforelse
                    <tr class="bg-slate-50 font-bold border-t-2 border-slate-200">
                        <td colspan="4" class="py-3.5 px-4 text-slate-800">TOTAL NILAI ASET PERSEDIAAN</td>
                        <td class="py-3.5 px-4 text-right text-indigo-700">{{ number_format($totalQuantity, 0, ',', '.') }} Unit</td>
                        <td></td>
                        <td></td>
                        <td class="py-3.5 px-4 text-right text-emerald-700 text-base">Rp {{ number_format($totalValuation, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

