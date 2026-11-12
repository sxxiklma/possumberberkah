@extends('layouts.app', ['title' => 'Detail Supplier: ' . $supplier->name, 'activeAccordion' => 'mitra'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $supplier->name }}</h1>
        <p class="text-xs text-slate-500 mt-1">Profil supplier, histori transaksi pembelian, dan rekap hutang dagang</p>
    </div>
    <a href="{{ route('mitra.suppliers.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Total Transaksi Pembelian (PO):</span>
            <span class="text-xl font-black text-slate-900 block mt-1">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</span>
            <p class="text-[11px] text-slate-400 mt-1">{{ $supplier->purchases->count() }} Transaksi PO tercatat</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Sisa Hutang Berjalan:</span>
            <span class="text-xl font-black text-rose-600 block mt-1">Rp {{ number_format($totalPayables, 0, ',', '.') }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Kewajiban bayar yang belum lunas</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Kontak Person:</span>
            <span class="text-sm font-bold text-slate-800 block mt-1">{{ $supplier->contact_name ?? '-' }}</span>
            <p class="text-xs text-slate-500 mt-0.5">Telp: {{ $supplier->phone ?? '-' }}</p>
            <p class="text-xs text-slate-500">{{ $supplier->email ?? '-' }}</p>
        </div>
    </div>

    <!-- Purchase History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Pembelian Barang Masuk (PO)</h2>
            <a href="{{ route('transactions.purchases.create') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Buat PO Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">No PO</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Gudang</th>
                        <th class="p-3.5">Total Tagihan</th>
                        <th class="p-3.5">Terbayar</th>
                        <th class="p-3.5">Status Barang</th>
                        <th class="p-3.5">Status Bayar</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplier->purchases as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-800">{{ $p->purchase_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $p->purchase_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 text-slate-700">{{ $p->warehouse?->name }}</td>
                            <td class="p-3.5 font-bold text-slate-800">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-emerald-600 font-semibold">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->status === 'received' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $p->payment_status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('transactions.purchases.show', $p) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Detail PO">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">Belum ada riwayat pembelian dari supplier ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

