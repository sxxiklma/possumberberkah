@extends('layouts.app', ['title' => 'Riwayat Pembayaran', 'activeAccordion' => 'finance'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Riwayat Pembayaran & Pelunasan</h1>
        <p class="text-xs text-slate-500 mt-1">Audit seluruh pembayaran masuk dan keluar (penjualan kasir, pembelian PO, cicilan piutang/hutang)</p>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('finance.payments') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor bukti pembayaran..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Jenis Pembayaran</option>
                    <option value="sale_payment" {{ $type === 'sale_payment' ? 'selected' : '' }}>Penjualan Kasir</option>
                    <option value="receivable_installment" {{ $type === 'receivable_installment' ? 'selected' : '' }}>Pelunasan Piutang Customer</option>
                    <option value="purchase_payment" {{ $type === 'purchase_payment' ? 'selected' : '' }}>Pembelian Supplier (PO)</option>
                    <option value="payable_installment" {{ $type === 'payable_installment' ? 'selected' : '' }}>Pembayaran Hutang Supplier</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('finance.payments') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">No Bukti Bayar</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Jenis Transaksi</th>
                        <th class="p-3.5">Mitra Terkait</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5 text-right">Nominal (Rp)</th>
                        <th class="p-3.5">Petugas</th>
                        <th class="p-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-800">{{ $p->payment_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $p->payment_date->format('d/m/Y') }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $p->payment_type === 'sale_payment' || $p->payment_type === 'receivable_installment' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ str_replace('_', ' ', $p->payment_type) }}
                                </span>
                            </td>
                            <td class="p-3.5 font-semibold text-slate-700">
                                {{ $p->customer?->name ?? $p->supplier?->name ?? 'Customer Umum' }}
                            </td>
                            <td class="p-3.5 uppercase font-medium text-slate-500">{{ $p->payment_method }}</td>
                            <td class="p-3.5 text-right font-bold {{ $p->payment_type === 'sale_payment' || $p->payment_type === 'receivable_installment' ? 'text-emerald-600' : 'text-slate-900' }}">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-slate-600">{{ $p->user?->name }}</td>
                            <td class="p-3.5 text-slate-500">{{ $p->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">Belum ada riwayat pembayaran tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $payments->links() }}</div>
    </div>

</div>
@endsection

