@extends('layouts.app', ['title' => 'Catat Penjualan Baru', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Catat Transaksi Penjualan</h1>
        <p class="text-xs text-slate-500 mt-1">Pencatatan langsung penjualan barang pesanan/toko tanpa harus melalui kasir pintar POS</p>
    </div>
    <a href="{{ route('transactions.sales.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali ke Daftar Penjualan
    </a>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('transactions.sales.store') }}" x-data="salesEntryForm()" class="space-y-6 max-w-5xl">
    @csrf

    <!-- Card 1: Informasi Transaksi Penjualan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h2 class="text-sm font-bold text-slate-800">Informasi Transaksi Penjualan</h2>
            <div class="text-[11px] font-mono text-slate-500">
                Estimasi No Faktur: <span class="font-bold text-emerald-700">{{ $previewInvoice }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <!-- Tanggal Penjualan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <!-- Pelanggan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    <span>Pelanggan / Customer</span>
                    <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                </label>
                <select name="customer_id" x-model="customerId" @change="onCustomerChange()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 font-medium">
                    <option value="">-- Customer Umum (Walk-In) --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-category="{{ $c->category }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ ucfirst($c->category) }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Opsional untuk pembeli eceran langsung toko.</p>
            </div>

            <!-- Tipe Harga -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Harga <span class="text-red-500">*</span></label>
                <select name="price_type" x-model="priceType" @change="reapplyPrices()" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 font-bold">
                    <option value="retail">Harga Retail (Eceran)</option>
                    <option value="wholesale">Harga Grosir (Reseller / Bakery)</option>
                </select>
            </div>

            <!-- Metode Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="payment_method" x-model="paymentMethod" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 font-semibold">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank (BCA / Mandiri / BRI)</option>
                    <option value="qris">QRIS Standar</option>
                    <option value="tempo">Tempo / Piutang Usaha</option>
                    <option value="dp">Uang Muka (DP Bertahap)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
            <!-- Jatuh Tempo (Hanya muncul jika tempo/dp/kurang bayar) -->
            <div x-show="paymentMethod === 'tempo' || paymentMethod === 'dp' || paidAmount < totalAmount">
                <label class="block text-xs font-semibold text-amber-700 mb-1 flex items-center gap-1.5">
                    <span>Jatuh Tempo Pelunasan</span>
                    <span class="text-[10px] font-normal text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded">Opsional</span>
                </label>
                <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}"
                       class="w-full bg-amber-50/50 border border-amber-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-amber-500">
                <p class="text-[10px] text-slate-400 mt-1">Batas waktu tempo (otomatis 30 hari jika tidak ditentukan).</p>
            </div>

            <!-- Catatan Transaksi -->
            <div :class="{'sm:col-span-2': paymentMethod !== 'tempo' && paymentMethod !== 'dp' && paidAmount >= totalAmount}">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Catatan Penjualan</label>
                <input type="text" name="notes" placeholder="Contoh: Pesanan via WhatsApp Bu Anik / Diambil sore hari"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
        </div>
    </div>

    <!-- Card 2: Daftar Barang yang Dijual -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Daftar Barang yang Dijual</h2>
                <p class="text-[11px] text-slate-500">Pilih produk, satuan (eceran/grosir), jumlah, dan harga jual yang berlaku</p>
            </div>
            <button type="button" @click="addItem()" class="px-3.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs hover:bg-emerald-100 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Baris
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/80 flex flex-wrap items-center gap-3 text-xs">
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
                                item.units = [];
                                item.unit_id = '';
                                item.unit_price = 0;
                                item.subtotal = 0;
                                recalcSubtotal(item);
                                this.open = false;
                                this.search = '';
                            }
                         }">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Produk <span class="text-red-500">*</span></label>
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

                    <!-- Satuan Penjualan -->
                    <div class="w-36">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Satuan</label>
                        <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" @change="onUnitSelect(item)" required
                                class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold text-emerald-800">
                            <template x-for="u in item.units" :key="u.unit_id">
                                <option :value="u.unit_id" x-text="u.unit_code + ' (' + u.unit_name + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Kuantitas -->
                    <div class="w-24">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Jumlah</label>
                        <input type="number" step="any" min="0.01" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="recalcSubtotal(item)" required
                               class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1.5 font-bold text-slate-800 text-center">
                    </div>

                    <!-- Harga Satuan (Rp) -->
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Harga Satuan (Rp)</label>
                        <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" @input="recalcSubtotal(item)" required
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 font-semibold text-right">
                    </div>

                    <!-- Diskon per Item (Rp) -->
                    <div class="w-28">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Diskon Item (Rp)</label>
                        <input type="number" step="any" min="0" :name="'items[' + index + '][discount_amount]'" x-model.number="item.discount_amount" @input="recalcSubtotal(item)"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-right">
                    </div>

                    <!-- Subtotal Item -->
                    <div class="w-28 text-right">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Subtotal</span>
                        <span class="font-bold text-emerald-700 py-1.5 block text-xs" x-text="formatRupiah(item.subtotal)"></span>
                    </div>

                    <!-- Delete button -->
                    <button type="button" @click="removeItem(index)" class="mt-4 p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-white transition" title="Hapus Baris">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Card 3: Ringkasan Total & Pembayaran -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Ringkasan Total & Pembayaran</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kolom Kiri: Diskon Tambahan & Pajak -->
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Diskon Tambahan Faktur (Rp)</label>
                    <input type="number" step="any" min="0" name="discount_amount" x-model.number="overallDiscount" @input="recalcTotals()"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pajak PPN (Rp)</label>
                    <input type="number" step="any" min="0" name="tax_amount" x-model.number="taxAmount" @input="recalcTotals()"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-semibold text-slate-700">Nominal Pembayaran Diterima (Rp) <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="setLunas()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition">
                                Bayar Lunas
                            </button>
                            <button type="button" @click="setTempo()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">
                                Belum Bayar (Tempo)
                            </button>
                        </div>
                    </div>
                    <input type="number" step="any" min="0" name="paid_amount" x-model.number="paidAmount" @input="isCustomPaid = true" required
                           class="w-full bg-emerald-50 border border-emerald-300 rounded-xl px-3 py-2 text-sm font-bold text-emerald-800 focus:outline-none focus:border-emerald-600">
                    <p class="text-[10px] text-slate-500 mt-1" x-text="paidAmount >= totalAmount ? '✅ Pembayaran LUNAS penuh. Transaksi selesai tanpa piutang.' : (paidAmount > 0 ? '⚠️ Bayar sebagian (DP). Sisa ' + formatRupiah(totalAmount - paidAmount) + ' dicatat ke buku piutang.' : '⚠️ Belum bayar. Total ' + formatRupiah(totalAmount) + ' dicatat sebagai tempo.')"></p>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Kalkulasi Akhir -->
            <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 text-xs border border-slate-200">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Subtotal Barang:</span>
                    <span class="font-bold text-slate-800" x-text="formatRupiah(subtotalAmount)"></span>
                </div>

                <div class="flex items-center justify-between text-slate-600">
                    <span>Diskon Tambahan:</span>
                    <span class="font-bold text-red-600" x-text="'- ' + formatRupiah(overallDiscount)"></span>
                </div>

                <div class="flex items-center justify-between text-slate-600">
                    <span>Pajak (PPN):</span>
                    <span class="font-bold text-slate-800" x-text="formatRupiah(taxAmount)"></span>
                </div>

                <div class="border-t border-slate-200 pt-2 flex items-center justify-between text-sm">
                    <span class="font-bold text-slate-900">Total Akhir Tagihan:</span>
                    <span class="font-extrabold text-emerald-700 text-base" x-text="formatRupiah(totalAmount)"></span>
                </div>

                <div class="border-t border-dashed border-slate-200 pt-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">Jumlah Dibayar:</span>
                    <span class="font-bold text-slate-900" x-text="formatRupiah(paidAmount)"></span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold" :class="paidAmount >= totalAmount ? 'text-emerald-700' : 'text-amber-700'"
                          x-text="paidAmount >= totalAmount ? 'Kembalian:' : 'Sisa Piutang (Kurang Bayar):'"></span>
                    <span class="font-bold" :class="paidAmount >= totalAmount ? 'text-emerald-700' : 'text-amber-700'"
                          x-text="formatRupiah(Math.abs(paidAmount - totalAmount))"></span>
                </div>

                <div class="pt-2 text-center">
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold"
                          :class="paidAmount >= totalAmount ? 'bg-emerald-100 text-emerald-800' : (paidAmount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800')">
                        STATUS: <span x-text="paidAmount >= totalAmount ? 'LUNAS' : (paidAmount > 0 ? 'DP / SEBAGIAN' : 'TEMPO / PIUTANG')"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Submit -->
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('transactions.sales.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Transaksi Penjualan
        </button>
    </div>
</form>

<script>
    function salesEntryForm() {
        const productCatalog = {!! json_encode($products->map(fn($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'stock_formatted' => $p->stock_formatted,
            'base_unit_id' => $p->base_unit_id,
            'retail_price' => (float)$p->selling_price,
            'wholesale_price' => (float)$p->wholesale_price,
            'units' => app(\App\Services\UnitConversionService::class)->getProductUnitsWithPricing($p),
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
            customerId: '{{ old("customer_id") }}',
            priceType: '{{ old("price_type", "retail") }}',
            paymentMethod: '{{ old("payment_method", "cash") }}',
            overallDiscount: {{ old('discount_amount', 0) }},
            taxAmount: {{ old('tax_amount', 0) }},
            paidAmount: {{ old('paid_amount', 0) }},
            isCustomPaid: false,
            items: [
                { product_id: '', unit_id: '', quantity: 1, unit_price: 0, discount_amount: 0, subtotal: 0, units: [] }
            ],
            init() {
                this.updateAutoPaid();
                this.$watch('paymentMethod', (newMethod) => {
                    if (newMethod === 'tempo') {
                        this.paidAmount = 0;
                        this.isCustomPaid = true;
                    } else if (newMethod === 'dp') {
                        if (this.paidAmount >= this.totalAmount || this.paidAmount <= 0) {
                            this.paidAmount = Math.round(this.totalAmount / 2);
                        }
                        this.isCustomPaid = true;
                    } else {
                        this.isCustomPaid = false;
                        this.paidAmount = this.totalAmount;
                    }
                });
            },
            setLunas() {
                this.paidAmount = this.totalAmount;
                this.isCustomPaid = false;
                if (this.paymentMethod === 'tempo') {
                    this.paymentMethod = 'cash';
                }
            },
            setTempo() {
                this.paidAmount = 0;
                this.isCustomPaid = true;
                this.paymentMethod = 'tempo';
            },
            updateAutoPaid() {
                if (!this.isCustomPaid && this.paymentMethod !== 'tempo' && this.paymentMethod !== 'dp') {
                    this.paidAmount = this.totalAmount;
                }
            },
            addItem() {
                this.items.push({
                    product_id: '',
                    unit_id: '',
                    quantity: 1,
                    unit_price: 0,
                    discount_amount: 0,
                    subtotal: 0,
                    units: []
                });
                this.updateAutoPaid();
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.updateAutoPaid();
                }
            },
            onCustomerChange() {
                // If customer is grosir / reseller category, auto suggest wholesale price
                const sel = document.querySelector('select[name="customer_id"]');
                const opt = sel.options[sel.selectedIndex];
                const cat = opt?.getAttribute('data-category');
                if (cat === 'grosir' || cat === 'reseller') {
                    this.priceType = 'wholesale';
                    this.reapplyPrices();
                }
            },
            onProductSelect(item) {
                const prod = productCatalog.find(p => p.id == item.product_id);
                if (prod) {
                    item.units = prod.units;
                    item.unit_id = prod.base_unit_id;
                    this.setPriceForUnit(item, prod);
                    this.recalcSubtotal(item);
                }
            },
            onUnitSelect(item) {
                const prod = productCatalog.find(p => p.id == item.product_id);
                if (prod) {
                    this.setPriceForUnit(item, prod);
                    this.recalcSubtotal(item);
                }
            },
            setPriceForUnit(item, prod) {
                const unitObj = item.units.find(u => Number(u.unit_id) === Number(item.unit_id));
                const factor = unitObj ? (Number(unitObj.conversion_factor) || 1) : 1;
                const baseRetail = Number(prod.retail_price) || 0;
                const baseWs = Number(prod.wholesale_price) || baseRetail;

                if (unitObj) {
                    if (this.priceType === 'wholesale') {
                        let ws = Number(unitObj.wholesale_price);
                        if (!ws || ws <= 0 || (factor > 1 && ws <= baseWs)) {
                            ws = Math.round(baseWs * factor);
                        }
                        item.unit_price = ws;
                    } else {
                        let ret = Number(unitObj.selling_price);
                        if (!ret || ret <= 0 || (factor > 1 && ret <= baseRetail)) {
                            ret = Math.round(baseRetail * factor);
                        }
                        item.unit_price = ret;
                    }
                } else {
                    item.unit_price = this.priceType === 'wholesale' ? baseWs : baseRetail;
                }
            },
            reapplyPrices() {
                this.items.forEach(item => {
                    if (item.product_id) {
                        const prod = productCatalog.find(p => p.id == item.product_id);
                        if (prod) {
                            this.setPriceForUnit(item, prod);
                            this.recalcSubtotal(item);
                        }
                    }
                });
                this.updateAutoPaid();
            },
            recalcSubtotal(item) {
                const raw = ((item.quantity || 0) * (item.unit_price || 0)) - (item.discount_amount || 0);
                item.subtotal = Math.max(0, Math.round(raw * 100) / 100);
                this.updateAutoPaid();
            },
            recalcTotals() {
                this.updateAutoPaid();
            },
            get subtotalAmount() {
                return this.items.reduce((sum, i) => sum + (i.subtotal || 0), 0);
            },
            get totalAmount() {
                return Math.max(0, this.subtotalAmount - (this.overallDiscount || 0) + (this.taxAmount || 0));
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
            }
        };
    }
</script>
@endsection

