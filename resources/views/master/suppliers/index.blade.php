@extends('layouts.app', ['title' => 'Supplier Toko', 'activeAccordion' => 'mitra'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Pemasok / Supplier</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data mitra distributor dan pemasok barang dagangan</p>
    </div>
    <button type="button" onclick="openCreateSupplierModal()" 
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Supplier
    </button>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('mitra.suppliers.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama supplier, kontak, kota, kode..."
                   class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">Kode</th>
                    <th class="p-3.5">Nama Supplier</th>
                    <th class="p-3.5">Kontak Person</th>
                    <th class="p-3.5">No. Telepon</th>
                    <th class="p-3.5">Kota / Alamat</th>
                    <th class="p-3.5 text-center">Transaksi PO</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($suppliers as $s)
                    <tr class="hover:bg-slate-50/60">
                        <td class="p-3.5 font-mono font-bold text-emerald-700">{{ $s->code }}</td>
                        <td class="p-3.5 font-bold text-slate-800">
                            <a href="{{ route('mitra.suppliers.show', $s) }}" class="hover:text-emerald-600">{{ $s->name }}</a>
                        </td>
                        <td class="p-3.5 text-slate-600">{{ $s->contact_name ?? '-' }}</td>
                        <td class="p-3.5 text-slate-600">{{ $s->phone ?? '-' }}</td>
                        <td class="p-3.5 text-slate-500">{{ $s->city ?? $s->address ?? '-' }}</td>
                        <td class="p-3.5 text-center font-bold text-slate-700">{{ $s->purchases_count }} PO</td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('mitra.suppliers.show', $s) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Detail Riwayat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" onclick="openEditSupplierModal({{ json_encode($s) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('mitra.suppliers.destroy', $s) }}" onsubmit="return confirm('Hapus supplier {{ $s->name }}?')">
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
                        <td colspan="7" class="p-6 text-center text-slate-400">Belum ada supplier terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $suppliers->links() }}</div>
    </div>

</div>

<!-- Modal Supplier -->
<div id="supplierModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-4" id="supModalTitle">Tambah Supplier Baru</h3>
        <form id="supForm" method="POST" action="{{ route('mitra.suppliers.store') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="supMethod" value="POST">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="supCode" required placeholder="Contoh: SUP-004"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 uppercase font-mono font-bold focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Perusahaan / Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="supName" required placeholder="Contoh: PT Semen Jaya"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kontak Person</label>
                    <input type="text" name="contact_name" id="supContact" placeholder="Contoh: Bpk. Budi"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" id="supPhone" placeholder="Contoh: 0812-xxxx-xxxx"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="supEmail" placeholder="supplier@domain.com"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kota</label>
                    <input type="text" name="city" id="supCity" placeholder="Contoh: Jakarta"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                <textarea name="address" id="supAddress" rows="2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('supplierModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateSupplierModal() {
        document.getElementById('supModalTitle').innerText = 'Tambah Supplier Baru';
        document.getElementById('supForm').action = "{{ route('mitra.suppliers.store') }}";
        document.getElementById('supMethod').value = 'POST';
        document.getElementById('supCode').value = '';
        document.getElementById('supName').value = '';
        document.getElementById('supContact').value = '';
        document.getElementById('supPhone').value = '';
        document.getElementById('supEmail').value = '';
        document.getElementById('supCity').value = '';
        document.getElementById('supAddress').value = '';
        document.getElementById('supplierModal').classList.remove('hidden');
    }

    function openEditSupplierModal(s) {
        document.getElementById('supModalTitle').innerText = 'Edit Supplier: ' + s.name;
        document.getElementById('supForm').action = `/mitra/suppliers/${s.id}`;
        document.getElementById('supMethod').value = 'PUT';
        document.getElementById('supCode').value = s.code;
        document.getElementById('supName').value = s.name;
        document.getElementById('supContact').value = s.contact_name || '';
        document.getElementById('supPhone').value = s.phone || '';
        document.getElementById('supEmail').value = s.email || '';
        document.getElementById('supCity').value = s.city || '';
        document.getElementById('supAddress').value = s.address || '';
        document.getElementById('supplierModal').classList.remove('hidden');
    }
</script>
@endsection

