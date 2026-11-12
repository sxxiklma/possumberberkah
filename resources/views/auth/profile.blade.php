@extends('layouts.app', ['title' => 'Profil Pengguna', 'activeAccordion' => ''])

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Profil Pengguna</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola informasi data akun, foto profil, dan kata sandi Anda</p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs"
     x-data="{
        previewUrl: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}',
        hasExistingPhoto: {{ $user->avatar ? 'true' : 'false' }},
        removeAvatar: false,
        maxSizeMb: {{ $maxUploadMb ?? 2 }},
        errorMessage: '',
        handlePhotoChange(e) {
            const file = e.target.files[0];
            this.errorMessage = '';
            if (!file) return;
            
            // Format check
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                this.errorMessage = 'Format file tidak didukung! Harap gunakan file gambar JPG, JPEG, PNG, atau WEBP.';
                e.target.value = '';
                return;
            }

            // Size check
            if (file.size > this.maxSizeMb * 1024 * 1024) {
                const actualMb = (file.size / (1024 * 1024)).toFixed(2);
                this.errorMessage = `Ukuran file (${actualMb} MB) melebihi batas maksimal ${this.maxSizeMb} MB sesuai Pengaturan Sistem!`;
                e.target.value = '';
                return;
            }

            this.previewUrl = URL.createObjectURL(file);
            this.removeAvatar = false;
        },
        deletePhoto() {
            this.previewUrl = '';
            this.removeAvatar = true;
            this.errorMessage = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
     }">

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

        <!-- Bagian Foto Profil & Header Pengguna -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 border-b border-slate-100">
            <!-- Avatar Display dengan Preview & Tombol Kamera -->
            <div class="relative group w-20 h-20 shrink-0">
                <!-- Tampilan Foto Preview / Eksisting -->
                <template x-if="previewUrl && !removeAvatar">
                    <img :src="previewUrl" alt="{{ $user->name }}" 
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-emerald-500 shadow-md shadow-emerald-500/10">
                </template>
                <!-- Inisial Huruf Jika Tanpa Foto -->
                <template x-if="!previewUrl || removeAvatar">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center font-bold text-3xl shadow-md shadow-emerald-500/20">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </template>

                <!-- Tombol Kamera Overlay -->
                <button type="button" @click="$refs.fileInput.click()" 
                        class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-md border-2 border-white transition transform active:scale-95 cursor-pointer" 
                        title="Pilih / Ganti Foto Profil">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>

            <!-- Kontrol & Keterangan Upload -->
            <div class="flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $user->name }}</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                                {{ $user->roles->first()?->name ?? 'Staff' }}
                            </span>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs text-slate-500">{{ $user->email }}</span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Foto -->
                    <div class="flex items-center gap-2 mt-2 sm:mt-0">
                        <button type="button" @click="$refs.fileInput.click()"
                                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span x-text="(previewUrl && !removeAvatar) ? 'Ganti Foto' : 'Unggah Foto'"></span>
                        </button>

                        <template x-if="(previewUrl && !removeAvatar) || (hasExistingPhoto && !removeAvatar)">
                            <button type="button" @click="deletePhoto()"
                                    class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold border border-red-200 transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Foto</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Input File Tersembunyi -->
                <input type="file" x-ref="fileInput" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" 
                       @change="handlePhotoChange($event)" class="hidden">

                <!-- Badge Keterangan Batas File -->
                <div class="mt-2.5 flex items-center gap-2 text-[11px] text-slate-500 flex-wrap">
                    <span class="inline-flex items-center gap-1 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200 text-slate-600 font-medium">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Format: JPG, PNG, WEBP &bull; Maks. <span class="font-bold text-emerald-700" x-text="maxSizeMb + ' MB'"></span>
                    </span>
                    <a href="{{ route('settings.index') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 hover:underline">
                        (Dapat diubah di Pengaturan Sistem)
                    </a>
                </div>

                <!-- Notifikasi Peringatan Client-Side & Server-Side -->
                <template x-if="errorMessage">
                    <div class="mt-2 text-xs font-semibold text-red-600 bg-red-50 p-2.5 rounded-xl border border-red-200 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>
                @error('avatar')
                    <div class="mt-2 text-xs font-semibold text-red-600 bg-red-50 p-2.5 rounded-xl border border-red-200 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">
            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Tidak dapat diubah)</label>
            <input type="email" value="{{ $user->email }}" disabled
                   class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-500 cursor-not-allowed">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
            <textarea name="address" rows="2"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">{{ old('address', $user->address) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Ubah Kata Sandi (Opsional)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer">
                Simpan Perubahan Profil
            </button>
        </div>
    </form>
</div>
@endsection
