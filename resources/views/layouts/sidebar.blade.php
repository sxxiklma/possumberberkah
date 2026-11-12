<!-- Sidebar Backdrop on Mobile -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false" 
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden" 
     x-cloak></div>

<!-- Sidebar Container -->
<aside class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out border-r border-slate-800 shadow-xl overflow-x-hidden"
       :class="{'w-64': !sidebarCollapsed, 'w-20': sidebarCollapsed, '-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen}">
    
    <!-- Brand / Logo Area -->
    <div class="h-16 flex items-center border-b border-slate-800 bg-slate-950/70 shrink-0 transition-all duration-300"
         :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-4'">
        
        <!-- Expanded State (Full Brand + Collapse Button) -->
        <div x-show="!sidebarCollapsed" class="flex items-center justify-between w-full">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-lg shadow-md shadow-emerald-500/20 shrink-0 group-hover:scale-105 transition-transform">
                    SB
                </div>
                <div class="flex flex-col whitespace-nowrap overflow-hidden">
                    <span class="font-extrabold text-white text-sm tracking-wide">SUMBER BERKAH</span>
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">POS & Inventory</span>
                </div>
            </a>
            <button @click="sidebarCollapsed = true" 
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                    title="Kecilkan Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Collapsed State (Centered Uncut Logo with Expand Cue) -->
        <div x-show="sidebarCollapsed" x-cloak class="flex items-center justify-center w-full">
            <button @click="sidebarCollapsed = false" 
                    class="relative group flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 font-black text-base shadow-md shadow-emerald-500/25 shrink-0 hover:scale-105 active:scale-95 transition-all cursor-pointer"
                    title="Buka / Perlebar Sidebar">
                SB
                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-slate-900 text-emerald-400 border border-slate-700 flex items-center justify-center shadow-sm group-hover:bg-emerald-500 group-hover:text-slate-950 transition-colors">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                </span>
            </button>
        </div>
    </div>

    <!-- Navigation Menu (Accordion) -->
    <nav class="flex-1 overflow-y-auto px-2 sm:px-3 py-4 space-y-1.5 custom-scrollbar text-sm">
        
        <!-- 1. Dashboard -->
        <a href="{{ route('dashboard') }}" 
           title="Dashboard"
           class="flex items-center rounded-xl font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-semibold' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}"
           :class="sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'gap-3 px-3 py-2.5'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
        </a>

        <!-- 2. Kasir Pintar (POS Quick Action) -->
        <a href="{{ route('pos.index') }}" 
           title="Kasir Pintar (POS)"
           class="flex items-center rounded-xl font-medium transition {{ request()->routeIs('pos.*') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-semibold' : 'bg-slate-800/80 text-emerald-400 hover:bg-slate-800 hover:text-emerald-300' }}"
           :class="sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'gap-3 px-3 py-2.5'">
            <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span x-show="!sidebarCollapsed" class="truncate font-semibold tracking-wide flex-1">Kasir Pintar</span>
            <span x-show="!sidebarCollapsed" class="text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">POS</span>
        </a>

        <!-- 3. Gudang (Accordion) -->
        <div x-data="{ open: activeAccordion === 'inventory' || {{ request()->is('inventory*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'inventory'; } else { open = !open; activeAccordion = open ? 'inventory' : ''; }" 
                    title="Gudang & Stok"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('inventory*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Gudang</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('inventory.stock-summary') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('inventory.stock-summary') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Ringkasan Stok</a>
                <a href="{{ route('master.products.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.products.index') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Daftar Produk</a>
                <a href="{{ route('master.warehouses.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.warehouses.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Lokasi Gudang</a>
                <a href="{{ route('inventory.stock-card') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('inventory.stock-card*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Kartu Stok</a>
                <a href="{{ route('inventory.min-stock') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('inventory.min-stock') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Stok Minimum</a>
                <a href="{{ route('inventory.expired-stock') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('inventory.expired-stock') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Stok Kedaluwarsa</a>
                <a href="{{ route('inventory.opname.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('inventory.opname.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Stok Opname</a>
            </div>
        </div>

        <!-- 4. Master Data (Accordion) -->
        <div x-data="{ open: activeAccordion === 'master' || {{ request()->is('master*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'master'; } else { open = !open; activeAccordion = open ? 'master' : ''; }" 
                    title="Master Data"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('master*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Master Data</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('master.products.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.products.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Master Produk</a>
                <a href="{{ route('master.categories.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.categories.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Kategori Produk</a>
                <a href="{{ route('master.brands.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.brands.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Merek Produk</a>
                <a href="{{ route('master.units.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.units.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Satuan Produk</a>
                <a href="{{ route('master.warehouses.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('master.warehouses.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Gudang & Rak</a>
            </div>
        </div>

        <!-- 5. Transaksi (Accordion) -->
        <div x-data="{ open: activeAccordion === 'transactions' || {{ request()->is('transactions*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'transactions'; } else { open = !open; activeAccordion = open ? 'transactions' : ''; }" 
                    title="Transaksi"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('transactions*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Transaksi</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('transactions.sales.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('transactions.sales.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Penjualan</a>
                <a href="{{ route('transactions.purchases.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('transactions.purchases.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Barang Masuk (PO)</a>
                <a href="{{ route('transactions.stock-outs.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('transactions.stock-outs.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Barang Keluar</a>
                <a href="{{ route('pos.history') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('pos.history') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Riwayat Kasir POS</a>
            </div>
        </div>

        <!-- 6. Mitra (Accordion) -->
        <div x-data="{ open: activeAccordion === 'mitra' || {{ request()->is('mitra*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'mitra'; } else { open = !open; activeAccordion = open ? 'mitra' : ''; }" 
                    title="Mitra Toko"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('mitra*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Mitra Toko</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('mitra.suppliers.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('mitra.suppliers.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Supplier (Pemasok)</a>
                <a href="{{ route('mitra.customers.index') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('mitra.customers.*') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Customer (Pelanggan)</a>
            </div>
        </div>

        <!-- 7. Keuangan (Accordion) -->
        <div x-data="{ open: activeAccordion === 'finance' || {{ request()->is('finance*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'finance'; } else { open = !open; activeAccordion = open ? 'finance' : ''; }" 
                    title="Keuangan & Kas"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('finance*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Keuangan</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('finance.payments') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('finance.payments') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Riwayat Pembayaran</a>
                <a href="{{ route('finance.receivables') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('finance.receivables') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Piutang Customer</a>
                <a href="{{ route('finance.payables') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('finance.payables') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Hutang Supplier</a>
                <a href="{{ route('finance.cash') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('finance.cash') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Buku Kas Toko</a>
                <a href="{{ route('finance.expenses') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('finance.expenses') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Beban Operasional</a>
            </div>
        </div>

        <!-- 8. Laporan (Accordion) -->
        <div x-data="{ open: activeAccordion === 'reports' || {{ request()->is('reports*') ? 'true' : 'false' }} }">
            <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; activeAccordion = 'reports'; } else { open = !open; activeAccordion = open ? 'reports' : ''; }" 
                    title="Laporan Toko"
                    class="w-full flex items-center rounded-xl font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                    :class="[
                        {'text-white bg-slate-800/50': open && !sidebarCollapsed},
                        {'text-emerald-400 bg-slate-800/80 border border-emerald-500/30 font-semibold': sidebarCollapsed && {{ request()->is('reports*') ? 'true' : 'false' }}},
                        sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'justify-between px-3 py-2.5'
                    ]">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Laporan</span>
                </div>
                <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 py-1 space-y-1 text-xs">
                <a href="{{ route('reports.sales') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('reports.sales') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Laporan Penjualan</a>
                <a href="{{ route('reports.purchases') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('reports.purchases') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Laporan Pembelian</a>
                <a href="{{ route('reports.inventory') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('reports.inventory') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Laporan Persediaan</a>
                <a href="{{ route('reports.products') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('reports.products') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Laporan Produk & Mutasi</a>
                <a href="{{ route('reports.financial') }}" class="block py-1.5 px-2.5 rounded-lg {{ request()->routeIs('reports.financial') ? 'text-emerald-400 font-semibold bg-slate-800/60' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">Laporan Laba Rugi</a>
            </div>
        </div>

        <div class="pt-3 pb-1 border-t border-slate-800/80 my-1" :class="sidebarCollapsed ? 'mx-1' : ''">
            <span x-show="!sidebarCollapsed" class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Administrasi</span>
        </div>

        <!-- 9. Pengguna -->
        <a href="{{ route('users.index') }}" 
           title="Pengguna & Role"
           class="flex items-center rounded-xl font-medium transition {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white font-semibold shadow-md shadow-emerald-600/30' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}"
           :class="sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'gap-3 px-3 py-2.5'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" class="truncate">Pengguna & Role</span>
        </a>

        <!-- 10. Audit Log -->
        <a href="{{ route('audit-logs.index') }}" 
           title="Audit Log Aktivitas"
           class="flex items-center rounded-xl font-medium transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-600 text-white font-semibold shadow-md shadow-emerald-600/30' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}"
           :class="sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'gap-3 px-3 py-2.5'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" class="truncate">Audit Log</span>
        </a>

        <!-- 11. Pengaturan -->
        <a href="{{ route('settings.index') }}" 
           title="Pengaturan Toko"
           class="flex items-center rounded-xl font-medium transition {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white font-semibold shadow-md shadow-emerald-600/30' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}"
           :class="sidebarCollapsed ? 'justify-center w-11 h-11 mx-auto px-0' : 'gap-3 px-3 py-2.5'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" class="truncate">Pengaturan Toko</span>
        </a>
    </nav>

    <!-- User Profile Bar Bottom -->
    <div class="border-t border-slate-800 bg-slate-950/70 flex items-center shrink-0 transition-all duration-300"
         :class="sidebarCollapsed ? 'justify-center p-2.5' : 'justify-between p-3'">
        <div class="flex items-center overflow-hidden" :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
            <a href="{{ route('profile') }}" 
               class="w-10 h-10 rounded-full overflow-hidden shrink-0 hover:scale-105 active:scale-95 transition-transform flex items-center justify-center border border-emerald-500/40"
               title="{{ auth()->user()->name ?? 'Pengguna' }} ({{ ucwords(str_replace('-', ' ', auth()->user()->roles->first()?->name ?? 'Staff')) }})">
                @if(auth()->user()?->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </a>
            <div x-show="!sidebarCollapsed" class="flex flex-col overflow-hidden">
                <span class="font-bold text-white text-xs truncate">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                <span class="text-[10px] text-emerald-400 font-medium truncate">{{ ucwords(str_replace('-', ' ', auth()->user()->roles->first()?->name ?? 'Staff')) }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" x-show="!sidebarCollapsed">
            @csrf
            <button type="submit" title="Keluar / Logout" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</aside>
