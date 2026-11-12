@extends('layouts.app', ['title' => 'Tambah Produk Baru', 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Tambah Produk Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Form input master katalog barang dan konfigurasi multi-satuan</p>
    </div>
    <a href="{{ route('master.products.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali ke Daftar Produk
    </a>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('master.products.store') }}" enctype="multipart/form-data" 
      x-data="productForm()" class="space-y-6 max-w-5xl">
    @csrf

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-xs mb-1.5">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Gagal Menyimpan Produk! Silakan periksa kolom berikut:
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

    <!-- 1. Informasi Dasar Produk -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">1. Identitas & Kategori Produk</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">SKU Produk <span class="text-red-500">*</span></label>
                <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="Contoh: TPG-SGT-01KG"
                       class="w-full bg-slate-50 border @error('sku') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs uppercase font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                @error('sku') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Barcode (Scan)</label>
                <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="Contoh: 899123456001"
                       class="w-full bg-slate-50 border @error('barcode') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-mono text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                @error('barcode') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Tepung Terigu Segitiga Biru 1 kg"
                       class="w-full bg-slate-50 border @error('name') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white font-medium">
                @error('name') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Produk <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full bg-slate-50 border @error('category_id') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Merek (Brand)</label>
                <select name="brand_id" class="w-full bg-slate-50 border @error('brand_id') border-red-400 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Tanpa Brand / Polos</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Rak Gudang</label>
                <select name="warehouse_location_id" class="w-full bg-slate-50 border @error('warehouse_location_id') border-red-400 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Pilih Rak Gudang (Opsional) --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('warehouse_location_id') == $loc->id ? 'selected' : '' }}>
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
                <input type="date" name="expiry_date" x-model="expiryDate" @change="onExpiryChange()" value="{{ old('expiry_date') }}"
                       class="w-full bg-slate-50 border @error('expiry_date') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Tgl kedaluwarsa barang/kemasan</span>
                @error('expiry_date') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between flex-wrap gap-2 text-xs border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700 hover:text-slate-900">
                <input type="checkbox" name="has_expiry" value="1" x-model="hasExpiry"
                       {{ old('has_expiry') ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-0">
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
                <p class="text-[11px] text-slate-500">Tentukan satuan terkecil (eceran) dan satuan kemasan lebih besar (contoh: 1 BOX = 12 PCS)</p>
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
                        <option value="{{ $u->id }}" {{ old('base_unit_id', $units->firstWhere('code', 'PCS')?->id) == $u->id ? 'selected' : '' }}>
                            {{ $u->code }} - {{ $u->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Pilih satuan eceran terkecil (contoh: PCS untuk sachet, KG untuk tepung, BKS untuk bungkusan).</p>
                @error('base_unit_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Minimum Peringatan</label>
                    <input type="number" step="any" min="0" name="min_stock" value="{{ old('min_stock', 10) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @error('min_stock') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Produk (Gram)</label>
                    <input type="number" step="any" min="0" name="weight" value="{{ old('weight', 0) }}"
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

                    <!-- Baris 3: Interactive Live Conversion & Purchase Calculator -->
                    <div class="p-3 rounded-xl bg-gradient-to-r from-emerald-50/90 to-teal-50/90 border border-emerald-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">&check;</span>
                            <span class="font-bold text-slate-700">Simulasi Pembelian:</span>
                            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-lg border border-emerald-300 shadow-2xs">
                                <span class="text-slate-500">Beli</span>
                                <input type="number" min="0" step="any" x-model.number="unit.sim_qty" @input="syncUnitToInitialStock(unit)"
                                       class="w-14 text-center font-black text-emerald-900 focus:outline-none">
                                <span class="font-bold text-emerald-800" x-text="getUnitCode(unit.unit_id)"></span>
                            </div>
                            <span class="text-emerald-500 font-bold">&rarr;</span>
                            <span class="bg-white px-2.5 py-1 rounded-lg border border-emerald-300 text-emerald-800 font-black shadow-2xs"
                                  x-text="((parseFloat(unit.sim_qty) || 0) * (parseFloat(unit.conversion_factor) || 1)) + ' ' + getBaseUnitCode()"></span>
                        </div>

                        <div class="flex items-center gap-2 justify-end flex-wrap">
                            <template x-if="unit.buy_price > 0 && unit.sim_qty > 0">
                                <span class="text-[11px] text-emerald-800 font-semibold">
                                     Total Modal: <span class="font-bold" x-text="formatRupiah((parseFloat(unit.sim_qty) || 0) * parseFloat(unit.buy_price))"></span>
                                </span>
                            </template>
                            <button type="button" @click="applyPurchaseAsInitialStock(unit)"
                                    class="px-3 py-1.5 rounded-lg font-bold text-xs transition cursor-pointer flex items-center gap-1.5 shadow-2xs"
                                    :class="isUnitActiveInInitialStock(unit) ? 'bg-emerald-700 text-white ring-2 ring-emerald-400' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                                <span x-text="isUnitActiveInInitialStock(unit) ? '✓ Dipakai di Stok Awal' : '+ Masukkan ke Stok Awal'"></span>
                            </button>
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
            <!-- Upload Foto Produk dengan Validasi Ukuran & Live Preview -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Produk</label>
                <div class="space-y-2">
                    <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif"
                           @change="handleImageChange($event)"
                           class="w-full bg-slate-50 border @error('image') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-500">Format: <strong>JPG, JPEG, PNG, WEBP, GIF</strong></span>
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Maksimal 10 MB</span>
                    </div>

                    <p x-show="imageSizeError" class="text-xs text-red-600 font-bold mt-1" x-text="imageSizeError"></p>
                    @error('image') <span class="text-[11px] text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror

                    <!-- Live Image Preview -->
                    <template x-if="imagePreview">
                        <div class="mt-2.5 p-2 bg-slate-50 rounded-xl border border-slate-200 inline-flex items-center gap-3">
                            <img :src="imagePreview" class="w-16 h-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 block" x-text="imageName"></span>
                                <span class="text-[10px] text-slate-500" x-text="imageSizeFormatted"></span>
                                <button type="button" @click="clearImage()" class="text-[10px] text-red-600 hover:underline block mt-0.5 font-semibold">
                                    Hapus Foto
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Tambahan / Catatan Spesifikasi</label>
                <textarea name="description" rows="4" placeholder="Deskripsi rasa, kegunaan, komposisi, atau catatan khusus lainnya..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">{{ old('description') }}</textarea>
                @error('description') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- 4. Saldo Stok Awal Fisik Toko & Gudang -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>4. Saldo Stok Awal Fisik Toko & Gudang</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wider">Opsional / FIFO Ready</span>
                </h2>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Masukkan kuantitas fisik riil barang saat ini agar langsung terbit batch FIFO & tercatat di stok gudang.
                </p>
            </div>
        </div>

        <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 flex items-start gap-2.5">
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="space-y-1 text-[11px]">
                <p class="font-bold text-blue-950">Perbedaan Stok Awal Fisik vs Stok Minimum Peringatan:</p>
                <p class="leading-relaxed">
                    &bull; <strong>Saldo Stok Awal Fisik:</strong> Kuantitas nyata barang yang ada di rak toko / gudang saat produk ini didaftarkan.<br>
                    &bull; <strong>Stok Minimum Peringatan (Bagian 2 di atas):</strong> Batas alarm (warning) jika stok menipis, bukan jumlah barang fisiknya.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Jumlah Stok Fisik Awal
                </label>
                <div class="flex items-center gap-1.5">
                    <input type="number" step="any" min="0" name="initial_stock" x-model="initialStockQty"
                           placeholder="0 (isi jika ada)"
                           class="w-full bg-slate-50 border @error('initial_stock') border-red-400 bg-red-50/30 @else border-slate-200 @enderror rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-emerald-500 focus:bg-white text-center">
                    
                    <select name="initial_stock_unit_id" x-model="initialStockUnitId"
                            class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 shrink-0">
                        <option :value="baseUnitId" x-text="getBaseUnitCode() + ' (Dasar)'"></option>
                        <template x-for="sec in secondaryUnits" :key="sec.unit_id">
                            <option :value="sec.unit_id" x-text="getUnitCode(sec.unit_id) + ' (Kemasan)'"></option>
                        </template>
                    </select>
                </div>
                <template x-if="initialStockQty > 0">
                    <div class="text-[11px] font-bold text-emerald-700 mt-1.5 flex items-center gap-1">
                        <span>✅</span>
                        <span>Stok awal: <strong x-text="initialStockQty"></strong> <strong x-text="getUnitCode(initialStockUnitId)"></strong> (<span x-text="getInitialStockBaseQty() + ' ' + getBaseUnitCode()"></span>) langsung aktif di persediaan.</span>
                    </div>
                </template>
                <template x-if="!initialStockQty || initialStockQty == 0">
                    <span class="text-[10px] text-slate-400 mt-1 block">Biarkan kosong/0 jika stok fisik belum ada di toko.</span>
                </template>
                @error('initial_stock') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Penyimpanan</label>
                <select name="initial_warehouse_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('initial_warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
                @error('initial_warehouse_id') <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Batch / Lot</label>
                <input type="text" name="initial_batch_number" value="{{ old('initial_batch_number') }}"
                       placeholder="Otomatis jika dikosongkan"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                <span class="text-[10px] text-slate-400 block mt-1">Opsional (cth: BATCH-01)</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Kedaluwarsa (Expired)</label>
                <input type="date" name="initial_expiry_date" x-model="expiryDate"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                <span class="text-[10px] text-emerald-600 font-medium block mt-1">✓ Tersinkron dengan tanggal di Bagian 1</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('master.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Produk Baru
        </button>
    </div>
</form>

<script>
    function productForm() {
        const rawSecondary = {!! json_encode(old('units', [])) !!};
        const mappedSecondary = (rawSecondary || []).map(u => ({
            ...u,
            sim_qty: u.sim_qty || 20
        }));

        return {
            baseUnitId: '{{ old("base_unit_id", $units->firstWhere("code", "PCS")?->id ?? $units->first()->id) }}',
            unitsMap: @json($units->pluck('code', 'id')),
            buyPrice: {{ old('buy_price', 0) }},
            sellingPrice: {{ old('selling_price', 0) }},
            wholesalePrice: {{ old('wholesale_price', 0) }},
            initialStockQty: '{{ old("initial_stock", "") }}',
            initialStockUnitId: '{{ old("initial_stock_unit_id", "") }}' || '{{ old("base_unit_id", $units->firstWhere("code", "PCS")?->id ?? $units->first()->id) }}',
            expiryDate: '{{ old("expiry_date", old("initial_expiry_date", "")) }}',
            hasExpiry: {{ old('has_expiry') ? 'true' : (old('expiry_date') || old('initial_expiry_date') ? 'true' : 'false') }},
            secondaryUnits: mappedSecondary,
            imagePreview: null,
            imageName: '',
            imageSizeFormatted: '',
            imageSizeError: '',
            init() {
                this.$watch('baseUnitId', (val) => {
                    if (!this.secondaryUnits.some(u => String(u.unit_id) === String(this.initialStockUnitId))) {
                        this.initialStockUnitId = val;
                    }
                });
            },
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
            getInitialStockBaseQty() {
                const qty = parseFloat(this.initialStockQty) || 0;
                if (!this.initialStockUnitId || this.initialStockUnitId == this.baseUnitId) {
                    return qty;
                }
                const sec = this.secondaryUnits.find(u => u.unit_id == this.initialStockUnitId);
                const factor = sec ? (parseFloat(sec.conversion_factor) || 1) : 1;
                return qty * factor;
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
                const removed = this.secondaryUnits[index];
                if (removed && String(this.initialStockUnitId) === String(removed.unit_id)) {
                    this.initialStockUnitId = this.baseUnitId;
                }
                this.secondaryUnits.splice(index, 1);
            },
            applyPurchaseAsInitialStock(unit) {
                const qty = parseFloat(unit.sim_qty) || 0;
                if (qty > 0) {
                    this.initialStockQty = qty;
                    this.initialStockUnitId = unit.unit_id;
                    if (window.showToast) {
                        window.showToast('success', `Stok awal diatur: ${qty} ${this.getUnitCode(unit.unit_id)} (${this.getInitialStockBaseQty()} ${this.getBaseUnitCode()})`);
                    }
                }
            },
            syncUnitToInitialStock(unit) {
                if (this.isUnitActiveInInitialStock(unit)) {
                    this.initialStockQty = parseFloat(unit.sim_qty) || 0;
                }
            },
            isUnitActiveInInitialStock(unit) {
                return String(this.initialStockUnitId) === String(unit.unit_id) && parseFloat(this.initialStockQty) > 0;
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
                const input = document.getElementById('imageInput');
                if (input) input.value = '';
            }
        };
    }
</script>
@endsection
