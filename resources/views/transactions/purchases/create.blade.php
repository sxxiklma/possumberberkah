@extends('layouts.app', ['title' => 'Buat Pembelian (PO)', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Buat Pembelian Barang Masuk (PO)</h1>
        <p class="text-xs text-slate-500 mt-1">Order pembelian ke supplier dan penerimaan batch stok ke gudang</p>
    </div>
    <a href="{{ route('transactions.purchases.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('transactions.purchases.store') }}" enctype="multipart/form-data" x-data="purchaseForm()" class="space-y-6 max-w-5xl">
    @csrf

    <!-- 1. Meta PO -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">1. Informasi Supplier & Dokumen</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                <select name="supplier_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 font-semibold">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Penyimpanan <span class="text-red-500">*</span></label>
                <select name="warehouse_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Invoice / Surat Jalan Supplier</label>
                <input type="text" name="invoice_number" placeholder="Contoh: INV-SUP-99882"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                <input type="text" name="notes" placeholder="Catatan PO / syarat pembayaran"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <!-- Foto Bukti Invoice / Surat Jalan -->
        <div class="pt-3 border-t border-slate-100">
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                Foto Bukti Invoice / Nota Supplier <span class="text-slate-400 font-normal">(Opsional - JPG, PNG, WEBP maks 10 MB)</span>
            </label>
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <div class="relative border-2 border-dashed border-slate-200 hover:border-emerald-500 rounded-xl p-3 text-center transition cursor-pointer bg-slate-50/50 hover:bg-emerald-50/20"
                         @click="$refs.invoiceInput.click()">
                        <input type="file" name="invoice_image" x-ref="invoiceInput" accept="image/*" class="hidden"
                               @change="previewInvoice($event)">
                        <div class="flex items-center justify-center gap-2 text-xs text-slate-600">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="font-bold text-emerald-700" x-text="invoiceName ? invoiceName : 'Klik untuk Pilih / Unggah Bukti Foto Invoice'"></span>
                            <span class="text-slate-400" x-show="!invoiceName">atau drag & drop foto ke sini</span>
                        </div>
                    </div>
                </div>
                <template x-if="invoicePreview">
                    <div class="relative group shrink-0">
                        <img :src="invoicePreview" class="w-16 h-16 object-cover rounded-xl border border-slate-200 shadow-2xs">
                        <button type="button" @click="clearInvoice()" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow hover:bg-red-600 transition cursor-pointer" title="Hapus foto">
                            ✕
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 2. Items List -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800">2. Rincian Barang yang Dipesan / Diterima</h2>
                <p class="text-[11px] text-slate-500">Pilih produk, satuan beli, kuantitas, harga, dan tanggal expired (jika ada)</p>
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
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-700" 
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

                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Satuan Pembelian</label>
                        <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" @change="onUnitChange(item)" required
                                class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 font-semibold text-slate-800">
                            <template x-for="u in item.units" :key="u.unit_id">
                                <option :value="u.unit_id" x-text="u.unit_code"></option>
                            </template>
                        </select>
                    </div>

                    <div class="w-24">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Kuantitas</label>
                        <input type="number" step="any" min="0.01" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="recalcSubtotal(item)" required
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold text-slate-800 text-center">
                    </div>

                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Harga Beli Satuan (Rp)</label>
                        <input type="number" step="any" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" @input="recalcSubtotal(item)" required
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 font-semibold">
                    </div>

                    <div class="w-28">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Expired (Opsi)</label>
                        <input type="date" :name="'items[' + index + '][expiry_date]'" x-model="item.expiry_date"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1 text-slate-800">
                    </div>

                    <div class="w-28 text-right">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Subtotal (Rp)</span>
                        <span class="font-bold text-emerald-700 py-1.5 block" x-text="formatRupiah(item.subtotal)"></span>
                    </div>

                    <button type="button" @click="removeItem(index)" class="mt-4 p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>

                    <!-- Live Multi-Unit Purchase Conversion Indicator -->
                    <template x-if="item.product_id && item.unit_id && item.quantity > 0">
                        <div class="w-full mt-2 pt-2 border-t border-slate-200/80 flex flex-wrap items-center justify-between text-[11px] text-emerald-800 font-medium gap-2">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>
                                    Kulakan: <strong x-text="item.quantity + ' ' + getSelectedUnitCode(item)"></strong>
                                    = <strong class="bg-emerald-100 text-emerald-900 px-2 py-0.5 rounded font-black" x-text="calcBaseQty(item) + ' ' + getBaseUnitCode(item)"></strong> dicatat masuk ke stok gudang
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-500 italic bg-white px-2 py-0.5 rounded border border-slate-200" x-text="'Harga per ' + getBaseUnitCode(item) + ' = ' + formatRupiah(calcPricePerBase(item))"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- 3. Totals & Pembayaran -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800">3. Ringkasan Total & Status Pembayaran</h2>
                <p class="text-[11px] text-slate-500">Pilih status pembayaran (Lunas, DP, atau Tempo), metode bayar, dan jatuh tempo hutang</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase transition"
                  :class="paymentStatus === 'paid' ? 'bg-emerald-100 text-emerald-800' : (paymentStatus === 'partial' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800')">
                STATUS: <span x-text="paymentStatus === 'paid' ? 'LUNAS' : (paymentStatus === 'partial' ? 'DP (UANG MUKA)' : 'TEMPO (HUTANG)')"></span>
            </span>
        </div>

        <!-- Kartu Pilihan Status Pembayaran: LUNAS / DP / TEMPO -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700">Pilih Opsi Pembayaran <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- 1. LUNAS -->
                <button type="button" @click="setPaymentMode('paid')"
                        class="p-4 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center cursor-pointer relative"
                        :class="paymentStatus === 'paid' ? 'border-emerald-500 bg-emerald-50/60 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex items-center gap-2 font-black text-sm" :class="paymentStatus === 'paid' ? 'text-emerald-700' : 'text-slate-700'">
                        <span class="w-2.5 h-2.5 rounded-full" :class="paymentStatus === 'paid' ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                        LUNAS
                    </div>
                    <span class="text-[11px] mt-0.5" :class="paymentStatus === 'paid' ? 'text-emerald-600 font-semibold' : 'text-slate-400'">Bayar Penuh</span>
                </button>

                <!-- 2. DP (Uang Muka) -->
                <button type="button" @click="setPaymentMode('partial')"
                        class="p-4 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center cursor-pointer relative"
                        :class="paymentStatus === 'partial' ? 'border-blue-500 bg-blue-50/60 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex items-center gap-2 font-black text-sm" :class="paymentStatus === 'partial' ? 'text-blue-700' : 'text-slate-700'">
                        <span class="w-2.5 h-2.5 rounded-full" :class="paymentStatus === 'partial' ? 'bg-blue-500' : 'bg-slate-300'"></span>
                        DP
                    </div>
                    <span class="text-[11px] mt-0.5" :class="paymentStatus === 'partial' ? 'text-blue-600 font-semibold' : 'text-slate-400'">Uang Muka</span>
                </button>

                <!-- 3. TEMPO -->
                <button type="button" @click="setPaymentMode('unpaid')"
                        class="p-4 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center cursor-pointer relative"
                        :class="paymentStatus === 'unpaid' ? 'border-amber-500 bg-amber-50/60 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex items-center gap-2 font-black text-sm" :class="paymentStatus === 'unpaid' ? 'text-amber-700' : 'text-slate-700'">
                        <span class="w-2.5 h-2.5 rounded-full" :class="paymentStatus === 'unpaid' ? 'bg-amber-500' : 'bg-slate-300'"></span>
                        TEMPO
                    </div>
                    <span class="text-[11px] mt-0.5" :class="paymentStatus === 'unpaid' ? 'text-amber-600 font-semibold' : 'text-slate-400'">Utang Supplier</span>
                </button>
            </div>
            <input type="hidden" name="payment_status" :value="paymentStatus">
        </div>

        <!-- Rincian Input Pembayaran & Rincian Total -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Kolom Kiri: Metode Bayar, Nominal Bayar & Jatuh Tempo -->
            <div class="space-y-4 text-xs">
                <!-- Metode Pembayaran -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                    <select name="payment_method" x-model="paymentMethod"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="cash">Tunai / Kas Toko</option>
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="giro">Giro / Bilyet Giro</option>
                        <option value="qris">QRIS / Non-Tunai</option>
                        <option value="credit">Kartu Kredit / Debit</option>
                    </select>
                </div>

                <!-- Nominal Dibayar Input -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-semibold text-slate-700">
                            Nominal Dibayar (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1" x-show="paymentStatus === 'partial'">
                            <button type="button" @click="setPercentagePaid(0.25)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">25%</button>
                            <button type="button" @click="setPercentagePaid(0.50)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">50%</button>
                        </div>
                    </div>
                    <input type="number" step="any" min="0" name="paid_amount" x-model.number="paidAmount" @input="onPaidAmountInput()"
                           :readonly="paymentStatus === 'unpaid'"
                           class="w-full rounded-xl px-3 py-2 text-sm font-bold border focus:outline-none transition"
                           :class="paymentStatus === 'paid' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : (paymentStatus === 'unpaid' ? 'bg-slate-100 border-slate-200 text-slate-500' : 'bg-white border-blue-300 text-blue-900 focus:border-blue-500')">
                    <p class="text-[10px] mt-1"
                       :class="paymentStatus === 'paid' ? 'text-emerald-600' : (paymentStatus === 'partial' ? 'text-blue-600' : 'text-amber-600')">
                        <span x-text="paymentStatus === 'paid' ? '✅ Lunas penuh tanpa hutang.' : (paymentStatus === 'partial' ? '⚠️ Uang muka dicatat, sisa ' + formatRupiah(remainingDebt) + ' dicatat sebagai Hutang Usaha ke supplier.' : '⚠️ Total belanja ' + formatRupiah(totalAmount) + ' dicatat penuh sebagai Hutang Usaha ke supplier.')"></span>
                    </p>
                </div>

                <!-- Jatuh Tempo (Hanya tampil jika DP atau Tempo) -->
                <div x-show="paymentStatus !== 'paid'" x-transition class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-amber-900 text-xs">
                            Tanggal Jatuh Tempo Hutang <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="setDueDays(7)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-800 transition">+7 Hari</button>
                            <button type="button" @click="setDueDays(14)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-800 transition">+14 Hari</button>
                            <button type="button" @click="setDueDays(30)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-800 transition">+30 Hari</button>
                        </div>
                    </div>
                    <input type="date" name="due_date" x-model="dueDate" :required="paymentStatus !== 'paid'"
                           class="w-full bg-white border border-amber-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-amber-500">
                    <p class="text-[10px] text-amber-700">Batas akhir pembayaran hutang kepada supplier.</p>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Kalkulasi Akhir & Auto-Receive -->
            <div class="space-y-4">
                <div class="bg-slate-50 rounded-2xl p-4 space-y-2.5 text-xs border border-slate-200">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal Barang:</span>
                        <span class="font-bold text-slate-800" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <div class="border-t border-slate-200 pt-2 flex items-center justify-between text-sm">
                        <span class="font-bold text-slate-900">TOTAL PEMBELIAN:</span>
                        <span class="font-extrabold text-emerald-700 text-base" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <div class="border-t border-dashed border-slate-200 pt-2 flex items-center justify-between">
                        <span class="font-semibold text-slate-600">Nominal Dibayar Awal:</span>
                        <span class="font-bold text-slate-900" x-text="formatRupiah(paidAmount)"></span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="font-semibold" :class="remainingDebt > 0 ? 'text-amber-700' : 'text-emerald-700'">
                            Sisa Hutang ke Supplier:
                        </span>
                        <span class="font-bold text-sm" :class="remainingDebt > 0 ? 'text-rose-600' : 'text-emerald-700'"
                              x-text="formatRupiah(remainingDebt)"></span>
                    </div>
                </div>

                <!-- Auto-receive checkbox -->
                <div class="p-3.5 rounded-xl border border-slate-200 bg-emerald-50/30">
                    <label class="flex items-start gap-2.5 cursor-pointer text-xs font-bold text-slate-800">
                        <input type="checkbox" name="auto_receive" value="1" checked class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-0">
                        <div>
                            <span>Konfirmasi Langsung Barang Sudah Diterima Fisik (Update Stok & FIFO)</span>
                            <p class="text-[11px] font-normal text-slate-500 mt-0.5">Jika dicentang, stok gudang langsung bertambah dan batch persediaan FIFO langsung terbentuk otomatis.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('transactions.purchases.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
            Simpan Transaksi Pembelian
        </button>
    </div>
</form>

<script>
    function purchaseForm() {
        const productCatalog = {!! json_encode($products->map(fn($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'stock_formatted' => $p->stock_formatted,
            'buy_price' => (float)$p->buy_price,
            'base_unit_id' => $p->base_unit_id,
            'units' => app(\App\Services\UnitConversionService::class)->getProductUnitsWithPricing($p),
        ])) !!};

        const defaultDueDate = new Date();
        defaultDueDate.setDate(defaultDueDate.getDate() + 30);
        const defaultDueDateStr = defaultDueDate.toISOString().split('T')[0];

        return {
            productCatalog: productCatalog,
            paymentStatus: 'paid', // 'paid', 'partial', 'unpaid'
            paymentMethod: 'cash',
            paidAmount: 0,
            dueDate: defaultDueDateStr,
            invoicePreview: null,
            invoiceName: '',

            init() {
                this.$watch('totalAmount', (newTotal) => {
                    if (this.paymentStatus === 'paid') {
                        this.paidAmount = newTotal;
                    } else if (this.paymentStatus === 'unpaid') {
                        this.paidAmount = 0;
                    }
                });
            },

            setPaymentMode(mode) {
                this.paymentStatus = mode;
                if (mode === 'paid') {
                    this.paidAmount = this.totalAmount;
                } else if (mode === 'unpaid') {
                    this.paidAmount = 0;
                } else if (mode === 'partial') {
                    if (this.paidAmount <= 0 || this.paidAmount >= this.totalAmount) {
                        this.paidAmount = Math.round(this.totalAmount / 2);
                    }
                }
            },

            onPaidAmountInput() {
                if (this.paidAmount >= this.totalAmount && this.totalAmount > 0) {
                    this.paymentStatus = 'paid';
                } else if (this.paidAmount > 0) {
                    this.paymentStatus = 'partial';
                } else {
                    this.paymentStatus = 'unpaid';
                }
            },

            setPercentagePaid(factor) {
                this.paidAmount = Math.round(this.totalAmount * factor);
                this.onPaidAmountInput();
            },

            setDueDays(days) {
                const d = new Date();
                d.setDate(d.getDate() + days);
                this.dueDate = d.toISOString().split('T')[0];
            },

            previewInvoice(event) {
                const file = event.target.files[0];
                if (file) {
                    this.invoiceName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.invoicePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            clearInvoice() {
                this.invoicePreview = null;
                this.invoiceName = '';
                if (this.$refs.invoiceInput) {
                    this.$refs.invoiceInput.value = '';
                }
            },

            get remainingDebt() {
                return Math.max(0, this.totalAmount - this.paidAmount);
            },

            getProductName(id) {
                const p = this.productCatalog.find(item => item.id == id);
                return p ? `${p.sku} - ${p.name}` : '';
            },
            getProductStock(id) {
                const p = this.productCatalog.find(item => item.id == id);
                return p ? p.stock_formatted : '';
            },
            items: [
                { product_id: '', unit_id: '', quantity: 1, unit_price: 0, subtotal: 0, expiry_date: '', units: [] }
            ],
            addItem() {
                this.items.push({ product_id: '', unit_id: '', quantity: 1, unit_price: 0, subtotal: 0, expiry_date: '', units: [] });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    if (this.paymentStatus === 'paid') {
                        this.paidAmount = this.totalAmount;
                    }
                }
            },
            onProductSelect(item) {
                const prod = productCatalog.find(p => p.id == item.product_id);
                if (prod) {
                    item.units = prod.units;
                    item.unit_id = prod.base_unit_id;
                    item.unit_price = prod.buy_price;
                    this.recalcSubtotal(item);
                }
            },
            onUnitChange(item) {
                const u = item.units.find(x => x.unit_id == item.unit_id);
                if (u && u.buy_price > 0) {
                    item.unit_price = u.buy_price;
                } else if (u) {
                    const prod = productCatalog.find(p => p.id == item.product_id);
                    if (prod) {
                        item.unit_price = Math.round(prod.buy_price * (u.conversion_factor || 1));
                    }
                }
                this.recalcSubtotal(item);
            },
            getSelectedUnitCode(item) {
                const u = item.units.find(x => x.unit_id == item.unit_id);
                return u ? u.unit_code : '';
            },
            getBaseUnitCode(item) {
                const u = item.units.find(x => x.is_base);
                return u ? u.unit_code : 'PCS';
            },
            calcBaseQty(item) {
                const u = item.units.find(x => x.unit_id == item.unit_id);
                const factor = u ? (parseFloat(u.conversion_factor) || 1) : 1;
                return Math.round(((parseFloat(item.quantity) || 0) * factor) * 100) / 100;
            },
            calcPricePerBase(item) {
                const u = item.units.find(x => x.unit_id == item.unit_id);
                const factor = u ? (parseFloat(u.conversion_factor) || 1) : 1;
                const price = parseFloat(item.unit_price) || 0;
                return factor > 0 ? (price / factor) : price;
            },
            recalcSubtotal(item) {
                item.subtotal = Math.round((item.quantity * item.unit_price) * 100) / 100;
                if (this.paymentStatus === 'paid') {
                    this.paidAmount = this.totalAmount;
                }
            },
            get totalAmount() {
                return this.items.reduce((sum, i) => sum + (i.subtotal || 0), 0);
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            }
        };
    }
</script>
@endsection

