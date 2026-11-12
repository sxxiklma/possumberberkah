@extends('layouts.app', ['title' => 'Detail PO ' . $purchase->purchase_number, 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Pembelian: {{ $purchase->purchase_number }}</h1>
        <p class="text-xs text-slate-500 mt-1">Rincian faktur pembelian, status barang gudang, dan hutang supplier</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('transactions.purchases.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            &larr; Kembali
        </a>
        <a href="{{ route('transactions.purchases.print', $purchase) }}" target="_blank" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak PO
        </a>
        @if($purchase->status !== 'received')
            <form method="POST" action="{{ route('transactions.purchases.receive', $purchase) }}" 
                  onsubmit="return confirm('Konfirmasi penerimaan barang ke dalam gudang sekarang?')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Konfirmasi Penerimaan Barang
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Meta Details -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-slate-500 font-medium">Supplier:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $purchase->supplier?->name }}</span>
            <span class="text-[10px] text-slate-400">Telp: {{ $purchase->supplier?->phone ?? '-' }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Gudang Penyimpanan:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $purchase->warehouse?->name }}</span>
            <span class="text-[10px] text-slate-400">Tgl: {{ $purchase->purchase_date->format('d/m/Y') }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Status Barang:</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block mt-0.5
                {{ $purchase->status === 'received' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $purchase->status === 'received' ? 'Sudah Diterima Fisik' : 'Draft Pesanan' }}
            </span>
            <span class="text-[10px] text-slate-400 block mt-0.5">No. Inv: {{ $purchase->invoice_number ?: '-' }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Status Pembayaran:</span>
            @php
                $statusColor = match($purchase->payment_status) {
                    'paid' => 'bg-emerald-100 text-emerald-800',
                    'partial' => 'bg-blue-100 text-blue-800',
                    default => 'bg-amber-100 text-amber-800',
                };
                $statusText = match($purchase->payment_status) {
                    'paid' => 'LUNAS',
                    'partial' => 'DP (UANG MUKA)',
                    default => 'TEMPO (HUTANG)',
                };
                $methodText = match($purchase->payment_method) {
                    'bank_transfer' => 'Transfer Bank',
                    'giro' => 'Giro / Cek',
                    'qris' => 'QRIS',
                    'credit' => 'Kartu Kredit/Debit',
                    default => 'Tunai / Kas',
                };
            @endphp
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block mt-0.5 {{ $statusColor }}">
                {{ $statusText }}
            </span>
            <span class="text-[10px] text-slate-500 block mt-0.5">
                Metode: <strong>{{ $methodText }}</strong>
                @if($purchase->due_date && $purchase->payment_status !== 'paid')
                    • Jatuh Tempo: <span class="font-bold text-amber-700">{{ $purchase->due_date->format('d/m/Y') }}</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800">Daftar Barang Masuk & Batch FIFO</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">SKU</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5 text-center">Jumlah Beli</th>
                        <th class="p-3.5 text-center">Konversi Dasar</th>
                        <th class="p-3.5 text-right">Harga Beli Satuan</th>
                        <th class="p-3.5 text-right">Subtotal</th>
                        <th class="p-3.5">Nomor Batch</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-700">{{ $item->product?->sku }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $item->product?->name }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-800">
                                {{ (float)$item->quantity }} {{ $item->unit?->code }}
                            </td>
                            <td class="p-3.5 text-center text-slate-600">
                                = {{ (float)$item->base_quantity }} {{ $item->product?->baseUnit?->code }}
                            </td>
                            <td class="p-3.5 text-right font-medium text-slate-700">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-bold text-emerald-700">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-600">
                                {{ $item->batch_number ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs">
            <div class="text-slate-500">
                Dicatat oleh: <strong>{{ $purchase->user?->name }}</strong>
            </div>
            <div class="w-64 space-y-1">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Total Pembelian:</span>
                    <span class="font-extrabold text-slate-900">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Terbayar:</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-rose-600 font-bold pt-1 border-t border-slate-200">
                    <span>Sisa Hutang:</span>
                    <span>Rp {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Informasi Pembayaran, Hutang & Bukti Foto Invoice -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Bukti Foto Invoice / Nota Supplier -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800">Bukti Foto Invoice / Nota Supplier</h3>
                </div>
                @if($purchase->invoice_image)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Terlampir</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">Belum Ada</span>
                @endif
            </div>

            @if($purchase->invoice_image)
                <div class="space-y-3">
                    <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-50 max-h-64 flex items-center justify-center p-2">
                        <img src="{{ asset('storage/' . $purchase->invoice_image) }}" alt="Bukti Invoice PO {{ $purchase->purchase_number }}" class="max-h-60 w-auto object-contain rounded-lg transition group-hover:scale-105 duration-300">
                        <a href="{{ asset('storage/' . $purchase->invoice_image) }}" target="_blank" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Buka Foto Ukuran Penuh
                        </a>
                    </div>
                    
                    <form action="{{ route('transactions.purchases.invoice-image', $purchase) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-100 flex items-center gap-2">
                        @csrf
                        <input type="file" name="invoice_image" accept="image/*" required class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer flex-1">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition shrink-0 cursor-pointer">
                            Ganti Foto
                        </button>
                    </form>
                </div>
            @else
                <div class="p-6 rounded-xl border border-dashed border-slate-200 text-center space-y-3 bg-slate-50/50">
                    <p class="text-xs text-slate-500">Belum ada foto invoice atau bukti pengiriman fisik yang diunggah untuk pembelian ini.</p>
                    <form action="{{ route('transactions.purchases.invoice-image', $purchase) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center justify-center gap-2 max-w-sm mx-auto">
                        @csrf
                        <input type="file" name="invoice_image" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition shrink-0 cursor-pointer">
                            Unggah Foto
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- 2. Ringkasan Status Hutang & Riwayat Pembayaran -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-sm font-bold text-slate-800">Status Pelunasan & Hutang Usaha</h3>
                </div>
                @if($purchase->payment_status === 'paid')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                @elseif($purchase->payment_status === 'partial')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">DP (UANG MUKA)</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">TEMPO</span>
                @endif
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Total Tagihan Pembelian:</span>
                    <span class="font-extrabold text-slate-900">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Sudah Dibayar:</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-rose-600 font-bold pt-2 border-t border-slate-100">
                    <span>Sisa Hutang:</span>
                    <span class="text-sm">Rp {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 0, ',', '.') }}</span>
                </div>

                @if($purchase->due_date)
                    <div class="flex items-center justify-between pt-1 text-slate-600">
                        <span>Jatuh Tempo:</span>
                        <span class="font-bold {{ $purchase->payment_status !== 'paid' && $purchase->due_date->isPast() ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $purchase->due_date->format('d/m/Y') }}
                            @if($purchase->payment_status !== 'paid')
                                ({{ $purchase->due_date->diffForHumans() }})
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            @if($purchase->payable && $purchase->payable->remaining_balance > 0)
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500">Tercatat di Buku Hutang Usaha</span>
                    <a href="{{ route('finance.payables') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center gap-1">
                        <span>Bayar / Cicil di Menu Hutang &rarr;</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

