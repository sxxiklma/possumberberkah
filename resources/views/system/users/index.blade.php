@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div class="space-y-6" x-data="{ showAddModal: false, showEditModal: false, editUser: {} }">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna (RBAC)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Kelola akun kasir, admin, gudang, finance, dan role permission sistem.</p>
        </div>
        <div>
            <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Tambah Pengguna Baru
            </button>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex-1 flex items-center gap-3 w-full">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari berdasarkan nama, username, atau email..."
                       class="w-full pl-10 pr-4 py-2 text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                Cari
            </button>
            @if($search)
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Daftar Akun Pengguna Toko</h2>
            <span class="text-xs text-slate-500">Total {{ $users->total() }} pengguna</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Username</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role / Peran</th>
                        <th class="py-3 px-4">No Telepon</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                @if($u->avatar)
                                    <img src="{{ asset('storage/' . $u->avatar) }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-slate-200 shadow-2xs">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                        <span class="text-[10px] text-emerald-600 font-semibold">(Sedang Login)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700">{{ $u->username }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $u->email }}</td>
                        <td class="py-3.5 px-4">
                            @foreach($u->roles as $role)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $role->name === 'super-admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' :
                                       ($role->name === 'admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' :
                                       ($role->name === 'kasir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                       ($role->name === 'staff-gudang' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                       ($role->name === 'finance' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700')))) }}">
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $u->phone ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($u->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        @click="editUser = {
                                            id: {{ $u->id }},
                                            name: '{{ addslashes($u->name) }}',
                                            username: '{{ addslashes($u->username) }}',
                                            email: '{{ addslashes($u->email) }}',
                                            phone: '{{ addslashes($u->phone ?? '') }}',
                                            role: '{{ $u->roles->first()?->name ?? 'kasir' }}',
                                            is_active: {{ $u->is_active ? 'true' : 'false' }},
                                            update_url: '{{ route('users.update', $u->id) }}'
                                        }; showEditModal = true"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Edit Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @if($u->id !== auth()->id())
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna {{ $u->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Pengguna">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            Tidak ditemukan akun pengguna yang cocok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Pengguna Baru -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100" @click.away="showAddModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="cth: Ahmad Kasir"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Username <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" required placeholder="cth: ahmad"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="ahmad@sumberberkah.com"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">No Telepon / WA</label>
                        <input type="text" name="phone" placeholder="08123456789"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role / Peran <span class="text-rose-500">*</span></label>
                        <select name="role" required class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password Awal <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        Daftarkan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pengguna -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100" @click.away="showEditModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Perbarui Data Pengguna</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="editUser.update_url" method="POST" class="space-y-4 mt-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editUser.name" required
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Username <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" x-model="editUser.username" required
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">No Telepon / WA</label>
                        <input type="text" name="phone" x-model="editUser.phone"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role / Peran <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="editUser.role" required class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tetap menggunakan password lama"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editUser.is_active"
                           class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="edit_is_active" class="text-xs font-semibold text-slate-700">Akun Aktif (Dapat Login ke Sistem)</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
