@extends('layouts.app', ['title' => 'Detail Produk ' . $product->name, 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $product->name }}</h1>
        <p class="text-xs text-slate-500 mt-1">Detail spesifikasi, batch persediaan FIFO aktif, dan riwayat mutasi stok</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.stock-card', $product->id) }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Lihat Kartu Stok Lengkap
        </a>
        <button type="button" @click="$dispatch('open-stock-modal')" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Isi / Tambah Stok Fisik</span>
        </button>
        <a href="{{ route('master.products.edit', $product) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition">
            Edit Produk
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="productDetailManager()" @open-stock-modal.window="stockModalOpen = true">

    <!-- Top Card: Summary & Info -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="flex items-center gap-4">
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-slate-900 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm relative group">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div>
                <span class="text-xs font-mono font-bold text-emerald-600 block">{{ $product->sku }}</span>
                <h2 class="text-base font-bold text-slate-900 mt-0.5 leading-snug">{{ $product->name }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $product->category?->name }} &bull; {{ $product->brand?->name ?? 'Tanpa Brand' }}</p>
                <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @if($product->expiry_date)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Exp: {{ $product->expiry_date->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stock & Valuation -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Stok Fisik Tersedia:</span>
                <span class="text-xl font-black text-slate-900 block mt-1">{{ $product->stock_formatted }}</span>
                <span class="text-xs text-slate-500">Total: {{ (float)$product->current_stock }} {{ $product->baseUnit?->code }}</span>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500">Nilai Persediaan:</span>
                <span class="font-bold text-emerald-700">Rp {{ number_format($product->inventory_value, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Prices -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Harga Beli Dasar:</span>
                <span class="font-bold text-slate-800">Rp {{ number_format($product->buy_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Harga Jual Retail:</span>
                <span class="font-bold text-emerald-600">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Harga Jual Grosir:</span>
                <span class="font-bold text-slate-700">Rp {{ number_format($product->wholesale_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                <span class="text-slate-500">Lokasi Rak:</span>
                <span class="font-mono font-bold text-slate-800">{{ $product->location?->code ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Active FIFO Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Batch Persediaan FIFO Aktif</h3>
            <p class="text-[11px] text-slate-400">Penjualan di kasir akan mengambil kuantitas dari batch teratas terlebih dahulu (FIFO)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3">Prioritas FIFO</th>
                        <th class="p-3">Nomor Batch</th>
                        <th class="p-3">Tgl Masuk</th>
                        <th class="p-3">Kedaluwarsa</th>
                        <th class="p-3">Harga Modal (Batch)</th>
                        <th class="p-3">Kuantitas Awal</th>
                        <th class="p-3">Kuantitas Tersedia</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->activeBatches as $idx => $batch)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">
                                    {{ $idx + 1 }}
                                </span>
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-800">{{ $batch->batch_number }}</td>
                            <td class="p-3 text-slate-600">{{ $batch->received_date->format('d/m/Y') }}</td>
                            <td class="p-3 text-slate-600">
                                @if($batch->expiry_date)
                                    <span class="{{ $batch->isExpired() ? 'text-red-600 font-bold' : '' }}">
                                        {{ $batch->expiry_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3 font-bold text-slate-700">Rp {{ number_format($batch->cost_price, 0, ',', '.') }}</td>
                            <td class="p-3 text-slate-500">{{ (float)$batch->initial_quantity }} {{ $product->baseUnit?->code }}</td>
                            <td class="p-3 font-extrabold text-emerald-600">{{ (float)$batch->available_quantity }} {{ $product->baseUnit?->code }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                    {{ strtoupper($batch->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">Tidak ada batch persediaan aktif saat ini (Stok 0)</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Produk ini belum memiliki kuantitas stok fisik riil di gudang.</p>
                                    </div>
                                    <button type="button" @click="stockModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Isi / Tambah Stok Fisik Sekarang</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah / Isi Stok Fisik Gudang -->
    <div x-show="stockModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50" 
         style="display: none;">
        <div @click.away="stockModalOpen = false" 
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
                <button type="button" @click="stockModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Product Summary Banner -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">{{ $product->name }}</h4>
                    <div class="text-[11px] text-slate-500 font-mono flex items-center gap-2 mt-0.5">
                        <span>SKU: <strong class="text-slate-700">{{ $product->sku }}</strong></span>
                        <span>&bull;</span>
                        <span>Satuan Dasar: <strong class="text-emerald-700">{{ $product->baseUnit?->code ?? 'PCS' }}</strong></span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Stok Saat Ini</span>
                    <span class="font-extrabold text-sm {{ $product->current_stock <= 0 ? 'text-red-600' : 'text-slate-800' }}">
                        {{ (float)$product->current_stock }} {{ $product->baseUnit?->code ?? 'PCS' }}
                    </span>
                </div>
            </div>

            <form action="{{ route('master.products.stock.add', $product) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Jumlah Fisik yang Ditambahkan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0.01" name="quantity" x-model="inputQty" required placeholder="Contoh: 20"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white text-center">
                            
                            <select name="unit_id" x-model="selectedUnitId"
                                    class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 shrink-0">
                                <option :value="baseUnitId" x-text="baseUnitCode + ' (Dasar)'"></option>
                                <template x-for="u in units" :key="u.unit_id">
                                    <option :value="u.unit_id" x-text="u.code + ' (Kemasan)'"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="selectedUnitId != baseUnitId && inputQty > 0" class="text-[10px] font-bold text-emerald-700 mt-1">
                            👉 Setara: <span x-text="calculateBaseQty()"></span> <span x-text="baseUnitCode"></span> dicatat ke persediaan
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Gudang Penyimpanan <span class="text-red-500">*</span>
                        </label>
                        <select name="warehouse_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $product->location?->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Harga Modal / Beli Batch (Rp)</label>
                        <input type="number" step="any" min="0" name="cost_price" value="{{ (float)$product->buy_price }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white text-right font-medium">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Default harga modal master</span>
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
                        <input type="text" name="notes" placeholder="Contoh: Stok awal fisik di toko"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2 text-xs">
                    <button type="button" @click="stockModalOpen = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">
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

@php
    $serializedUnits = $product->productUnits->map(function($pu) {
        return [
            'unit_id' => (string)$pu->unit_id,
            'code' => $pu->unit?->code ?? 'UNIT',
            'factor' => (float)$pu->conversion_factor,
            'buy_price' => (float)$pu->buy_price,
        ];
    })->values()->all();
@endphp

<script>
function productDetailManager() {
    return {
        stockModalOpen: false,
        inputQty: '',
        selectedUnitId: '{{ (string)($product->productUnits->first()?->unit_id ?? $product->base_unit_id) }}',
        baseUnitId: '{{ (string)$product->base_unit_id }}',
        baseUnitCode: '{{ $product->baseUnit?->code ?? "PCS" }}',
        units: @json($serializedUnits),
        calculateBaseQty() {
            const qty = parseFloat(this.inputQty) || 0;
            if (!this.selectedUnitId || String(this.selectedUnitId) === String(this.baseUnitId)) {
                return qty;
            }
            const sec = this.units.find(u => String(u.unit_id) === String(this.selectedUnitId));
            const factor = sec ? (parseFloat(sec.factor) || 1) : 1;
            return qty * factor;
        }
    };
}
</script>
@endsection

