@extends('layouts.app', ['title' => 'Ringkasan Stok Gudang', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Ringkasan Stok Gudang (Multi-Satuan)</h1>
        <p class="text-xs text-slate-500 mt-1">Status stok fisik riil, konversi kemasan, dan total nilai persediaan barang toko</p>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl text-right">
        <span class="text-[10px] text-emerald-800 font-semibold uppercase tracking-wider block">Total Nilai Persediaan:</span>
        <span class="text-base font-black text-emerald-700">Rp {{ number_format($totalValuation, 0, ',', '.') }}</span>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('inventory.stock-summary') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, SKU, barcode..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="category_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('inventory.stock-summary') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <!-- Stock Summary Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">SKU / Barcode</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5">Format Stok Multi-Satuan</th>
                        <th class="p-3.5">Total Satuan Dasar</th>
                        <th class="p-3.5">Nilai Persediaan (HPP)</th>
                        <th class="p-3.5">Lokasi Rak</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Kartu Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60">
                            <td class="p-3.5 font-mono font-bold text-slate-700">
                                <div>{{ $product->sku }}</div>
                                @if($product->barcode)
                                    <div class="text-[10px] text-slate-400">{{ $product->barcode }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 font-bold text-slate-800">
                                <a href="{{ route('master.products.show', $product) }}" class="hover:text-emerald-600">{{ $product->name }}</a>
                            </td>
                            <td class="p-3.5 text-slate-600">{{ $product->category?->name }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs inline-block">
                                    {{ $product->stock_formatted }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold text-slate-800">
                                {{ (float)$product->current_stock }} {{ $product->baseUnit?->code }}
                            </td>
                            <td class="p-3.5 font-bold text-slate-700">
                                Rp {{ number_format($product->inventory_value, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-500">
                                {{ $product->location?->code ?? '-' }}
                            </td>
                            <td class="p-3.5">
                                @if($product->current_stock <= 0)
                                    <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">HABIS</span>
                                @elseif($product->current_stock <= $product->min_stock)
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">MENIPIS</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">AMAN</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('inventory.stock-card', $product->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition inline-block" title="Kartu Stok Mutasi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Tidak ada produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection

