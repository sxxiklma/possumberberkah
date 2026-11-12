@extends('layouts.app', ['title' => 'Customer / Pelanggan', 'activeAccordion' => 'mitra'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Pelanggan / Customer</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data pelanggan (Umum, Retail, Grosir, Reseller, Pelanggan Tetap)</p>
    </div>
    <button type="button" onclick="openCreateCustomerModal()" 
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Customer
    </button>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('mitra.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, nomor telepon, kode..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Kategori Customer</option>
                    <option value="umum" {{ $category === 'umum' ? 'selected' : '' }}>Umum</option>
                    <option value="retail" {{ $category === 'retail' ? 'selected' : '' }}>Retail</option>
                    <option value="grosir" {{ $category === 'grosir' ? 'selected' : '' }}>Grosir</option>
                    <option value="reseller" {{ $category === 'reseller' ? 'selected' : '' }}>Reseller</option>
                    <option value="pelanggan_tetap" {{ $category === 'pelanggan_tetap' ? 'selected' : '' }}>Pelanggan Tetap</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
                <a href="{{ route('mitra.customers.index') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">Kode</th>
                    <th class="p-3.5">Nama Customer</th>
                    <th class="p-3.5">Kategori</th>
                    <th class="p-3.5">No. Telepon / WA</th>
                    <th class="p-3.5">Kota / Alamat</th>
                    <th class="p-3.5 text-center">Transaksi</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $c)
                    <tr class="hover:bg-slate-50/60">
                        <td class="p-3.5 font-mono font-bold text-emerald-700">{{ $c->code }}</td>
                        <td class="p-3.5 font-bold text-slate-800">
                            <a href="{{ route('mitra.customers.show', $c) }}" class="hover:text-emerald-600">{{ $c->name }}</a>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $c->category === 'grosir' ? 'bg-amber-100 text-amber-800' : ($c->category === 'pelanggan_tetap' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-600') }}">
                                {{ str_replace('_', ' ', $c->category) }}
                            </span>
                        </td>
                        <td class="p-3.5 text-slate-600">{{ $c->phone ?? '-' }}</td>
                        <td class="p-3.5 text-slate-500">{{ $c->city ?? $c->address ?? '-' }}</td>
                        <td class="p-3.5 text-center font-bold text-slate-700">{{ $c->sales_count }} Kali</td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('mitra.customers.show', $c) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Detail Riwayat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" onclick="openEditCustomerModal({{ json_encode($c) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('mitra.customers.destroy', $c) }}" onsubmit="return confirm('Hapus customer {{ $c->name }}?')">
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
                        <td colspan="7" class="p-6 text-center text-slate-400">Belum ada data customer terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $customers->links() }}</div>
    </div>

</div>

<!-- Modal Customer -->
<div id="customerModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-4" id="custModalTitle">Tambah Customer Baru</h3>
        <form id="custForm" method="POST" action="{{ route('mitra.customers.store') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="custMethod" value="POST">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Customer <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="custCode" required placeholder="Contoh: CUST-005"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 uppercase font-mono font-bold focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Customer <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="custName" required placeholder="Contoh: Toko Berkah Mandiri"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Customer <span class="text-red-500">*</span></label>
                    <select name="category" id="custCategory" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="umum">Umum</option>
                        <option value="retail">Retail</option>
                        <option value="grosir">Grosir</option>
                        <option value="reseller">Reseller</option>
                        <option value="pelanggan_tetap">Pelanggan Tetap</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" id="custPhone" placeholder="Contoh: 0812-xxxx-xxxx"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="custEmail" placeholder="customer@domain.com"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kota</label>
                    <input type="text" name="city" id="custCity" placeholder="Contoh: Bogor"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                <textarea name="address" id="custAddress" rows="2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('customerModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateCustomerModal() {
        document.getElementById('custModalTitle').innerText = 'Tambah Customer Baru';
        document.getElementById('custForm').action = "{{ route('mitra.customers.store') }}";
        document.getElementById('custMethod').value = 'POST';
        document.getElementById('custCode').value = '';
        document.getElementById('custName').value = '';
        document.getElementById('custCategory').value = 'umum';
        document.getElementById('custPhone').value = '';
        document.getElementById('custEmail').value = '';
        document.getElementById('custCity').value = '';
        document.getElementById('custAddress').value = '';
        document.getElementById('customerModal').classList.remove('hidden');
    }

    function openEditCustomerModal(c) {
        document.getElementById('custModalTitle').innerText = 'Edit Customer: ' + c.name;
        document.getElementById('custForm').action = `/mitra/customers/${c.id}`;
        document.getElementById('custMethod').value = 'PUT';
        document.getElementById('custCode').value = c.code;
        document.getElementById('custName').value = c.name;
        document.getElementById('custCategory').value = c.category;
        document.getElementById('custPhone').value = c.phone || '';
        document.getElementById('custEmail').value = c.email || '';
        document.getElementById('custCity').value = c.city || '';
        document.getElementById('custAddress').value = c.address || '';
        document.getElementById('customerModal').classList.remove('hidden');
    }
</script>
@endsection

