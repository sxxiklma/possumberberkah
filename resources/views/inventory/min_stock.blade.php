@extends('layouts.app', ['title' => 'Peringatan Stok Minimum', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Peringatan Stok Minimum & Habis</h1>
        <p class="text-xs text-slate-500 mt-1">Daftar produk yang berada pada atau di bawah batas minimum pemesanan ulang (Reorder Point)</p>
    </div>
    <a href="{{ route('transactions.purchases.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Pesanan Pembelian (PO)
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">SKU</th>
                    <th class="p-3.5">Nama Produk</th>
                    <th class="p-3.5">Kategori</th>
                    <th class="p-3.5">Stok Saat Ini</th>
                    <th class="p-3.5">Batas Minimum</th>
                    <th class="p-3.5">Kekurangan</th>
                    <th class="p-3.5">Lokasi Gudang</th>
                    <th class="p-3.5 text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/50">
                        <td class="p-3.5 font-mono font-bold text-slate-700">{{ $product->sku }}</td>
                        <td class="p-3.5 font-bold text-slate-800">
                            <a href="{{ route('master.products.show', $product) }}" class="hover:text-emerald-600">{{ $product->name }}</a>
                        </td>
                        <td class="p-3.5 text-slate-600">{{ $product->category?->name }}</td>
                        <td class="p-3.5">
                            <span class="font-extrabold block text-xs {{ $product->current_stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $product->stock_formatted }}
                            </span>
                            <span class="text-[10px] text-slate-400">({{ (float)$product->current_stock }} {{ $product->baseUnit?->code }})</span>
                        </td>
                        <td class="p-3.5 font-semibold text-slate-700">{{ (float)$product->min_stock }} {{ $product->baseUnit?->code }}</td>
                        <td class="p-3.5 font-bold text-red-600">
                            -{{ max(0, (float)$product->min_stock - (float)$product->current_stock) }} {{ $product->baseUnit?->code }}
                        </td>
                        <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $product->location?->code ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            <a href="{{ route('transactions.purchases.create') }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] inline-block transition">
                                + Reorder PO
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-bold text-slate-700 block">Kondisi Stok Aman</span>
                            <span class="text-xs">Tidak ada produk yang berada di bawah ambang batas stok minimum saat ini.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

