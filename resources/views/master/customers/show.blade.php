@extends('layouts.app', ['title' => 'Detail Customer: ' . $customer->name, 'activeAccordion' => 'mitra'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h1>
        <p class="text-xs text-slate-500 mt-1">Profil pelanggan, riwayat belanja kasir, dan status saldo piutang</p>
    </div>
    <a href="{{ route('mitra.customers.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Total Belanja (Sales):</span>
            <span class="text-xl font-black text-slate-900 block mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
            <p class="text-[11px] text-slate-400 mt-1">{{ $customer->sales->count() }} Transaksi selesai</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Sisa Saldo Piutang:</span>
            <span class="text-xl font-black text-amber-600 block mt-1">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Tagihan tempo yang belum dibayar</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Kategori & Kontak:</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block mt-1
                {{ $customer->category === 'grosir' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                {{ str_replace('_', ' ', $customer->category) }}
            </span>
            <p class="text-xs text-slate-600 mt-1 font-semibold">Telp: {{ $customer->phone ?? '-' }}</p>
            <p class="text-xs text-slate-400">{{ $customer->address ?? $customer->city ?? '-' }}</p>
        </div>
    </div>

    <!-- Sales History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Transaksi Penjualan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Faktur</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5">Total Bayar</th>
                        <th class="p-3.5">Terbayar</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->sales as $sale)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-800">{{ $sale->invoice_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                            <td class="p-3.5 uppercase font-semibold text-slate-500">{{ $sale->payment_method }}</td>
                            <td class="p-3.5 font-bold text-slate-800">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sale->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $sale->payment_status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="text-emerald-600 hover:underline font-bold">
                                    Cetak Struk &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">Belum ada transaksi penjualan untuk customer ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

