@extends('layouts.app', ['title' => 'Merek Produk', 'activeAccordion' => 'master'])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Merek / Brand Produk</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola daftar merek, produsen, dan pabrikan produk toko</p>
    </div>
    <button type="button" onclick="openCreateModal()" 
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Merek
    </button>
</div>
@endsection

@section('content')
<div class="space-y-4 max-w-4xl">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.brands.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau deskripsi merek..."
                   class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold cursor-pointer">Cari</button>
            @if($search)
                <a href="{{ route('master.brands.index') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5 w-12 text-center">#</th>
                    <th class="p-3.5">Nama Merek / Brand</th>
                    <th class="p-3.5">Deskripsi</th>
                    <th class="p-3.5 text-center">Jumlah Produk</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($brands as $index => $brand)
                    <tr class="hover:bg-slate-50/60">
                        <td class="p-3.5 text-center font-mono text-slate-400">{{ $brands->firstItem() + $index }}</td>
                        <td class="p-3.5">
                            <span class="font-bold text-slate-800 text-sm block">{{ $brand->name }}</span>
                        </td>
                        <td class="p-3.5 text-slate-500 max-w-xs">{{ $brand->description ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $brand->products_count > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $brand->products_count }} item
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        onclick="openEditModal({{ json_encode(['id' => $brand->id, 'name' => $brand->name, 'description' => $brand->description]) }})" 
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition cursor-pointer" 
                                        title="Edit Merek">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('master.brands.destroy', $brand) }}" onsubmit="return confirm('Hapus merek {{ $brand->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" title="Hapus Merek">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-400">Belum ada merek yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $brands->links() }}</div>
    </div>

</div>

<!-- Modal Create / Edit Brand -->
<div id="brandModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900" id="brandModalTitle">Tambah Merek</h3>
            <button type="button" onclick="closeBrandModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="brandForm" method="POST" action="{{ route('master.brands.store') }}" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="brandMethod" value="POST">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Merek / Brand <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="brandName" required placeholder="Contoh: Bogasari, Blue Band, Anchor"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white font-medium">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Deskripsi / Keterangan (Opsional)</label>
                <textarea name="description" id="brandDesc" rows="3" placeholder="Contoh: Produsen tepung terigu Segitiga Biru & Cakra Kembar"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeBrandModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20 transition cursor-pointer">
                    Simpan Merek
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const brandModal = document.getElementById('brandModal');
    const brandForm = document.getElementById('brandForm');
    const brandTitle = document.getElementById('brandModalTitle');
    const brandMethod = document.getElementById('brandMethod');
    const brandName = document.getElementById('brandName');
    const brandDesc = document.getElementById('brandDesc');

    function openCreateModal() {
        brandTitle.innerText = 'Tambah Merek Baru';
        brandForm.action = '{{ route("master.brands.store") }}';
        brandMethod.value = 'POST';
        brandName.value = '';
        brandDesc.value = '';
        brandModal.classList.remove('hidden');
        setTimeout(() => brandName.focus(), 100);
    }

    function openEditModal(brand) {
        brandTitle.innerText = 'Edit Merek / Brand';
        brandForm.action = '{{ url("master/brands") }}/' + brand.id;
        brandMethod.value = 'PUT';
        brandName.value = brand.name || '';
        brandDesc.value = brand.description || '';
        brandModal.classList.remove('hidden');
        setTimeout(() => brandName.focus(), 100);
    }

    function closeBrandModal() {
        brandModal.classList.add('hidden');
    }
</script>
@endsection

