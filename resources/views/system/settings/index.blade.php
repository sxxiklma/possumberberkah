@extends('layouts.app')

@section('title', 'Pengaturan Toko & Sistem')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'general' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Toko & Sistem</h1>
            <p class="text-slate-500 text-sm mt-0.5">Konfigurasi profil toko, aturan kasir POS, format struk, dan parameter persediaan FIFO.</p>
        </div>
    </div>

    <!-- Tab navigation pills -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <button @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Profil & Identitas Toko
        </button>
        <button @click="activeTab = 'pos'"
                :class="activeTab === 'pos' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            POS & Format Struk Kasir
        </button>
        <button @click="activeTab = 'inventory'"
                :class="activeTab === 'inventory' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Kebijakan FIFO & Gudang
        </button>
    </div>

    <!-- Form Settings -->
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tab 1: General Profile -->
        <div x-show="activeTab === 'general'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Identitas Toko</h3>
                <p class="text-xs text-slate-500 mt-0.5">Informasi ini akan tercetak pada kop faktur, struk kasir, dan laporan resmi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Badan Usaha</label>
                    <input type="text" name="store_name" value="{{ \App\Models\Setting::get('store_name', 'TOKO SUMBER BERKAH') }}" required
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slogan / Tagline</label>
                    <input type="text" name="store_tagline" value="{{ \App\Models\Setting::get('store_tagline', 'Point of Sale & Inventory Management System') }}"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No Telepon & WhatsApp</label>
                    <input type="text" name="store_phone" value="{{ \App\Models\Setting::get('store_phone', '(031) 8921234') }}"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Toko</label>
                    <input type="email" name="store_email" value="{{ \App\Models\Setting::get('store_email', 'info@tokosumberberkah.com') }}"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Toko</label>
                    <textarea name="store_address" rows="3"
                              class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">{{ \App\Models\Setting::get('store_address', 'Jl. Raya Perdagangan No. 88, Sidoarjo, Jawa Timur') }}</textarea>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Pengaturan Upload Foto & Media Berkas</span>
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">Atur batasan ukuran file gambar yang dapat diunggah pengguna sistem (foto profil akun, logo, dll).</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Maksimal Ukuran Upload Foto Profil (MB)</label>
                        <div class="relative">
                            <input type="number" min="1" max="50" step="1" name="max_upload_size_mb" 
                                   value="{{ \App\Models\Setting::get('max_upload_size_mb', 2) }}" required
                                   class="w-full text-sm border-slate-200 rounded-xl pr-12 focus:border-emerald-500 focus:ring-emerald-500 font-bold text-slate-800">
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-slate-400">MB</span>
                        </div>
                        <span class="text-[11px] text-slate-400 mt-1 block">Batas maksimal ukuran foto profil pengguna saat diunggah (standar: 2 MB, minimal: 1 MB).</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: POS Settings -->
        <div x-show="activeTab === 'pos'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Pengaturan Kasir Pintar & Struk Thermal</h3>
                <p class="text-xs text-slate-500 mt-0.5">Konfigurasi operasional meja kasir, printer struk, dan pesan bukti pembayaran.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif PPN Default (%)</label>
                    <input type="number" step="0.1" name="tax_percentage" value="{{ \App\Models\Setting::get('tax_percentage', 0) }}"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Isi 0 jika toko tidak mengenakan PPN pada kasir.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lebar Kertas Struk Thermal</label>
                    <select name="receipt_printer_width" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="58mm" {{ \App\Models\Setting::get('receipt_printer_width') === '58mm' ? 'selected' : '' }}>58 mm (Printer Kasir Standar)</option>
                        <option value="80mm" {{ \App\Models\Setting::get('receipt_printer_width') === '80mm' ? 'selected' : '' }}>80 mm (Printer Struk Lebar)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pesan Kaki Struk (Footer Struk)</label>
                    <textarea name="receipt_footer_text" rows="3"
                              class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">{{ \App\Models\Setting::get('receipt_footer_text', 'Terima kasih atas kunjungan Anda!') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Tab 3: Inventory & FIFO -->
        <div x-show="activeTab === 'inventory'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Kebijakan Persediaan FIFO & Gudang</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengaturan algoritma First In First Out dan batasan pencegahan stok negatif.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ambang Peringatan Kedaluwarsa (Hari)</label>
                    <input type="number" name="expiry_warning_days" value="{{ \App\Models\Setting::get('expiry_warning_days', 30) }}"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Notifikasi muncul jika sisa masa expired kurang dari sekian hari.</span>
                </div>

                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="fifo_strict_mode" value="0">
                        <input type="checkbox" name="fifo_strict_mode" id="fifo_strict_mode" value="1"
                               {{ \App\Models\Setting::get('fifo_strict_mode', '1') == '1' ? 'checked' : '' }}
                               class="rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="fifo_strict_mode" class="text-sm font-semibold text-slate-800">
                            Aktifkan Mode Ketat FIFO (Auto-Deduct Oldest Non-Expired Batch)
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="prevent_negative_stock" value="0">
                        <input type="checkbox" name="prevent_negative_stock" id="prevent_negative_stock" value="1"
                               {{ \App\Models\Setting::get('prevent_negative_stock', '1') == '1' ? 'checked' : '' }}
                               class="rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="prevent_negative_stock" class="text-sm font-semibold text-slate-800">
                            Cegah Stok Negatif (Tolak Penjualan Jika Batch Habis)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
