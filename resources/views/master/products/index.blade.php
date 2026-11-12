@extends('layouts.app', ['title' => 'Master Produk', 'activeAccordion' => 'master'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Master Data Produk</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola katalog barang, multi-satuan, harga beli/jual, dan lokasi gudang</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('master.products.export') }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" 
                class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/></svg>
            Import Excel
        </button>
        <a href="{{ route('master.products.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>
    </div>
</div>
@endsection

@section('content')
@php
    $productsData = $products->getCollection()->mapWithKeys(function($p) use ($warehouses) {
        return [$p->id => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'base_unit_id' => $p->base_unit_id,
            'base_unit' => $p->baseUnit?->code ?? 'PCS',
            'buy_price' => (float)$p->buy_price,
            'current_stock' => (float)$p->current_stock,
            'location_id' => $p->warehouse_location_id,
            'warehouse_id' => $p->location?->warehouse_id ?? ($warehouses->first()?->id ?? 1),
            'units' => $p->productUnits->map(fn($pu) => [
                'unit_id' => $pu->unit_id,
                'code' => $pu->unit?->code,
                'factor' => (float)$pu->conversion_factor,
                'buy_price' => (float)$pu->buy_price
            ])->values()->all()
        ]];
    });
@endphp
<div class="space-y-4" x-data="productList()">
    
    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.products.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
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
            <div>
                <select name="stock_filter" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Kondisi Stok</option>
                    <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>Stok Menipis (&le; Min)</option>
                    <option value="out" {{ $stockFilter === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('master.products.index') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-800">
            <h4 class="font-bold mb-1">Catatan Import:</h4>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Produk</th>
                        <th class="p-3.5">Kategori & Brand</th>
                        <th class="p-3.5">Stok Fisik Gudang</th>
                        <th class="p-3.5">Harga Beli (Modal)</th>
                        <th class="p-3.5">Harga Jual Retail</th>
                        <th class="p-3.5">Harga Grosir</th>
                        <th class="p-3.5">Lokasi Rak</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60">
                            <td class="p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 leading-tight">{{ $product->name }}</h3>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400 font-mono flex-wrap">
                                            <span>SKU: {{ $product->sku }}</span>
                                            @if($product->barcode)
                                                <span>&bull;</span>
                                                <span>{{ $product->barcode }}</span>
                                            @endif
                                            @if($product->expiry_date)
                                                <span>&bull;</span>
                                                <span class="text-amber-600 font-semibold font-sans">Exp: {{ $product->expiry_date->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5">
                                <span class="font-semibold text-slate-700">{{ $product->category?->name }}</span>
                                <p class="text-[10px] text-slate-400">{{ $product->brand?->name ?? '-' }}</p>
                            </td>
                            <td class="p-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold text-slate-900 block" style="font-size: 13px;">
                                        {{ $product->stock_formatted }}
                                    </span>
                                    @if($product->current_stock <= 0)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Habis</span>
                                    @endif
                                </div>
                                <span class="text-[10px] {{ $product->current_stock <= $product->min_stock ? 'text-amber-600 font-bold' : 'text-slate-400' }} block">
                                    Total: {{ (float)$product->current_stock }} {{ $product->baseUnit?->code }} (Min: {{ (float)$product->min_stock }})
                                </span>
                            </td>
                            <td class="p-3.5 font-medium text-slate-600">Rp {{ number_format($product->buy_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 font-bold text-slate-700">Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-mono font-bold">
                                    {{ $product->location?->code ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            @click="openStockModal({{ $product->id }})" 
                                            class="p-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-700 transition cursor-pointer" 
                                            title="Tambah / Isi Stok Fisik">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <a href="{{ route('master.products.show', $product) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Lihat Detail & Batch">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('master.products.edit', $product) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Produk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('master.products.destroy', $product) }}" 
                                          onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Belum ada data produk yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal Import Excel / CSV -->
<div id="importModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900">Import Produk dari Excel/CSV</h3>
            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="mb-4 p-3 bg-emerald-50 rounded-xl text-xs text-emerald-800">
            <p class="font-semibold mb-1">Gunakan Template Resmi Toko Sumber Berkah:</p>
            <a href="{{ route('master.products.export-template') }}" class="inline-flex items-center gap-1.5 text-emerald-700 font-bold hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Template CSV (.csv)
            </a>
        </div>

        <form method="POST" action="{{ route('master.products.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih File (.csv / .xlsx):</label>
                <input type="file" name="file" required accept=".csv,.xlsx,.xls,.txt"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                    Proses Import Data
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Tambah / Isi Stok Fisik Gudang -->
<div x-show="stockModalOpen" 
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50" 
     style="display: none;">
    <div @click.away="closeStockModal()" 
         class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <span>Isi / Tambah Stok Fisik Produk</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Penambahan kuantitas batch fisik nyata ke kartu stok (FIFO)</p>
            </div>
            <button type="button" @click="closeStockModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Product Summary Banner -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 text-sm" x-text="selectedProduct.name"></h4>
                <div class="text-[11px] text-slate-500 font-mono flex items-center gap-2 mt-0.5">
                    <span>SKU: <strong class="text-slate-700" x-text="selectedProduct.sku"></strong></span>
                    <span>&bull;</span>
                    <span>Satuan Dasar: <strong class="text-emerald-700" x-text="selectedProduct.base_unit"></strong></span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Stok Saat Ini</span>
                <span class="font-extrabold text-sm" :class="selectedProduct.current_stock <= 0 ? 'text-red-600' : 'text-slate-800'" 
                      x-text="selectedProduct.current_stock + ' ' + selectedProduct.base_unit"></span>
            </div>
        </div>

        <form :action="actionUrl" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Jumlah Fisik yang Ditambahkan <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" step="any" min="0.01" name="quantity" x-model="inputQty" required placeholder="Contoh: 20"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white text-center">
                        
                        <select name="unit_id" x-model="selectedUnitId" @change="updateUnitCost()"
                                class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 shrink-0">
                            <option :value="selectedProduct.base_unit_id" x-text="selectedProduct.base_unit + ' (Dasar)'"></option>
                            <template x-for="u in (selectedProduct.units || [])" :key="u.unit_id">
                                <option :value="u.unit_id" x-text="u.code + ' (Kemasan)'"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="selectedUnitId != selectedProduct.base_unit_id && inputQty > 0" class="text-[10px] font-bold text-emerald-700 mt-1">
                        👉 Setara: <span x-text="calculateBaseQty()"></span> <span x-text="selectedProduct.base_unit"></span> dicatat ke persediaan
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Gudang Penyimpanan <span class="text-red-500">*</span>
                    </label>
                    <select name="warehouse_id" x-model="selectedProduct.warehouse_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Harga Modal / Beli Batch (Rp)</label>
                    <input type="number" step="any" min="0" name="cost_price" x-model="inputCostPrice"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white text-right font-medium">
                    <span class="text-[10px] text-slate-400 block mt-0.5">Default harga modal satuan yang dipilih</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Kedaluwarsa (Expired)</label>
                    <input type="date" name="expiry_date"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                    <span class="text-[10px] text-slate-400 block mt-0.5">Kosongkan jika tidak ada expired</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Batch / Lot</label>
                    <input type="text" name="batch_number" placeholder="Otomatis jika kosong"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                    <input type="text" name="notes" placeholder="Contoh: Kulakan 20 BOX (@ 12 PCS)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2 text-xs">
                <button type="button" @click="closeStockModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Stok Fisik
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
    const productsStockData = @json($productsData);

    function productList() {
        return {
            stockModalOpen: false,
            inputQty: '',
            inputCostPrice: '',
            selectedUnitId: null,
            selectedProduct: {
                id: null,
                name: '',
                sku: '',
                base_unit_id: null,
                base_unit: '',
                buy_price: 0,
                current_stock: 0,
                location_id: null,
                warehouse_id: '{{ $warehouses->first()?->id ?? 1 }}',
                units: [],
            },
            actionUrl: '',
            openStockModal(id) {
                const prod = productsStockData[id];
                if (!prod) return;
                this.selectedProduct = prod;
                this.inputQty = '';
                if (prod.units && prod.units.length > 0) {
                    this.selectedUnitId = prod.units[0].unit_id;
                    this.inputCostPrice = prod.units[0].buy_price || (prod.buy_price * prod.units[0].factor);
                } else {
                    this.selectedUnitId = prod.base_unit_id;
                    this.inputCostPrice = prod.buy_price;
                }
                this.actionUrl = '{{ url("master/products") }}/' + prod.id + '/stock';
                this.stockModalOpen = true;
            },
            updateUnitCost() {
                if (!this.selectedUnitId || this.selectedUnitId == this.selectedProduct.base_unit_id) {
                    this.inputCostPrice = this.selectedProduct.buy_price;
                } else {
                    const sec = (this.selectedProduct.units || []).find(u => u.unit_id == this.selectedUnitId);
                    this.inputCostPrice = sec ? (sec.buy_price || (this.selectedProduct.buy_price * sec.factor)) : this.selectedProduct.buy_price;
                }
            },
            closeStockModal() {
                this.stockModalOpen = false;
            },
            calculateBaseQty() {
                const qty = parseFloat(this.inputQty) || 0;
                if (!this.selectedUnitId || this.selectedUnitId == this.selectedProduct.base_unit_id) {
                    return qty;
                }
                const sec = (this.selectedProduct.units || []).find(u => u.unit_id == this.selectedUnitId);
                const factor = sec ? (parseFloat(sec.factor) || 1) : 1;
                return qty * factor;
            }
        };
    }
</script>
@endsection

