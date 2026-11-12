@extends('layouts.app', ['title' => 'Barang Masuk (Pembelian)', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Barang Masuk (Pembelian Supplier)</h1>
        <p class="text-xs text-slate-500 mt-1">Pencatatan order pembelian, penerimaan barang ke gudang, dan pembentukan batch FIFO</p>
    </div>
    <a href="{{ route('transactions.purchases.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Pembelian Baru (PO)
    </a>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <!-- Filter Toolbar -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('transactions.purchases.index') }}" class="flex flex-col md:flex-row md:items-center gap-2.5 sm:gap-3 text-xs">
            <!-- Search bar with icon -->
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor PO atau no invoice supplier..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white text-xs transition">
            </div>

            <!-- Supplier Dropdown -->
            <div class="w-full md:w-44 lg:w-48">
                <select name="supplier_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition cursor-pointer">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ $supplierId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Fisik Dropdown -->
            <div class="w-full md:w-40 lg:w-44">
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition cursor-pointer">
                    <option value="">Semua Status Barang</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft (Belum Diterima)</option>
                    <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Diterima (Stok Masuk)</option>
                </select>
            </div>

            <!-- Status Bayar Dropdown -->
            <div class="w-full md:w-36 lg:w-40">
                <select name="payment_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition cursor-pointer">
                    <option value="">Semua Status Bayar</option>
                    <option value="paid" {{ ($paymentStatus ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="partial" {{ ($paymentStatus ?? '') === 'partial' ? 'selected' : '' }}>DP (Uang Muka)</option>
                    <option value="unpaid" {{ ($paymentStatus ?? '') === 'unpaid' ? 'selected' : '' }}>Tempo (Hutang)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                @if($search || $supplierId || $status || $paymentStatus)
                    <a href="{{ route('transactions.purchases.index') }}" class="px-3 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs transition flex items-center gap-1" title="Reset Semua Filter">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Reset</span>
                    </a>
                @else
                    <a href="{{ route('transactions.purchases.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Nomor PO</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Supplier</th>
                        <th class="p-3.5">Gudang Tujuan</th>
                        <th class="p-3.5">Total Tagihan</th>
                        <th class="p-3.5">Terbayar</th>
                        <th class="p-3.5">Status Fisik</th>
                        <th class="p-3.5">Status Bayar</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-slate-800">{{ $p->purchase_number }}</span>
                                    @if($p->invoice_image)
                                        <span class="inline-flex items-center text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded text-[10px] font-bold" title="Foto Bukti Invoice Tersedia">
                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Foto
                                        </span>
                                    @endif
                                </div>
                                @if($p->invoice_number)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">Inv: {{ $p->invoice_number }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-500">{{ $p->purchase_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $p->supplier?->name }}</td>
                            <td class="p-3.5 text-slate-600">{{ $p->warehouse?->name }}</td>
                            <td class="p-3.5 font-bold text-slate-900">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-emerald-600 font-semibold">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5">
                                @if($p->status === 'received')
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">DITERIMA</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold">DRAFT</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($p->payment_status === 'paid')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">LUNAS</span>
                                @elseif($p->payment_status === 'partial')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800">DP (SEBAGIAN)</span>
                                    @if($p->due_date)
                                        <span class="block text-[10px] text-slate-500 mt-0.5">Tempo: {{ $p->due_date->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">TEMPO</span>
                                    @if($p->due_date)
                                        <span class="block text-[10px] text-amber-700 font-semibold mt-0.5">Tempo: {{ $p->due_date->format('d/m/Y') }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('transactions.purchases.show', $p) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('transactions.purchases.print', $p) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Cetak Bukti PO">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Belum ada transaksi barang masuk tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $purchases->links() }}</div>
    </div>

</div>
@endsection

