@extends('layouts.app', ['title' => 'Detail Opname ' . $opname->opname_number, 'activeAccordion' => 'inventory'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Stok Opname: {{ $opname->opname_number }}</h1>
        <p class="text-xs text-slate-500 mt-1">Periksa selisih fisik vs sistem dan terapkan penyesuaian (adjustment)</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.opname.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            &larr; Kembali
        </a>

        @if($opname->status !== 'adjusted')
            <form method="POST" action="{{ route('inventory.opname.adjust', $opname) }}" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menerapkan hasil penyesuaian stok ini ke sistem dan kartu stok gudang?')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Terapkan Penyesuaian Stok (Adjust)
                </button>
            </form>
        @else
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200">
                &check; Penyesuaian Telah Diterapkan
            </span>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Meta Info -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-slate-500 font-medium">Gudang Diperiksa:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $opname->warehouse?->name }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Tanggal Opname:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $opname->opname_date->format('d/m/Y') }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Petugas Pemeriksa:</span>
            <span class="font-bold text-slate-800 block mt-0.5">{{ $opname->user?->name }}</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium">Status Dokumen:</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block mt-0.5
                {{ $opname->status === 'adjusted' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $opname->status === 'adjusted' ? 'Selesai (Adjusted)' : 'Draft (Menunggu Persetujuan)' }}
            </span>
        </div>
    </div>

    <!-- Items Variance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">SKU</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5 text-center">Stok Sistem</th>
                        <th class="p-3.5 text-center">Fisik Nyata</th>
                        <th class="p-3.5 text-center">Selisih Fisik</th>
                        <th class="p-3.5 text-right">Biaya Satuan</th>
                        <th class="p-3.5 text-right">Nilai Selisih (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($opname->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-700">{{ $item->product?->sku }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ $item->product?->name }}</td>
                            <td class="p-3.5 text-center font-bold text-slate-600">
                                {{ (float)$item->system_quantity }} {{ $item->product?->baseUnit?->code }}
                            </td>
                            <td class="p-3.5 text-center font-black text-slate-900">
                                {{ (float)$item->physical_quantity }} {{ $item->product?->baseUnit?->code }}
                            </td>
                            <td class="p-3.5 text-center font-extrabold">
                                <span class="px-2 py-0.5 rounded-md text-[11px]
                                    {{ $item->difference > 0 ? 'bg-emerald-50 text-emerald-700' : ($item->difference < 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-500') }}">
                                    {{ $item->difference > 0 ? '+' . (float)$item->difference : (float)$item->difference }} {{ $item->product?->baseUnit?->code }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right text-slate-600">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-bold {{ $item->total_difference_cost >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format($item->total_difference_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

