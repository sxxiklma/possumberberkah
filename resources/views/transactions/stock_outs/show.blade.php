@extends('layouts.app', ['title' => 'Detail Barang Keluar: ' . $stockOut->reference_number, 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Barang Keluar: {{ $stockOut->reference_number }}</h1>
        <p class="text-xs text-slate-500 mt-1">Bukti pengeluaran non-penjualan dan rincian pembebanan modal (HPP)</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('transactions.stock-outs.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            &larr; Kembali
        </a>
        <a href="{{ route('transactions.stock-outs.csv', $stockOut) }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        <a href="{{ route('transactions.stock-outs.pdf', $stockOut) }}" class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export PDF
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-slate-500 font-medium">Alasan Pengeluaran:</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block mt-1
                {{ $stockOut->reason === 'damaged' || $stockOut->reason === 'expired' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                {{ str_replace('_', ' ', $stockOut->reason) }}
            </span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Gudang Asal:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $stockOut->warehouse?->name }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Tanggal Transaksi:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $stockOut->date->format('d/m/Y') }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Petugas Pencatat:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $stockOut->user?->name }}</span>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800">Daftar Barang yang Dikeluarkan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">SKU</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5 text-center">Jumlah Keluar</th>
                        <th class="p-3.5 text-center">Satuan Dasar</th>
                        <th class="p-3.5 text-right">Nilai HPP (Rp)</th>
                        <th class="p-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($stockOut->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-700">{{ $item->product?->sku }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $item->product?->name }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-800">
                                {{ (float)$item->quantity }} {{ $item->unit?->code }}
                            </td>
                            <td class="p-3.5 text-center text-slate-600">
                                = {{ (float)$item->base_quantity }} {{ $item->product?->baseUnit?->code }}
                            </td>
                            <td class="p-3.5 text-right font-bold text-slate-900">
                                Rp {{ number_format($item->cogs_total, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-slate-500">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold">
                        <td colspan="4" class="p-3.5 text-right">TOTAL NILAI PEMBEBANAN (HPP):</td>
                        <td class="p-3.5 text-right text-rose-600" style="font-size: 13px;">Rp {{ number_format($stockOut->total_cost, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection

