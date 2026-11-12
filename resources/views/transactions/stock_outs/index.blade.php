@extends('layouts.app', ['title' => 'Barang Keluar (Non-Penjualan)', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Barang Keluar (Non-Penjualan)</h1>
        <p class="text-xs text-slate-500 mt-1">Pengeluaran stok untuk barang rusak, hilang, pemakaian internal, sampel, dan expired</p>
    </div>
    <a href="{{ route('transactions.stock-outs.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Catat Barang Keluar
    </a>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('transactions.stock-outs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor referensi..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="reason" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Alasan Pengeluaran</option>
                    <option value="damaged" {{ $reason === 'damaged' ? 'selected' : '' }}>Barang Rusak</option>
                    <option value="lost" {{ $reason === 'lost' ? 'selected' : '' }}>Barang Hilang</option>
                    <option value="internal_use" {{ $reason === 'internal_use' ? 'selected' : '' }}>Pemakaian Internal Toko</option>
                    <option value="sample" {{ $reason === 'sample' ? 'selected' : '' }}>Sampel Toko</option>
                    <option value="expired" {{ $reason === 'expired' ? 'selected' : '' }}>Barang Kedaluwarsa</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('transactions.stock-outs.index') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">No Referensi</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Alasan Pengeluaran</th>
                        <th class="p-3.5">Gudang Asal</th>
                        <th class="p-3.5 text-center">Jumlah Item</th>
                        <th class="p-3.5 text-right">Total Nilai HPP (Rp)</th>
                        <th class="p-3.5">Petugas</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stockOuts as $out)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-800">{{ $out->reference_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $out->date->format('d/m/Y') }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $out->reason === 'damaged' || $out->reason === 'expired' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ str_replace('_', ' ', $out->reason) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-600">{{ $out->warehouse?->name }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-800">{{ $out->items_count }} Item</td>
                            <td class="p-3.5 text-right font-bold text-slate-900">Rp {{ number_format($out->total_cost, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-slate-600">{{ $out->user?->name }}</td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('transactions.stock-outs.show', $out) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition inline-block" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">Belum ada transaksi barang keluar non-penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $stockOuts->links() }}</div>
    </div>

</div>
@endsection

