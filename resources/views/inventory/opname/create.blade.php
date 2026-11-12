@extends('layouts.app', ['title' => 'Formulir Stok Opname', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Formulir Stok Opname Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Input kuantitas fisik riil hasil penghitungan gudang</p>
    </div>
    <a href="{{ route('inventory.opname.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('inventory.opname.store') }}" x-data="opnameForm()" class="space-y-6 max-w-5xl">
    @csrf

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Informasi Jadwal Opname</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang yang Diperiksa <span class="text-red-500">*</span></label>
                <select name="warehouse_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pemeriksaan Fisik <span class="text-red-500">*</span></label>
                <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Opname</label>
            <input type="text" name="notes" placeholder="Contoh: Opname rutin akhir bulan gudang utama"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Daftar Barang & Hasil Hitung Fisik</h2>
                <p class="text-[11px] text-slate-500">Masukkan jumlah fisik nyata yang ada di rak</p>
            </div>
            <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-xs hover:bg-emerald-100 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Baris Barang
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex flex-wrap items-center gap-3 text-xs">
                    <!-- Produk Searchable Select -->
                    <div class="flex-1 min-w-[260px] relative" 
                         x-data="{ 
                            open: false, 
                            search: '',
                            get filteredProducts() {
                                if (!this.search.trim()) return productCatalog;
                                const q = this.search.toLowerCase().trim();
                                return productCatalog.filter(p => 
                                    (p.name && p.name.toLowerCase().includes(q)) || 
                                    (p.sku && p.sku.toLowerCase().includes(q))
                                );
                            },
                            select(prod) {
                                item.product_id = prod.id;
                                onProductSelect(item);
                                this.open = false;
                                this.search = '';
                            },
                            clear() {
                                item.product_id = '';
                                item.system_qty = 0;
                                item.physical_qty = 0;
                                item.unit_code = 'PCS';
                                this.open = false;
                                this.search = '';
                            }
                         }">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Pilih Produk <span class="text-red-500">*</span></label>
                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id" required>
                        
                        <!-- Trigger Button -->
                        <div class="relative">
                            <button type="button" 
                                    @click="open = !open; if(open) { $nextTick(() => $refs.searchInput?.focus()); }"
                                    class="w-full bg-white border rounded-lg px-2.5 py-1.5 font-semibold text-left flex items-center justify-between gap-2 shadow-2xs hover:border-emerald-500 transition cursor-pointer"
                                    :class="item.product_id ? 'border-emerald-300 text-slate-800' : 'border-slate-200 text-slate-400'">
                                <div class="truncate flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="truncate font-bold" x-text="getProductName(item.product_id) || '-- Cari / Pilih Produk --'"></span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <template x-if="item.product_id">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600" x-text="'Stok: ' + getProductStock(item.product_id)"></span>
                                    </template>
                                    <template x-if="item.product_id">
                                        <span @click.stop="clear()" class="text-slate-400 hover:text-red-500 p-0.5 rounded cursor-pointer text-xs" title="Kosongkan">✕</span>
                                    </template>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <!-- Dropdown Panel -->
                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute left-0 top-full mt-1 w-full min-w-[320px] max-w-md bg-white rounded-xl shadow-2xl border border-slate-200 z-50 p-2 space-y-2">
                                <!-- Search Input with Autofocus -->
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <input type="text" x-ref="searchInput" x-model="search" placeholder="Ketik nama produk atau SKU..."
                                           @keydown.escape="open = false"
                                           class="w-full pl-8 pr-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500 focus:bg-white font-medium text-slate-800">
                                </div>

                                <!-- Filtered Results List -->
                                <div class="max-h-52 overflow-y-auto space-y-1">
                                    <template x-for="p in filteredProducts" :key="p.id">
                                        <button type="button" @click="select(p)"
                                                class="w-full text-left p-2 rounded-lg hover:bg-emerald-50 transition flex items-center justify-between gap-2 cursor-pointer group"
                                                :class="item.product_id == p.id ? 'bg-emerald-50/80 border border-emerald-200' : ''">
                                            <div class="truncate">
                                                <div class="font-bold text-slate-800 group-hover:text-emerald-800 truncate" x-text="p.name"></div>
                                                <div class="text-[10px] text-slate-400 font-mono" x-text="'SKU: ' + p.sku"></div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" 
                                                      :class="p.stock_formatted.startsWith('0') ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-700'"
                                                      x-text="'Stok: ' + p.stock_formatted"></span>
                                            </div>
                                        </button>
                                    </template>
                                    <div x-show="filteredProducts.length === 0" class="py-4 text-center text-slate-400 text-xs">
                                        Tidak ditemukan produk dengan kata kunci "<span x-text="search" class="font-semibold text-slate-600"></span>"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-28 text-center">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Stok Sistem</span>
                        <span class="font-bold text-slate-700 py-1.5 block bg-slate-200/60 rounded-lg" x-text="item.system_qty + ' ' + item.unit_code"></span>
                    </div>

                    <div class="w-36">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Fisik Nyata (Hitung)</label>
                        <input type="number" step="any" min="0" :name="'items[' + index + '][physical_quantity]'" x-model.number="item.physical_qty" required
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 font-black text-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="w-28 text-center">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Selisih (+/-)</span>
                        <span class="font-extrabold py-1.5 block rounded-lg text-xs" 
                              :class="(item.physical_qty - item.system_qty) >= 0 ? 'text-emerald-700' : 'text-rose-700'"
                              x-text="(item.physical_qty - item.system_qty)"></span>
                    </div>

                    <button type="button" @click="removeItem(index)" class="mt-4 p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('inventory.opname.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
            Simpan Draft Opname
        </button>
    </div>
</form>

<script>
    function opnameForm() {
        const productCatalog = {!! json_encode($products->map(fn($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'stock_formatted' => $p->stock_formatted,
            'system_stock' => (float)$p->current_stock,
            'unit_code' => $p->baseUnit?->code ?? 'PCS',
        ])) !!};

        return {
            productCatalog: productCatalog,
            getProductName(id) {
                const p = this.productCatalog.find(item => item.id == id);
                return p ? `${p.sku} - ${p.name}` : '';
            },
            getProductStock(id) {
                const p = this.productCatalog.find(item => item.id == id);
                return p ? p.stock_formatted : '';
            },
            items: [
                { product_id: '', system_qty: 0, physical_qty: 0, unit_code: 'PCS' }
            ],
            addItem() {
                this.items.push({ product_id: '', system_qty: 0, physical_qty: 0, unit_code: 'PCS' });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            onProductSelect(item) {
                const prod = productCatalog.find(p => p.id == item.product_id);
                if (prod) {
                    item.system_qty = prod.system_stock;
                    item.physical_qty = prod.system_stock;
                    item.unit_code = prod.unit_code;
                }
            }
        };
    }
</script>
@endsection

