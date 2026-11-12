@extends('layouts.app', ['title' => 'Stok Opname', 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Stok Opname (Penyesuaian Fisik Gudang)</h1>
        <p class="text-xs text-slate-500 mt-1">Pemeriksaan fisik barang berkala dan penyesuaian selisih ke kartu stok</p>
    </div>
    <a href="{{ route('inventory.opname.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Mulai Opname Baru
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">No Opname</th>
                    <th class="p-3.5">Tanggal</th>
                    <th class="p-3.5">Gudang</th>
                    <th class="p-3.5 text-center">Jumlah Item Diperiksa</th>
                    <th class="p-3.5">Petugas</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($opnames as $op)
                    <tr class="hover:bg-slate-50/50">
                        <td class="p-3.5 font-mono font-bold text-slate-800">{{ $op->opname_number }}</td>
                        <td class="p-3.5 text-slate-500">{{ $op->opname_date->format('d/m/Y') }}</td>
                        <td class="p-3.5 font-medium text-slate-700">{{ $op->warehouse?->name }}</td>
                        <td class="p-3.5 text-center font-bold text-slate-800">{{ $op->items_count }} Item</td>
                        <td class="p-3.5 text-slate-600">{{ $op->user?->name }}</td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $op->status === 'adjusted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $op->status === 'adjusted' ? 'Disetujui / Selesai' : 'Draft Pemeriksaan' }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <a href="{{ route('inventory.opname.show', $op) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs inline-block transition">
                                Buka Detail &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-400">Belum ada riwayat stock opname tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100">{{ $opnames->links() }}</div>
</div>
@endsection

