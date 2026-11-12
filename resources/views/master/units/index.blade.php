@extends('layouts.app', ['title' => 'Master Satuan Produk', 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Master Satuan Produk</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola daftar satuan barang (PCS, PACK, DUS, BOX, KG, LITER, dll)</p>
    </div>
    <button type="button" onclick="openCreateUnitModal()" 
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Satuan
    </button>
</div>
@endsection

@section('content')
<div class="space-y-4 max-w-4xl">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.units.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama satuan..."
                   class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">Kode Satuan</th>
                    <th class="p-3.5">Nama Satuan</th>
                    <th class="p-3.5">Keterangan</th>
                    <th class="p-3.5 text-center">Digunakan di Produk</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($units as $unit)
                    <tr class="hover:bg-slate-50/60">
                        <td class="p-3.5 font-mono font-extrabold text-emerald-700">{{ $unit->code }}</td>
                        <td class="p-3.5 font-bold text-slate-800">{{ $unit->name }}</td>
                        <td class="p-3.5 text-slate-500">{{ $unit->description ?? '-' }}</td>
                        <td class="p-3.5 text-center font-bold text-slate-700">{{ $unit->products_count }} item</td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openEditUnitModal({{ json_encode($unit) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('master.units.destroy', $unit) }}" onsubmit="return confirm('Hapus satuan {{ $unit->code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-400">Belum ada satuan terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $units->links() }}</div>
    </div>

</div>

<!-- Modal Unit -->
<div id="unitModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-4" id="unitModalTitle">Tambah Satuan</h3>
        <form id="unitForm" method="POST" action="{{ route('master.units.store') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="unitMethod" value="POST">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kode Satuan (Singkatan) <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="unitCode" required placeholder="Contoh: PCS / PACK / DUS"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 uppercase font-mono font-bold focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Panjang Satuan <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="unitName" required placeholder="Contoh: Pieces / Butir / Buah"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Keterangan</label>
                <textarea name="description" id="unitDesc" rows="2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('unitModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUnitModal() {
        document.getElementById('unitModalTitle').innerText = 'Tambah Satuan Baru';
        document.getElementById('unitForm').action = "{{ route('master.units.store') }}";
        document.getElementById('unitMethod').value = 'POST';
        document.getElementById('unitCode').value = '';
        document.getElementById('unitName').value = '';
        document.getElementById('unitDesc').value = '';
        document.getElementById('unitModal').classList.remove('hidden');
    }

    function openEditUnitModal(unit) {
        document.getElementById('unitModalTitle').innerText = 'Edit Satuan: ' + unit.code;
        document.getElementById('unitForm').action = `/master/units/${unit.id}`;
        document.getElementById('unitMethod').value = 'PUT';
        document.getElementById('unitCode').value = unit.code;
        document.getElementById('unitName').value = unit.name;
        document.getElementById('unitDesc').value = unit.description || '';
        document.getElementById('unitModal').classList.remove('hidden');
    }
</script>
@endsection

