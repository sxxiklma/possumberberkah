@extends('layouts.app', ['title' => 'Stok Kedaluwarsa & Hampir Expired', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Stok Kedaluwarsa & Peringatan Expired</h1>
        <p class="text-xs text-slate-500 mt-1">Pemantauan batch barang berkedaluwarsa untuk mencegah penjualan otomatis barang rusak</p>
    </div>
    <a href="{{ route('transactions.stock-outs.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Keluarkan Barang Rusak/Expired
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- 1. Sudah Kedaluwarsa (Expired) -->
    <div class="bg-white rounded-2xl border border-red-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-red-100 bg-red-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <h2 class="text-sm font-bold text-red-900">Batch Sudah Kedaluwarsa (Telah Ditolak Otomatis di Kasir)</h2>
            </div>
            <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-bold">{{ $expiredBatches->count() }} Batch</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Nomor Batch</th>
                        <th class="p-3.5">Produk</th>
                        <th class="p-3.5">Tanggal Masuk</th>
                        <th class="p-3.5">Tanggal Kedaluwarsa</th>
                        <th class="p-3.5">Sisa Fisik</th>
                        <th class="p-3.5">Harga Modal</th>
                        <th class="p-3.5">Lokasi Gudang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expiredBatches as $b)
                        <tr class="hover:bg-red-50/20 bg-red-50/10">
                            <td class="p-3.5 font-mono font-bold text-red-700">{{ $b->batch_number }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $b->product?->name }}</td>
                            <td class="p-3.5 text-slate-500">{{ $b->received_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-bold text-red-600">{{ $b->expiry_date?->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ (float)$b->available_quantity }} {{ $b->product?->baseUnit?->code }}</td>
                            <td class="p-3.5 font-medium text-slate-600">Rp {{ number_format($b->cost_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $b->location?->code ?? $b->warehouse?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">Tidak ada batch produk yang berstatus expired.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Mendekati Kedaluwarsa (< 60 Hari) -->
    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <h2 class="text-sm font-bold text-amber-900">Batch Mendekati Kedaluwarsa (Jatuh Tempo &lt; 60 Hari)</h2>
            </div>
            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">{{ $expiringSoonBatches->count() }} Batch</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Nomor Batch</th>
                        <th class="p-3.5">Produk</th>
                        <th class="p-3.5">Tanggal Masuk</th>
                        <th class="p-3.5">Tanggal Kedaluwarsa</th>
                        <th class="p-3.5">Sisa Fisik</th>
                        <th class="p-3.5">Harga Modal</th>
                        <th class="p-3.5">Lokasi Gudang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expiringSoonBatches as $b)
                        <tr class="hover:bg-amber-50/20">
                            <td class="p-3.5 font-mono font-bold text-amber-700">{{ $b->batch_number }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $b->product?->name }}</td>
                            <td class="p-3.5 text-slate-500">{{ $b->received_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-bold text-amber-700">{{ $b->expiry_date?->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ (float)$b->available_quantity }} {{ $b->product?->baseUnit?->code }}</td>
                            <td class="p-3.5 font-medium text-slate-600">Rp {{ number_format($b->cost_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $b->location?->code ?? $b->warehouse?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">Tidak ada batch produk yang mendekati masa kedaluwarsa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

