@extends('layouts.app', ['title' => 'Detail Transaksi ' . $sale->invoice_number, 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-slate-900">{{ $sale->invoice_number }}</h1>
            @if($sale->status === 'void')
                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">BATAL (VOID)</span>
            @elseif($sale->payment_status === 'paid')
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">LUNAS</span>
            @elseif($sale->payment_status === 'dp')
                <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">DP (UANG MUKA)</span>
            @else
                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">TEMPO (PIUTANG)</span>
            @endif
        </div>
        <p class="text-xs text-slate-500 mt-1">Transaksi penjualan tanggal {{ $sale->sale_date->format('d F Y, H:i') }} WIB oleh {{ $sale->user?->name ?? 'Kasir' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('transactions.sales.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition whitespace-nowrap">
            &larr; Kembali
        </a>
        @if($sale->status !== 'void')
            <button type="button" onclick="triggerPaymentModal()" class="px-3 py-2 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Ubah Status
            </button>
        @endif
        <a href="{{ route('transactions.sales.print', $sale) }}" target="_blank" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Cetak Faktur
        </a>
        <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Struk
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl" x-data="salePaymentManager()">

    <!-- Card Info Pelanggan & Transaksi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Informasi Pelanggan</span>
            <div class="text-sm font-bold text-slate-800">{{ $sale->customer?->name ?? 'Customer Umum (Walk-In)' }}</div>
            <div class="text-xs text-slate-500 mt-1">
                @if($sale->customer)
                    <p>Telp: {{ $sale->customer->phone ?: '-' }}</p>
                    <p class="truncate">Alamat: {{ $sale->customer->address ?: '-' }}</p>
                    <span class="inline-block px-2 py-0.5 mt-2 rounded bg-slate-100 text-[10px] font-semibold text-slate-600">
                        Kategori: {{ ucfirst($sale->customer->category) }}
                    </span>
                @else
                    <p>Pembelian langsung tanpa data member</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status & Pembayaran</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-2 py-0.5 rounded bg-slate-100 text-xs font-bold uppercase text-slate-700">
                    {{ $sale->payment_method }}
                </span>
                <span class="text-xs font-semibold text-slate-500">
                    ({{ $sale->price_type === 'wholesale' ? 'Harga Grosir' : 'Harga Retail' }})
                </span>
            </div>
            <div class="text-xs text-slate-500 mt-2 space-y-0.5">
                <p>Total Tagihan: <span class="font-bold text-slate-800">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span></p>
                <p>Sudah Dibayar: <span class="font-bold text-emerald-700">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span></p>
                @if($sale->payment_status !== 'paid' && $sale->status !== 'void')
                    <p class="text-amber-700 font-bold">
                        Sisa Piutang: Rp {{ number_format(max(0, $sale->total_amount - $sale->paid_amount), 0, ',', '.') }}
                    </p>
                    @if($sale->due_date)
                        <p class="text-[11px] text-slate-400">Jatuh Tempo: {{ $sale->due_date->format('d/m/Y') }}</p>
                    @endif
                    <button type="button" @click="openModal({
                        id: '{{ $sale->id }}',
                        invoice: '{{ $sale->invoice_number }}',
                        customer: '{{ addslashes($sale->customer?->name ?? 'Customer Umum (Walk-In)') }}',
                        total: {{ (float)$sale->total_amount }},
                        paid: {{ (float)$sale->paid_amount }},
                        remaining: {{ max(0, (float)$sale->total_amount - (float)$sale->paid_amount) }},
                        currentStatus: '{{ $sale->payment_status }}',
                        method: '{{ $sale->payment_method }}'
                    })" class="mt-3 w-full py-1.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Ubah Status / Catat Pelunasan
                    </button>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan & Petugas</span>
            <div class="text-xs font-semibold text-slate-700">Petugas: {{ $sale->user?->name ?? 'Kasir Toko' }}</div>
            <div class="text-xs text-slate-500 mt-2">
                <span class="font-semibold text-slate-600">Catatan:</span>
                <p class="italic text-slate-600 mt-0.5">{{ $sale->notes ?: 'Tidak ada catatan khusus.' }}</p>
            </div>
            @if($sale->status === 'void')
                <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg text-[11px] text-red-700">
                    <span class="font-bold">Dibatalkan oleh:</span> {{ $sale->voidByUser?->name ?? 'Admin' }}<br>
                    <span class="font-bold">Alasan:</span> {{ $sale->void_reason }}
                </div>
            @endif
        </div>
    </div>

    <!-- Tabel Rincian Barang yang Dijual -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Produk yang Dijual</h2>
            <span class="text-xs text-slate-400 font-medium">{{ $sale->items->count() }} Item Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">Produk</th>
                        <th class="p-3.5 text-center">Satuan</th>
                        <th class="p-3.5 text-center">Jumlah</th>
                        <th class="p-3.5 text-right">Harga Satuan</th>
                        <th class="p-3.5 text-right">Diskon</th>
                        <th class="p-3.5 text-right">Subtotal</th>
                        <th class="p-3.5 text-right text-slate-400">HPP Modal (FIFO)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $idx => $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 text-slate-400 font-semibold">{{ $idx + 1 }}</td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800">{{ $item->product?->name }}</div>
                                <span class="text-[10px] font-mono text-slate-400">{{ $item->product?->sku }}</span>
                            </td>
                            <td class="p-3.5 text-center font-bold text-emerald-800">
                                {{ $item->unit?->code }}
                            </td>
                            <td class="p-3.5 text-center font-bold text-slate-800">
                                {{ (float)$item->quantity }}
                                @if($item->conversion_factor > 1)
                                    <span class="block text-[10px] text-slate-400 font-normal">({{ (float)$item->base_quantity }} {{ $item->product?->baseUnit?->code }})</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right text-slate-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right text-red-500">
                                {{ $item->discount_amount > 0 ? '- Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="p-3.5 text-right font-bold text-emerald-700">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right text-slate-400 font-mono">
                                Rp {{ number_format($item->cogs_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer Total -->
        <div class="p-5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs">
            <div class="text-slate-500 space-y-1">
                <p>Total Modal HPP FIFO: <span class="font-bold text-slate-700 font-mono">Rp {{ number_format($sale->total_cogs, 0, ',', '.') }}</span></p>
                <p>Estimasi Laba Kotor: <span class="font-bold text-emerald-600 font-mono">Rp {{ number_format(max(0, $sale->total_amount - $sale->total_cogs), 0, ',', '.') }}</span></p>
            </div>

            <div class="w-full sm:w-72 space-y-1.5 text-right">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Barang:</span>
                    <span class="font-semibold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Diskon Faktur:</span>
                        <span class="font-semibold">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($sale->tax_amount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Pajak PPN:</span>
                        <span class="font-semibold">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-slate-900 border-t border-slate-200 pt-1.5">
                    <span>Total Penjualan:</span>
                    <span class="text-emerald-700 text-base">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Nominal Dibayar:</span>
                    <span class="font-bold">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                @if($sale->change_amount > 0)
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Ubah Status Pembayaran / Pelunasan -->
    <div x-show="isOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden"
             @click.away="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Ubah Status Pembayaran
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Faktur: <span class="font-mono font-bold text-slate-700" x-text="sale.invoice"></span> &bull; <span x-text="sale.customer"></span>
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form :action="'{{ url('transactions/sales') }}/' + sale.id + '/payment'" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- Ringkasan Nilai Tagihan -->
                <div class="grid grid-cols-3 gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Faktur</span>
                        <span class="text-xs font-bold text-slate-800" x-text="formatRupiah(sale.total)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Sudah Dibayar</span>
                        <span class="text-xs font-bold text-emerald-600" x-text="formatRupiah(sale.paid)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Sisa Tagihan</span>
                        <span class="text-xs font-bold text-amber-600" x-text="formatRupiah(sale.remaining)"></span>
                    </div>
                </div>

                <!-- Pilihan Status Pembayaran Baru -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Status Pembayaran Baru <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- LUNAS -->
                        <button type="button" 
                                @click="onStatusChange('paid')"
                                :class="form.payment_status === 'paid' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-2 ring-emerald-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold">LUNAS</span>
                        </button>

                        <!-- DP -->
                        <button type="button" 
                                @click="onStatusChange('dp')"
                                :class="form.payment_status === 'dp' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold">DP</span>
                        </button>

                        <!-- TEMPO -->
                        <button type="button" 
                                @click="onStatusChange('pending')"
                                :class="form.payment_status === 'pending' ? 'bg-amber-50 border-amber-500 text-amber-800 ring-2 ring-amber-500/20 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="py-2 px-3 rounded-xl border text-center transition flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold">TEMPO</span>
                        </button>
                    </div>
                    <input type="hidden" name="payment_status" :value="form.payment_status">
                </div>

                <!-- Input Nominal Yang Dibayarkan Sekarang -->
                <div x-show="form.payment_status !== 'pending'" class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">
                            Nominal Pembayaran Diterima (Rp) <span class="text-red-500">*</span>
                        </label>
                        <button type="button" 
                                x-show="sale.remaining > 0"
                                @click="setFullAmount()" 
                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                            + Lunasi Penuh (<span x-text="formatRupiah(sale.remaining)"></span>)
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" 
                               name="amount" 
                               x-model.number="form.amount"
                               :max="sale.remaining"
                               min="0"
                               step="100"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Pratinjau Sisa Piutang Setelah Simpan:</span>
                        <span class="font-bold" :class="remainingPreview <= 0 ? 'text-emerald-600' : 'text-amber-600'" 
                              x-text="remainingPreview <= 0 ? 'Rp 0 (LUNAS)' : formatRupiah(remainingPreview)"></span>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" 
                            x-model="form.payment_method" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="tempo">Tempo (Piutang)</option>
                    </select>
                </div>

                <!-- Catatan / Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pelunasan / Keterangan (Opsional)</label>
                    <input type="text" 
                           name="notes" 
                           x-model="form.notes"
                           placeholder="Contoh: Pelunasan sisa oleh pembeli di kasir..." 
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeModal()" 
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function triggerPaymentModal() {
        window.dispatchEvent(new CustomEvent('open-payment-modal', {
            detail: {
                id: '{{ $sale->id }}',
                invoice: '{{ $sale->invoice_number }}',
                customer: '{{ addslashes($sale->customer?->name ?? 'Customer Umum (Walk-In)') }}',
                total: {{ (float)$sale->total_amount }},
                paid: {{ (float)$sale->paid_amount }},
                remaining: {{ max(0, (float)$sale->total_amount - (float)$sale->paid_amount) }},
                currentStatus: '{{ $sale->payment_status }}',
                method: '{{ $sale->payment_method }}'
            }
        }));
    }

    function salePaymentManager() {
        return {
            isOpen: false,
            sale: {
                id: null,
                invoice: '',
                customer: '',
                total: 0,
                paid: 0,
                remaining: 0,
                currentStatus: '',
                method: 'cash'
            },
            form: {
                payment_status: 'paid',
                amount: 0,
                payment_method: 'cash',
                notes: ''
            },
            init() {
                window.addEventListener('open-payment-modal', (e) => {
                    this.openModal(e.detail);
                });
            },
            openModal(data) {
                this.sale = { ...data };
                this.form.payment_method = data.method === 'tempo' ? 'cash' : (data.method || 'cash');
                this.form.notes = '';

                if (data.currentStatus === 'paid') {
                    this.form.payment_status = 'paid';
                    this.form.amount = 0;
                } else {
                    this.form.payment_status = 'paid';
                    this.form.amount = data.remaining;
                }
                this.isOpen = true;
            },
            closeModal() {
                this.isOpen = false;
            },
            onStatusChange(newStatus) {
                this.form.payment_status = newStatus;
                if (newStatus === 'paid') {
                    this.form.amount = this.sale.remaining;
                    if (this.form.payment_method === 'tempo') {
                        this.form.payment_method = 'cash';
                    }
                } else if (newStatus === 'dp') {
                    if (this.form.amount <= 0 || this.form.amount >= this.sale.remaining) {
                        this.form.amount = Math.round(this.sale.remaining / 2);
                    }
                    if (this.form.payment_method === 'tempo') {
                        this.form.payment_method = 'cash';
                    }
                } else if (newStatus === 'pending') {
                    this.form.amount = 0;
                    this.form.payment_method = 'tempo';
                }
            },
            setFullAmount() {
                this.form.payment_status = 'paid';
                this.form.amount = this.sale.remaining;
                if (this.form.payment_method === 'tempo') {
                    this.form.payment_method = 'cash';
                }
            },
            get remainingPreview() {
                if (this.form.payment_status === 'paid') return 0;
                if (this.form.payment_status === 'pending') return this.sale.remaining;
                const pay = parseFloat(this.form.amount) || 0;
                return Math.max(0, this.sale.remaining - pay);
            },
            formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
            }
        }
    }
</script>
@endsection

