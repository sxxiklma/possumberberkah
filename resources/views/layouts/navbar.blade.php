<header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6">
    <!-- Left: Hamburger & Page Title / Breadcrumb context -->
    <div class="flex items-center gap-3">
        <button @click="if (window.innerWidth >= 1024) { sidebarCollapsed = !sidebarCollapsed; } else { sidebarOpen = !sidebarOpen; }" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition focus:outline-none cursor-pointer"
                title="Buka / Tutup Sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden sm:flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                TOKO SUMBER BERKAH
            </span>
            <span class="text-slate-400">/</span>
            <span class="text-slate-600 font-medium">{{ $title ?? 'POS & Inventory' }}</span>
        </div>
    </div>

    <!-- Right: Real-time clock, Quick Actions, Profile -->
    <div class="flex items-center gap-3 sm:gap-4">
        
        <!-- Live Realtime Clock -->
        <div class="hidden md:flex flex-col text-right" id="realtimeClockContainer">
            <span class="text-xs font-semibold text-slate-700" id="liveTime">--:--:-- WIB</span>
            <span class="text-[10px] text-slate-400" id="liveDate">Hari ini</span>
        </div>

        <!-- POS Quick Link Button -->
        <a href="{{ route('pos.index') }}" 
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm shadow-emerald-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="hidden sm:inline">Buka Kasir</span>
        </a>

        <!-- Notifications Dropdown -->
        <style>
            @media (max-width: 639px) {
                .notif-dropdown-container {
                    position: fixed !important;
                    top: 64px !important;
                    left: 12px !important;
                    right: 12px !important;
                    width: auto !important;
                    max-width: 420px !important;
                    margin-left: auto !important;
                    margin-right: auto !important;
                }
            }
            @media (min-width: 640px) {
                .notif-dropdown-container {
                    position: absolute !important;
                    right: 0 !important;
                    top: 100% !important;
                    margin-top: 8px !important;
                    width: 380px !important;
                    left: auto !important;
                }
            }
        </style>
        <div class="relative" x-data="notificationCenter()" @click.outside="open = false">
            <button @click="open = !open" 
                    class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition relative cursor-pointer"
                    title="Notifikasi Sistem">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <template x-if="unreadCount > 0">
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </template>
            </button>

            <!-- Dropdown Container -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="notif-dropdown-container bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 text-xs overflow-hidden"
                 x-cloak>
                
                <!-- Header -->
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/90">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-xs sm:text-sm">Notifikasi Sistem</span>
                        <template x-if="unreadCount > 0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800" x-text="unreadCount + ' Baru'"></span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click.stop="markAllAsRead()" 
                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 hover:underline cursor-pointer flex items-center gap-1"
                                title="Tandai semua notifikasi sudah dibaca">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Tandai Semua</span>
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    <template x-for="item in items" :key="item.id">
                        <div class="p-3.5 transition-colors"
                             :class="!item.is_read ? 'bg-emerald-50/25' : 'bg-white'">
                            
                            <!-- Top: Indicator, Title, Time, and 'X' Button -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-2 flex-1 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 mt-1"
                                          :class="{
                                              'bg-amber-500 ring-2 ring-amber-100': item.color === 'amber',
                                              'bg-indigo-500 ring-2 ring-indigo-100': item.color === 'indigo',
                                              'bg-rose-500 ring-2 ring-rose-100': item.color === 'rose',
                                              'bg-emerald-500 ring-2 ring-emerald-100': item.color === 'emerald'
                                          }"></span>
                                    <div class="min-w-0 flex-1">
                                        <a :href="item.url" @click="markAsRead(item.id)" class="block group cursor-pointer">
                                            <h4 class="text-xs font-bold leading-snug group-hover:text-emerald-700 transition"
                                                :class="!item.is_read ? 'text-slate-900' : 'text-slate-600'"
                                                x-text="item.title"></h4>
                                        </a>
                                        <p class="text-slate-500 text-[11px] leading-relaxed mt-0.5" x-text="item.message"></p>
                                    </div>
                                </div>

                                <!-- Time badge + Tombol X Hapus -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium"
                                          :class="!item.is_read ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500'"
                                          x-text="item.time"></span>

                                    <!-- Tombol X (Hapus / Dismiss Item Notifikasi) -->
                                    <button type="button" 
                                            @click.stop="removeNotification(item.id)" 
                                            class="w-6 h-6 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 border border-slate-100 hover:border-red-200 transition cursor-pointer" 
                                            title="Hapus notifikasi ini">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Bottom: Actions Per Item (Tandai Dibaca & Buka Menu) -->
                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <!-- Tombol Tandai Dibaca (Per Item) -->
                                <div>
                                    <template x-if="!item.is_read">
                                        <button type="button" 
                                                @click.stop="markAsRead(item.id)" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer"
                                                title="Tandai notifikasi ini sudah dibaca">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Tandai Dibaca</span>
                                        </button>
                                    </template>
                                    <template x-if="item.is_read">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium text-slate-400 bg-slate-50 rounded border border-slate-200">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Sudah Dibaca</span>
                                        </span>
                                    </template>
                                </div>

                                <a :href="item.url" @click="markAsRead(item.id)" class="inline-flex items-center gap-1 font-semibold text-slate-500 hover:text-emerald-600 hover:underline">
                                    <span>Buka Menu</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="items.length === 0" class="py-8 px-4 text-center">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 border border-emerald-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="font-bold text-slate-800 text-xs sm:text-sm">Semua Notifikasi Bersih</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pemberitahuan baru.</p>
                        <button type="button" @click="resetNotifications()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Pulihkan Notifikasi</span>
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div x-show="items.length > 0" class="px-4 py-2.5 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span x-text="items.length + ' pemberitahuan'"></span>
                    <button type="button" @click="items = []; save();" class="text-red-500 hover:text-red-700 hover:underline font-medium cursor-pointer" title="Hapus semua notifikasi">
                        Hapus Semua
                    </button>
                </div>

            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-100 transition focus:outline-none cursor-pointer">
                @if(auth()->user()?->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-xl object-cover shadow-sm ring-1 ring-slate-200">
                @else
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                @endif
                <div class="hidden sm:flex flex-col text-left">
                    <span class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                    <span class="text-[10px] text-slate-400">{{ auth()->user()->roles->first()?->name ?? 'Kasir' }}</span>
                </div>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" 
                 x-transition 
                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 text-xs"
                 x-cloak>
                <div class="px-4 py-2 border-b border-slate-100">
                    <p class="font-bold text-slate-800">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-slate-700 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Saya
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 text-slate-700 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Pengaturan Toko
                </a>
                <div class="border-t border-slate-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-red-600 hover:bg-red-50 text-left transition font-medium">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

<script>
    function notificationCenter() {
        const defaultNotifs = [
            {
                id: 1,
                title: 'Pemeriksaan Stok Minimum',
                message: 'Cek produk yang berada di bawah ambang batas stok minimum.',
                url: '{{ route("inventory.min-stock") }}',
                time: 'Penting',
                color: 'amber',
                is_read: false
            },
            {
                id: 2,
                title: 'Jatuh Tempo Piutang',
                message: 'Pantau piutang customer tempo dan lakukan pelunasan.',
                url: '{{ route("finance.receivables") }}',
                time: 'Hari ini',
                color: 'indigo',
                is_read: false
            },
            {
                id: 3,
                title: 'Peringatan Kedaluwarsa',
                message: 'Terdapat batch persediaan mendekati tanggal kedaluwarsa.',
                url: '{{ route("inventory.expired-stock") }}',
                time: '1 jam lalu',
                color: 'rose',
                is_read: false
            }
        ];

        return {
            open: false,
            items: [],
            init() {
                try {
                    const saved = localStorage.getItem('sumber_berkah_notifs_v2');
                    if (saved) {
                        this.items = JSON.parse(saved);
                    } else {
                        this.items = defaultNotifs;
                    }
                } catch(e) {
                    this.items = defaultNotifs;
                }
            },
            save() {
                try {
                    localStorage.setItem('sumber_berkah_notifs_v2', JSON.stringify(this.items));
                } catch(e) {}
            },
            get unreadCount() {
                return (this.items || []).filter(i => !i.is_read).length;
            },
            markAllAsRead() {
                this.items = (this.items || []).map(i => ({ ...i, is_read: true }));
                this.save();
                if (window.showToast) window.showToast('success', 'Semua notifikasi ditandai dibaca');
            },
            markAsRead(id) {
                const target = (this.items || []).find(i => i.id === id);
                if (target) {
                    target.is_read = true;
                    this.save();
                }
            },
            removeNotification(id) {
                this.items = (this.items || []).filter(i => i.id !== id);
                this.save();
            },
            resetNotifications() {
                this.items = defaultNotifs.map(i => ({ ...i, is_read: false }));
                this.save();
            }
        };
    }

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
        const dateStr = now.toLocaleDateString('id-ID', options);
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        
        const liveTimeEl = document.getElementById('liveTime');
        const liveDateEl = document.getElementById('liveDate');
        if (liveTimeEl) liveTimeEl.innerText = timeStr;
        if (liveDateEl) liveDateEl.innerText = dateStr;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

