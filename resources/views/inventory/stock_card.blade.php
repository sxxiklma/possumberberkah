@extends('layouts.app', ['title' => 'Kartu Stok Produk', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Kartu Stok (Buku Mutasi Barang)</h1>
        <p class="text-xs text-slate-500 mt-1">Audit trail lengkap pergerakan fisik barang (pembelian, penjualan POS, opname, barang keluar)</p>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Product & Date Filter -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('inventory.stock-card') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs items-end">
            <div class="sm:col-span-2 relative"
                 x-data="{
                    open: false,
                    search: '',
                    selectedId: '{{ $selectedProductId ?? '' }}',
                    products: {!! json_encode($products->map(fn($p) => [
                        'id' => (string)$p->id,
                        'sku' => $p->sku,
                        'name' => $p->name,
                        'stock' => $p->stock_formatted,
                    ])) !!},
                    get selectedProduct() {
                        return this.products.find(p => p.id === this.selectedId);
                    },
                    get filteredProducts() {
                        if (!this.search.trim()) return this.products;
                        const q = this.search.toLowerCase().trim();
                        return this.products.filter(p => 
                            (p.name && p.name.toLowerCase().includes(q)) || 
                            (p.sku && p.sku.toLowerCase().includes(q))
                        );
                    },
                    select(p) {
                        this.selectedId = p.id;
                        this.open = false;
                        this.search = '';
                    },
                    clear() {
                        this.selectedId = '';
                        this.open = false;
                        this.search = '';
                    }
                 }">
                <label class="block font-semibold text-slate-700 mb-1">Pilih Produk Barang:</label>
                <input type="hidden" name="product_id" :value="selectedId" required>

                <div class="relative">
                    <button type="button" 
                            @click="open = !open; if(open) { $nextTick(() => $refs.searchInput?.focus()); }"
                            class="w-full bg-slate-50 border rounded-xl px-3 py-2 text-slate-800 text-left flex items-center justify-between gap-2 transition cursor-pointer"
                            :class="selectedId ? 'border-emerald-300 bg-white font-bold' : 'border-slate-200 text-slate-400 font-medium'">
                        <div class="truncate flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span class="truncate" x-text="selectedProduct ? (selectedProduct.sku + ' • ' + selectedProduct.name) : '-- Cari / Pilih Produk untuk Kartu Stok --'"></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <template x-if="selectedProduct">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" x-text="'Sisa: ' + selectedProduct.stock"></span>
                            </template>
                            <template x-if="selectedId">
                                <span @click.stop="clear()" class="text-slate-400 hover:text-red-500 p-0.5 rounded cursor-pointer" title="Hapus pilihan">✕</span>
                            </template>
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute left-0 top-full mt-1.5 w-full bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 p-2.5 space-y-2">
                        <!-- Search Input -->
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" x-ref="searchInput" x-model="search" placeholder="Cari nama produk atau SKU..."
                                   @keydown.escape="open = false"
                                   class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:bg-white font-medium text-slate-800">
                        </div>

                        <!-- Options List -->
                        <div class="max-h-60 overflow-y-auto space-y-1">
                            <template x-for="p in filteredProducts" :key="p.id">
                                <button type="button" @click="select(p)"
                                        class="w-full text-left p-2.5 rounded-xl hover:bg-emerald-50 transition flex items-center justify-between gap-2 cursor-pointer group"
                                        :class="selectedId === p.id ? 'bg-emerald-50/80 border border-emerald-200' : ''">
                                    <div class="truncate">
                                        <div class="font-bold text-slate-800 group-hover:text-emerald-800 truncate" x-text="p.name"></div>
                                        <div class="text-[11px] text-slate-400 font-mono" x-text="'SKU: ' + p.sku"></div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="'Sisa: ' + p.stock"></span>
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
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dari Tanggal:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Sampai Tanggal:</label>
                <div class="flex gap-2">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                        Buka
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($selectedProduct)
        <!-- Product Quick Stats Header -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-mono font-bold text-emerald-600 block">{{ $selectedProduct->sku }}</span>
                <h2 class="text-base font-bold text-slate-900 mt-0.5">{{ $selectedProduct->name }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Satuan Dasar: <strong>{{ $selectedProduct->baseUnit?->code }}</strong> &bull; 
                    Lokasi Rak: <strong>{{ $selectedProduct->location?->code ?? '-' }}</strong>
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-right">
                <span class="text-xs font-semibold text-slate-500 block">Sisa Stok Fisik Saat Ini:</span>
                <span class="text-lg font-black text-slate-900 block mt-0.5">{{ $selectedProduct->stock_formatted }}</span>
                <span class="text-[11px] text-emerald-600 font-bold">Total: {{ (float)$selectedProduct->current_stock }} {{ $selectedProduct->baseUnit?->code }}</span>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Waktu / Tanggal</th>
                            <th class="p-3.5">Tipe Mutasi</th>
                            <th class="p-3.5">No Referensi</th>
                            <th class="p-3.5 text-right text-emerald-700 font-bold">Masuk (+)</th>
                            <th class="p-3.5 text-right text-rose-700 font-bold">Keluar (-)</th>
                            <th class="p-3.5 text-right">Sisa Stok</th>
                            <th class="p-3.5">Petugas</th>
                            <th class="p-3.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movements as $m)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $m->quantity > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ str_replace('_', ' ', $m->type) }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-mono font-bold text-slate-800">{{ $m->reference_number ?? '-' }}</td>
                                <td class="p-3.5 text-right font-extrabold text-emerald-600">
                                    {{ $m->quantity > 0 ? '+' . (float)$m->quantity : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-extrabold text-rose-600">
                                    {{ $m->quantity < 0 ? (float)abs($m->quantity) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-black text-slate-900 bg-slate-50/40">
                                    {{ (float)$m->quantity_after }} {{ $selectedProduct->baseUnit?->code }}
                                </td>
                                <td class="p-3.5 text-slate-600">{{ $m->user?->name ?? 'System' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $m->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-400">Belum ada mutasi stok untuk produk ini pada rentang tanggal yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($movements, 'links'))
                <div class="p-4 border-t border-slate-100">{{ $movements->links() }}</div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-semibold text-slate-600">Pilih salah satu produk di atas untuk melihat buku kartu stok mutasi.</p>
        </div>
    @endif

</div>
@endsection

