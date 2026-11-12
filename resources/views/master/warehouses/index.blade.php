@extends('layouts.app', ['title' => 'Gudang & Lokasi Rak', 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Gudang & Lokasi Rak</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data gudang penyimpanan fisik dan koordinat rak/baris/kolom</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openLocationModal()" 
                class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Rak / Baris
        </button>
        <button type="button" onclick="openWarehouseModal()" 
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Gudang
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @foreach($warehouses as $wh)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        {{ substr($wh->code, -1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900">{{ $wh->name }}</h2>
                            <span class="px-2 py-0.5 rounded-md bg-slate-200 font-mono text-[10px] font-bold text-slate-700">{{ $wh->code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $wh->address ?? 'Alamat belum diatur' }} &bull; Telp: {{ $wh->phone ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium">{{ $wh->locations->count() }} Lokasi Rak</span>
                    <button type="button" onclick="editWarehouse({{ json_encode($wh) }})" 
                            class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold">
                        Edit Gudang
                    </button>
                </div>
            </div>

            <!-- Locations in this warehouse -->
            <div class="p-4">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Daftar Rak & Posisi Penyimpanan:</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($wh->locations as $loc)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-xs text-emerald-800 block">{{ $loc->code }}</span>
                                <span class="text-[10px] text-slate-500">Area: {{ $loc->area ?? '-' }} &bull; Rak: {{ $loc->rack ?? '-' }}</span>
                            </div>
                            <form method="POST" action="{{ route('master.warehouses.location.destroy', $loc) }}" onsubmit="return confirm('Hapus lokasi rak {{ $loc->code }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="col-span-full py-4 text-center text-xs text-slate-400">
                            Belum ada lokasi rak di gudang ini. Klik tombol "Tambah Rak / Baris" di atas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

</div>

<!-- Modal Warehouse -->
<div id="warehouseModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-4" id="whModalTitle">Tambah Gudang</h3>
        <form id="whForm" method="POST" action="{{ route('master.warehouses.store') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="whMethod" value="POST">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kode Gudang <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="whCode" required placeholder="Contoh: GUDANG-C"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 uppercase font-mono font-bold focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Gudang <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="whName" required placeholder="Contoh: Gudang Toko Sumber Berkah Cabang 2"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Gudang</label>
                <textarea name="address" id="whAddress" rows="2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" id="whPhone"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('warehouseModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Location -->
<div id="locationModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-4">Tambah Lokasi Rak Gudang</h3>
        <form method="POST" action="{{ route('master.warehouses.location.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Pilih Gudang <span class="text-red-500">*</span></label>
                <select name="warehouse_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kode Rak / Posisi <span class="text-red-500">*</span></label>
                <input type="text" name="code" required placeholder="Contoh: GUD-A/RAK-03/B-01"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 uppercase font-mono font-bold focus:outline-none focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Area / Zona</label>
                    <input type="text" name="area" placeholder="Contoh: Cat / Semen"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Rak</label>
                    <input type="text" name="rack" placeholder="Contoh: RAK-01"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('locationModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Lokasi Rak</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openWarehouseModal() {
        document.getElementById('whModalTitle').innerText = 'Tambah Gudang Baru';
        document.getElementById('whForm').action = "{{ route('master.warehouses.store') }}";
        document.getElementById('whMethod').value = 'POST';
        document.getElementById('whCode').value = '';
        document.getElementById('whName').value = '';
        document.getElementById('whAddress').value = '';
        document.getElementById('whPhone').value = '';
        document.getElementById('warehouseModal').classList.remove('hidden');
    }

    function editWarehouse(wh) {
        document.getElementById('whModalTitle').innerText = 'Edit Gudang: ' + wh.name;
        document.getElementById('whForm').action = `/master/warehouses/${wh.id}`;
        document.getElementById('whMethod').value = 'PUT';
        document.getElementById('whCode').value = wh.code;
        document.getElementById('whName').value = wh.name;
        document.getElementById('whAddress').value = wh.address || '';
        document.getElementById('whPhone').value = wh.phone || '';
        document.getElementById('warehouseModal').classList.remove('hidden');
    }

    function openLocationModal() {
        document.getElementById('locationModal').classList.remove('hidden');
    }
</script>
@endsection

