@extends('layouts.app', ['title' => 'Edit Produk: ' . $product->name, 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Edit Produk: {{ $product->name }}</h1>
        <p class="text-xs text-slate-500 mt-1">Ubah spesifikasi katalog barang, konfigurasi multi-satuan, dan foto produk</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('master.products.show', $product) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Lihat Detail
        </a>
        <a href="{{ route('master.products.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            &larr; Kembali
        </a>
    </div>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('master.products.update', $product) }}" enctype="multipart/form-data" 
      x-data="productEditForm()" class="space-y-6 max-w-5xl">
    @csrf
    @method('PUT')

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-xs mb-1.5">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Gagal Memperbarui Produk! Silakan periksa kolom berikut:
            </div>
            <ul class="list-disc list-inside text-[11px] text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 shadow-xs text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 1. Identitas & Kategori -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">1. Identitas & Kategori Produk</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">SKU Produk <span class="text-red-500">*</span></label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                       class="w-full bg-slate-50 border @error('sku') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs uppercase font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                @error('sku') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Barcode (Scan)</label>
                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                       class="w-full bg-slate-50 border @error('barcode') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-mono text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                @error('barcode') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full bg-slate-50 border @error('name') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white font-medium">
                @error('name') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Produk <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full bg-slate-50 border @error('category_id') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Merek (Brand)</label>
                <select name="brand_id" class="w-full bg-slate-50 border @error('brand_id') border-red-400 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Tanpa Brand / Polos</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Rak Gudang</label>
                <select name="warehouse_location_id" class="w-full bg-slate-50 border @error('warehouse_location_id') border-red-400 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Pilih Rak Gudang (Opsional) --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('warehouse_location_id', $product->warehouse_location_id) == $loc->id ? 'selected' : '' }}>
                            {{ $loc->warehouse?->name }} &bull; {{ $loc->code }} ({{ $loc->area }})
                        </option>
                    @endforeach
                </select>
                @error('warehouse_location_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Tgl Kedaluwarsa (Expired)</span>
                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">Opsional</span>
                </label>
                <input type="date" name="expiry_date" x-model="expiryDate" @change="onExpiryChange()" 
                       value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}"
                       class="w-full bg-slate-50 border @error('expiry_date') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Tgl kedaluwarsa barang/kemasan</span>
                @error('expiry_date') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between flex-wrap gap-2 text-xs border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700 hover:text-slate-900">
                <input type="checkbox" name="has_expiry" value="1" x-model="hasExpiry"
                       {{ old('has_expiry', $product->has_expiry) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-0">
                <span>Produk Memiliki Tanggal Kedaluwarsa (Expired Date)</span>
            </label>
            <span class="text-[11px] text-slate-400">Sistem otomatis memantau & memberi alarm saat mendekati masa kedaluwarsa</span>
        </div>
    </div>

    <!-- 2. Satuan & Konversi Multi-Satuan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between border-b border-slate-100 pb-3 gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>2. Satuan Dasar & Konversi Kemasan (Multi-Unit)</span>
                    <span class="text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full">Kemasan Grosir / Kulakan</span>
                </h2>
                <p class="text-[11px] text-slate-500">Kelola satuan dasar dan kemasan grosir/pack/dus (contoh: 1 BOX = 12 PCS)</p>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="secondaryUnits.length > 0">
                    <button type="button" @click="autoCalculateAllKemasan()" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs hover:bg-blue-100 border border-blue-200 transition flex items-center gap-1.5" title="Hitung harga kemasan otomatis berdasarkan harga dasar di Bagian 3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Hitung Otomatis Harga Kemasan</span>
                    </button>
                </template>
                <button type="button" @click="addUnitRow()" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Satuan Kemasan</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Dasar Terkecil (Base Unit) <span class="text-red-500">*</span></label>
                <select name="base_unit_id" x-model="baseUnitId" required class="w-full bg-slate-50 border @error('base_unit_id') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-bold text-emerald-700 focus:outline-none focus:border-emerald-500">
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ old('base_unit_id', $product->base_unit_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->code }} - {{ $u->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Satuan eceran terkecil (PCS, KG, BKS, dll).</p>
                @error('base_unit_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Minimum Peringatan</label>
                    <input type="number" step="any" min="0" name="min_stock" value="{{ old('min_stock', (float)$product->min_stock) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @error('min_stock') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Produk (Gram)</label>
                    <input type="number" step="any" min="0" name="weight" value="{{ old('weight', (float)$product->weight) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @error('weight') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Secondary Units Repeater -->
        <div class="space-y-4 pt-2">
            <template x-for="(unit, index) in secondaryUnits" :key="index">
                <div class="p-4 sm:p-5 rounded-2xl border-2 border-emerald-100 bg-white shadow-2xs space-y-4 hover:border-emerald-300 transition">
                    
                    <!-- Header Kemasan & Tombol Hapus -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center" x-text="index + 1">1</span>
                            <span class="text-xs font-bold text-slate-800">
                                Satuan Kemasan: <span class="text-emerald-700 font-extrabold" x-text="getUnitCode(unit.unit_id)"></span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold">
                                1 <span x-text="getUnitCode(unit.unit_id)"></span> = <span class="font-black text-emerald-700" x-text="unit.conversion_factor || 1"></span> <span x-text="getBaseUnitCode()"></span>
                            </span>
                        </div>
                        <button type="button" @click="removeUnitRow(index)" class="p-1.5 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition cursor-pointer" title="Hapus Kemasan Ini">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>

                    <!-- Baris 1: Pilihan Satuan Kemasan & Isi Rasio Konversi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Satuan Kemasan <span class="text-slate-400 font-normal">(Dus/Box/Pack/dll)</span>
                            </label>
                            <select :name="'units[' + index + '][unit_id]'" x-model="unit.unit_id" required
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->code }} - {{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Isi Per Kemasan <span class="text-slate-400 font-normal" x-text="'(Berapa ' + getBaseUnitCode() + ')'"></span>
                            </label>
                            <div class="relative">
                                <input type="number" step="any" min="1" :name="'units[' + index + '][conversion_factor]'" x-model="unit.conversion_factor" required
                                       placeholder="12" 
                                       class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-3.5 pr-14 py-2 text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-slate-400" x-text="getBaseUnitCode()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Baris 2: Struktur Harga Kemasan (3 Card Berdampingan) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Harga Beli Kemasan -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                Harga Beli Kemasan (Modal)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                <input type="number" step="any" min="0" :name="'units[' + index + '][buy_price]'" x-model="unit.buy_price"
                                       class="w-full bg-white border border-slate-300 rounded-lg pl-8 pr-2.5 py-1.5 text-xs text-right font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <template x-if="unit.buy_price > 0 && unit.conversion_factor > 0">
                                <div class="mt-2 pt-1.5 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                                    <span x-text="'= ' + formatRupiah(unit.buy_price / unit.conversion_factor) + '/' + getBaseUnitCode()"></span>
                                    <button type="button" @click="applyBaseBuyPriceFromUnit(unit)" class="text-blue-600 hover:underline font-bold text-[10px]" title="Salin harga modal eceran ke Bagian 3">
                                        Salin ke Modal
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Harga Jual Retail Kemasan -->
                        <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200">
                            <label class="block text-[11px] font-bold text-emerald-800 mb-1">
                                Harga Jual Retail Kemasan
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs font-bold text-emerald-600">Rp</span>
                                <input type="number" step="any" min="0" :name="'units[' + index + '][selling_price]'" x-model="unit.selling_price"
                                       class="w-full bg-white border border-emerald-300 rounded-lg pl-8 pr-2.5 py-1.5 text-xs text-right font-bold text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <template x-if="unit.selling_price > 0 && unit.conversion_factor > 0">
                                <div class="mt-2 pt-1.5 border-t border-emerald-200 flex items-center justify-between text-[11px] text-slate-500">
                                    <span x-text="'= ' + formatRupiah(unit.selling_price / unit.conversion_factor) + '/' + getBaseUnitCode()"></span>
                                    <button type="button" @click="applyBaseSellingPriceFromUnit(unit)" class="text-emerald-700 hover:underline font-bold text-[10px]" title="Salin harga jual eceran ke Bagian 3">
                                        Salin ke Retail
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Harga Jual Grosir Kemasan -->
                        <div class="p-3 rounded-xl bg-purple-50/50 border border-purple-200">
                            <label class="block text-[11px] font-bold text-purple-800 mb-1">
                                Harga Jual Grosir Kemasan
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs font-bold text-purple-600">Rp</span>
                                <input type="number" step="any" min="0" :name="'units[' + index + '][wholesale_price]'" x-model="unit.wholesale_price"
                                       class="w-full bg-white border border-purple-300 rounded-lg pl-8 pr-2.5 py-1.5 text-xs text-right font-bold text-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <template x-if="unit.wholesale_price > 0 && unit.conversion_factor > 0">
                                <div class="mt-2 pt-1.5 border-t border-purple-200 text-[11px] text-slate-500">
                                    <span x-text="'= ' + formatRupiah(unit.wholesale_price / unit.conversion_factor) + '/' + getBaseUnitCode()"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Baris 3: Simulasi Pembelian -->
                    <div class="p-3 rounded-xl bg-gradient-to-r from-emerald-50/90 to-teal-50/90 border border-emerald-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">&check;</span>
                            <span class="font-bold text-slate-700">Simulasi Pembelian:</span>
                            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-lg border border-emerald-300 shadow-2xs">
                                <span class="text-slate-500">Beli</span>
                                <input type="number" min="0" step="any" x-model.number="unit.sim_qty"
                                       class="w-14 text-center font-black text-emerald-900 focus:outline-none">
                                <span class="font-bold text-emerald-800" x-text="getUnitCode(unit.unit_id)"></span>
                            </div>
                            <span class="text-emerald-500 font-bold">&rarr;</span>
                            <div class="flex items-center gap-1 font-bold">
                                <span class="text-slate-600">Terhitung:</span>
                                <span class="bg-white px-2.5 py-1 rounded-lg border border-emerald-300 text-emerald-800 font-black shadow-2xs"
                                      x-text="((parseFloat(unit.sim_qty) || 0) * (parseFloat(unit.conversion_factor) || 1)) + ' ' + getBaseUnitCode()"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 justify-end flex-wrap">
                            <template x-if="unit.buy_price > 0 && unit.sim_qty > 0">
                                <span class="text-[11px] text-emerald-800 font-semibold">
                                    Total Modal: <span class="font-bold" x-text="formatRupiah((parseFloat(unit.sim_qty) || 0) * parseFloat(unit.buy_price))"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <!-- 3. Penetapan Harga Dasar & Foto -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>3. Harga Per Satuan Dasar & Foto</span>
                    <span class="text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200 px-2 py-0.5 rounded-full">Eceran Terkecil (Base Unit)</span>
                </h2>
                <p class="text-[11px] text-slate-500">Harga per 1 <span class="font-bold text-emerald-700" x-text="getBaseUnitCode()"></span> saat dijual satuan eceran ke pembeli</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    <span>Harga Beli / Modal Dasar (Rp)</span> <span class="text-red-500">*</span>
                    <span class="text-[10px] font-normal text-slate-400" x-text="'(per 1 ' + getBaseUnitCode() + ')'"></span>
                </label>
                <input type="number" step="any" min="0" name="buy_price" x-model.number="buyPrice" required
                       class="w-full bg-slate-50 border @error('buy_price') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white text-right">
                @error('buy_price') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    <span>Harga Jual Retail Dasar (Rp)</span> <span class="text-red-500">*</span>
                    <span class="text-[10px] font-normal text-slate-400" x-text="'(per 1 ' + getBaseUnitCode() + ')'"></span>
                </label>
                <input type="number" step="any" min="0" name="selling_price" x-model.number="sellingPrice" required
                       class="w-full bg-slate-50 border @error('selling_price') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-bold text-emerald-600 focus:outline-none focus:border-emerald-500 focus:bg-white text-right">
                @error('selling_price') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    <span>Harga Jual Grosir Dasar (Rp)</span>
                    <span class="text-[10px] font-normal text-slate-400" x-text="'(per 1 ' + getBaseUnitCode() + ')'"></span>
                </label>
                <input type="number" step="any" min="0" name="wholesale_price" x-model.number="wholesalePrice"
                       class="w-full bg-slate-50 border @error('wholesale_price') border-red-400 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white text-right">
                @error('wholesale_price') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
            <!-- Upload Foto Produk -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Foto Produk</label>
                <div class="space-y-2">
                    <input type="file" name="image" id="editImageInput" accept="image/jpeg,image/png,image/webp,image/gif"
                           @change="handleImageChange($event)"
                           class="w-full bg-slate-50 border @error('image') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-500">Format: <strong>JPG, JPEG, PNG, WEBP, GIF</strong></span>
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Maksimal 10 MB</span>
                    </div>

                    <p x-show="imageSizeError" class="text-xs text-red-600 font-bold mt-1" x-text="imageSizeError"></p>
                    @error('image') <span class="text-[11px] text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror

                    <!-- Preview foto baru vs foto lama -->
                    <template x-if="imagePreview">
                        <div class="mt-2.5 p-2 bg-slate-50 rounded-xl border border-slate-200 inline-flex items-center gap-3">
                            <img :src="imagePreview" class="w-16 h-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                            <div class="text-xs">
                                <span class="font-bold text-emerald-800 block">Foto Baru Terpilih</span>
                                <span class="text-[10px] text-slate-500" x-text="imageName + ' (' + imageSizeFormatted + ')'"></span>
                                <button type="button" @click="clearImage()" class="text-[10px] text-red-600 hover:underline block mt-0.5 font-semibold">
                                    Batal Ganti Foto
                                </button>
                            </div>
                        </div>
                    </template>

                    @if($product->image)
                        <div x-show="!imagePreview" class="mt-2.5 flex items-center gap-2 p-2 bg-slate-50 rounded-xl border border-slate-200 w-fit">
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                            <span class="text-[11px] text-slate-500 font-medium">Foto produk aktif saat ini</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-3">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-0">
                    <span>Status Produk Aktif (Dapat Dijual di Kasir & Transaksi)</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Tambahan / Spesifikasi</label>
            <textarea name="description" rows="3"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">{{ old('description', $product->description) }}</textarea>
            @error('description') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('master.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Perubahan Produk
        </button>
    </div>
</form>

<script>
    function productEditForm() {
        const rawUnits = {!! json_encode(old('units', $product->productUnits->map(fn($pu) => [
            'unit_id' => $pu->unit_id,
            'conversion_factor' => (float)$pu->conversion_factor,
            'buy_price' => (float)$pu->buy_price,
            'selling_price' => (float)$pu->selling_price,
            'wholesale_price' => (float)$pu->wholesale_price,
            'sim_qty' => 20,
        ])->toArray())) !!};

        const mappedUnits = (rawUnits || []).map(u => ({
            ...u,
            sim_qty: u.sim_qty || 20
        }));

        return {
            baseUnitId: '{{ old("base_unit_id", $product->base_unit_id) }}',
            unitsMap: @json($units->pluck('code', 'id')),
            buyPrice: {{ old('buy_price', $product->buy_price) }},
            sellingPrice: {{ old('selling_price', $product->selling_price) }},
            wholesalePrice: {{ old('wholesale_price', $product->wholesale_price) }},
            expiryDate: '{{ old("expiry_date", $product->expiry_date?->format("Y-m-d") ?? "") }}',
            hasExpiry: {{ old('has_expiry', $product->has_expiry) ? 'true' : 'false' }},
            secondaryUnits: mappedUnits,
            imagePreview: null,
            imageName: '',
            imageSizeFormatted: '',
            imageSizeError: '',
            onExpiryChange() {
                if (this.expiryDate) {
                    this.hasExpiry = true;
                }
            },
            getBaseUnitCode() {
                return this.unitsMap[this.baseUnitId] || 'PCS';
            },
            getUnitCode(id) {
                return this.unitsMap[id] || 'UNIT';
            },
            addUnitRow() {
                const defaultUnitId = '{{ $units->firstWhere("code", "BOX")?->id ?? ($units->firstWhere("code", "PACK")?->id ?? $units->first()->id) }}';
                const factor = 12;
                const bBuy = parseFloat(this.buyPrice) || 0;
                const bSell = parseFloat(this.sellingPrice) || 0;
                const bWs = parseFloat(this.wholesalePrice) || 0;

                this.secondaryUnits.push({
                    unit_id: defaultUnitId,
                    conversion_factor: factor,
                    buy_price: bBuy > 0 ? Math.round(bBuy * factor) : 0,
                    selling_price: bSell > 0 ? Math.round(bSell * factor) : 0,
                    wholesale_price: bWs > 0 ? Math.round(bWs * factor) : 0,
                    sim_qty: 20,
                });
            },
            removeUnitRow(index) {
                this.secondaryUnits.splice(index, 1);
            },
            autoCalculateAllKemasan() {
                const bBuy = parseFloat(this.buyPrice) || 0;
                const bSell = parseFloat(this.sellingPrice) || 0;
                const bWs = parseFloat(this.wholesalePrice) || 0;

                this.secondaryUnits.forEach(u => {
                    const factor = parseFloat(u.conversion_factor) || 1;
                    u.buy_price = Math.round(bBuy * factor);
                    u.selling_price = Math.round(bSell * factor);
                    u.wholesale_price = Math.round(bWs * factor);
                });

                if (window.showToast) {
                    window.showToast('success', 'Harga seluruh kemasan berhasil dihitung otomatis');
                }
            },
            applyBaseBuyPriceFromUnit(unit) {
                const factor = parseFloat(unit.conversion_factor) || 1;
                const packBuy = parseFloat(unit.buy_price) || 0;
                if (packBuy > 0 && factor > 0) {
                    this.buyPrice = Math.round(packBuy / factor);
                    if (window.showToast) {
                        window.showToast('info', 'Harga modal dasar (Bagian 3) diperbarui menjadi ' + this.formatRupiah(this.buyPrice));
                    }
                }
            },
            applyBaseSellingPriceFromUnit(unit) {
                const factor = parseFloat(unit.conversion_factor) || 1;
                const packSell = parseFloat(unit.selling_price) || 0;
                if (packSell > 0 && factor > 0) {
                    this.sellingPrice = Math.round(packSell / factor);
                    if (window.showToast) {
                        window.showToast('info', 'Harga jual retail dasar (Bagian 3) diperbarui menjadi ' + this.formatRupiah(this.sellingPrice));
                    }
                }
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
            },
            handleImageChange(e) {
                const file = e.target.files[0];
                this.imageSizeError = '';
                if (!file) {
                    this.clearImage();
                    return;
                }

                // Max size check: 10 MB (10 * 1024 * 1024 bytes)
                const maxBytes = 10 * 1024 * 1024;
                if (file.size > maxBytes) {
                    this.imageSizeError = `Ukuran file terlalu besar (${(file.size / (1024 * 1024)).toFixed(2)} MB). Maksimal ukuran file foto adalah 10 MB.`;
                    e.target.value = '';
                    this.clearImage();
                    return;
                }

                this.imageName = file.name;
                this.imageSizeFormatted = (file.size / 1024).toFixed(1) + ' KB';

                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(file);
            },
            clearImage() {
                this.imagePreview = null;
                this.imageName = '';
                this.imageSizeFormatted = '';
                const input = document.getElementById('editImageInput');
                if (input) input.value = '';
            }
        };
    }
</script>
@endsection
