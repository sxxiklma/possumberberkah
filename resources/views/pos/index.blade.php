<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir Pintar (POS) - {{ $storeName }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden text-slate-800 antialiased"
      x-data="posApp()"
      x-init="initPos()">

    <!-- Toast Notification for Mobile / Desktop Quick Add -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-16 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white px-4 py-2 rounded-full text-xs font-semibold shadow-xl backdrop-blur-xs z-50 flex items-center gap-2 pointer-events-none"
         x-cloak>
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        <span x-text="addedToastText" class="truncate max-w-[220px]"></span>
    </div>

    <div class="h-screen flex flex-col overflow-hidden">
        
        <!-- POS Top Header -->
        <header class="bg-slate-900 text-white shrink-0 shadow-md z-30">
            <!-- Top Main Bar -->
            <div class="h-14 px-3 sm:px-4 flex items-center justify-between gap-2">
                <!-- Brand & Back -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('dashboard') }}" class="p-1.5 sm:p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Kembali ke Dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white font-black text-xs sm:text-sm flex items-center justify-center shrink-0 shadow-xs">SB</div>
                        <div class="min-w-0">
                            <h1 class="text-xs font-bold leading-tight truncate max-w-[120px] sm:max-w-none">{{ $storeName }}</h1>
                            <p class="text-[9px] sm:text-[10px] text-emerald-400 font-medium hidden sm:block">Kasir Pintar POS &bull; Shift Aktif</p>
                        </div>
                    </div>
                </div>

                <!-- Desktop Search Bar (Center) -->
                <div class="hidden lg:block flex-1 max-w-md mx-4">
                    <div class="relative">
                        <input type="text" 
                               x-ref="barcodeSearchInput"
                               x-model="searchQuery" 
                               @keydown.enter.prevent="handleBarcodeOrSearch()"
                               placeholder="Scan Barcode / Cari nama produk (Tekan Enter)..."
                               class="w-full bg-slate-800 text-white placeholder:text-slate-400 text-xs rounded-xl pl-9 pr-4 py-2 border border-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Retail vs Wholesale Toggle -->
                    <div class="bg-slate-800 p-0.5 rounded-lg flex items-center text-[10px] sm:text-[11px] font-semibold border border-slate-700">
                        <button type="button" @click="setPriceType('retail')" 
                                class="px-2 sm:px-2.5 py-1 rounded-md transition"
                                :class="priceType === 'retail' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-400 hover:text-white'">
                            Retail
                        </button>
                        <button type="button" @click="setPriceType('wholesale')" 
                                class="px-2 sm:px-2.5 py-1 rounded-md transition"
                                :class="priceType === 'wholesale' ? 'bg-amber-600 text-white shadow-2xs' : 'text-slate-400 hover:text-white'">
                            Grosir
                        </button>
                    </div>

                    <!-- History Button -->
                    <a href="{{ route('pos.history') }}" class="p-2 sm:px-2.5 sm:py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition border border-slate-700 flex items-center gap-1.5" title="Riwayat Transaksi">
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="hidden sm:inline">Riwayat</span>
                    </a>

                    <!-- Mobile Cart Switcher Button with live badge -->
                    <button type="button" 
                            @click="activeMobileTab = (activeMobileTab === 'cart' ? 'catalog' : 'cart')"
                            class="lg:hidden relative p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white transition border border-slate-700 flex items-center"
                            :class="activeMobileTab === 'cart' ? 'border-emerald-500 bg-slate-700 text-emerald-400' : ''"
                            title="Buka Keranjang">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="cartTotalItems > 0" 
                              class="absolute -top-1.5 -right-1.5 bg-emerald-500 text-slate-900 text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center shadow-xs"
                              x-text="cartTotalItems"></span>
                    </button>

                    <!-- Cashier Name (Desktop) -->
                    <div class="hidden xl:flex items-center gap-2 pl-2 border-l border-slate-700 text-xs">
                        <span class="font-bold text-slate-200">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Bar (Below Header on < lg) -->
            <div class="lg:hidden px-3 pb-2.5">
                <div class="relative">
                    <input type="text" 
                           x-ref="barcodeSearchInputMobile"
                           x-model="searchQuery" 
                           @keydown.enter.prevent="handleBarcodeOrSearch()"
                           placeholder="Scan Barcode / Cari nama produk..."
                           class="w-full bg-slate-800 text-white placeholder:text-slate-400 text-xs rounded-xl pl-9 pr-8 py-2 border border-slate-700 focus:outline-none focus:border-emerald-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Tab Navigation: [Katalog Produk] / [Keranjang Belanja] -->
            <div class="lg:hidden flex border-t border-slate-800 bg-slate-950/60 text-xs">
                <button type="button" 
                        @click="activeMobileTab = 'catalog'"
                        class="flex-1 py-2.5 font-bold text-center border-b-2 transition flex items-center justify-center gap-1.5"
                        :class="activeMobileTab === 'catalog' ? 'border-emerald-500 text-emerald-400 bg-slate-800/60' : 'border-transparent text-slate-400 hover:text-slate-200'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Katalog Produk</span>
                </button>
                <button type="button" 
                        @click="activeMobileTab = 'cart'"
                        class="flex-1 py-2.5 font-bold text-center border-b-2 transition flex items-center justify-center gap-1.5"
                        :class="activeMobileTab === 'cart' ? 'border-emerald-500 text-emerald-400 bg-slate-800/60' : 'border-transparent text-slate-400 hover:text-slate-200'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Keranjang</span>
                    <span x-show="cartTotalItems > 0" class="px-1.5 py-0.2 bg-emerald-500 text-slate-900 rounded-full text-[10px] font-black" x-text="cartTotalItems"></span>
                </button>
            </div>
        </header>

        <!-- POS Main Body -->
        <div class="flex-1 flex overflow-hidden relative">
            
            <!-- LEFT PANEL: Product Grid & Category Filter -->
            <div :class="activeMobileTab === 'catalog' ? 'flex' : 'hidden lg:flex'"
                 class="flex-1 flex-col bg-slate-100 p-3 sm:p-4 overflow-hidden w-full lg:w-auto relative">
                
                <!-- Category Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2.5 sm:pb-3 shrink-0 no-scrollbar">
                    <button type="button" @click="selectedCategory = null"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer"
                            :class="selectedCategory === null ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200'">
                        Semua Kategori
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" @click="selectedCategory = {{ $cat->id }}"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer"
                                :class="selectedCategory === {{ $cat->id }} ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200'">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Products Grid -->
                <div class="flex-1 overflow-y-auto pr-0.5 sm:pr-1">
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3.5 pb-24 lg:pb-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addToCart(product)"
                                 class="bg-white rounded-2xl border border-slate-200/80 p-2.5 sm:p-3 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition cursor-pointer group active:scale-[0.98]">
                                <div class="flex gap-2 sm:gap-2.5">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden shrink-0 flex items-center justify-center">
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between text-[9px] sm:text-[10px] text-slate-400 mb-0.5">
                                            <span x-text="product.sku" class="font-mono truncate mr-1"></span>
                                            <span x-text="product.stock_formatted" 
                                                  :class="product.current_stock <= 0 ? 'text-red-500 font-bold' : 'text-slate-500 font-medium'"
                                                  class="shrink-0 text-[9px]"></span>
                                        </div>
                                        <h3 x-text="product.name" class="font-bold text-[11px] sm:text-xs text-slate-800 line-clamp-2 leading-tight group-hover:text-emerald-700 transition"></h3>
                                    </div>
                                </div>
                                <div class="mt-2 pt-1.5 sm:mt-2.5 sm:pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] sm:text-xs font-extrabold text-emerald-600 truncate" 
                                          x-text="formatRupiah(priceType === 'wholesale' ? product.wholesale_price : product.selling_price)"></span>
                                    <span class="p-1 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition shrink-0 ml-1">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="filteredProducts.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-400" x-cloak>
                        <svg class="w-12 h-12 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-xs font-medium">Tidak ada produk yang cocok dengan pencarian.</p>
                    </div>
                </div>

                <!-- Mobile Floating Bottom Cart Bar (Catalog View) -->
                <div x-show="cart.length > 0 && activeMobileTab === 'catalog'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="translate-y-full opacity-0"
                     x-transition:enter-end="translate-y-0 opacity-100"
                     class="lg:hidden absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 shadow-2xl flex items-center justify-between gap-2.5 z-20">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="relative w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="absolute -top-1 -right-1 bg-emerald-600 text-white text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center shadow-xs" x-text="cartTotalItems"></span>
                        </div>
                        <div class="truncate">
                            <div class="text-[10px] text-slate-500 font-semibold" x-text="cartTotalItems + ' Barang Dipilih'"></div>
                            <div class="text-xs sm:text-sm font-black text-emerald-600 truncate" x-text="formatRupiah(grandTotal)"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" @click="activeMobileTab = 'cart'" 
                                class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1 transition">
                            <span>Keranjang</span>
                            <span class="text-emerald-400 font-extrabold">&rarr;</span>
                        </button>
                        <button type="button" @click="openPaymentModal()" 
                                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center gap-1 transition">
                            <span>Bayar</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: Active Cart Panel -->
            <div :class="activeMobileTab === 'cart' ? 'flex' : 'hidden lg:flex'"
                 class="w-full lg:w-96 xl:w-[420px] bg-white border-l border-slate-200 flex-col shrink-0 shadow-lg z-10 h-full overflow-hidden">
                
                <!-- Mobile Back To Catalog Header (Visible on mobile only) -->
                <div class="lg:hidden p-2.5 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                    <button type="button" @click="activeMobileTab = 'catalog'" class="px-3 py-1.5 rounded-xl bg-white border border-emerald-200 text-xs font-bold text-emerald-800 hover:bg-emerald-100 flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>&larr; Tambah Barang Lain</span>
                    </button>
                    <span class="text-xs font-bold text-emerald-900" x-text="cartTotalItems + ' Item'"></span>
                </div>

                <!-- Customer Selection Bar -->
                <div class="p-3 border-b border-slate-200 bg-slate-50 flex items-center gap-2">
                    <select x-model="selectedCustomerId" class="flex-1 bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="">Customer Umum (Walk-in)</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ strtoupper($c->category) }})</option>
                        @endforeach
                    </select>
                    <button type="button" @click="clearCart()" class="p-1.5 text-slate-400 hover:text-red-500 rounded-lg hover:bg-slate-200 transition" title="Kosongkan Keranjang">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <!-- Cart Items List (Multi-Unit Selection Enabled) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    <template x-for="(item, index) in cart" :key="item.cart_id || (item.product_id + '-' + index)">
                        <div class="p-2.5 sm:p-3 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-slate-300 transition flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 x-text="item.product_name" class="font-bold text-xs text-slate-800 leading-tight"></h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="item.sku"></span>
                                        <span class="text-[10px] text-emerald-600 font-semibold" x-text="'@ ' + formatRupiah(item.unit_price) + '/' + getItemUnitCode(item)"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeFromCart(index)" class="text-slate-400 hover:text-red-500 transition cursor-pointer p-1 -mr-1" title="Hapus dari keranjang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-200/60">
                                <!-- Unit Dropdown for Multi-Unit POS -->
                                <select x-model.number="item.unit_id" 
                                        @change="onItemUnitChange(item)"
                                        class="bg-white border border-slate-200 rounded-lg px-2 py-1 text-[11px] font-bold text-emerald-800 focus:outline-none focus:border-emerald-500 cursor-pointer max-w-[130px] truncate">
                                    <template x-for="u in item.available_units" :key="u.unit_id">
                                        <option :value="u.unit_id" 
                                                :selected="Number(u.unit_id) === Number(item.unit_id)"
                                                x-text="u.unit_code + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + ' ' + (item.base_unit_code || 'PCS') + ')' : '')">
                                        </option>
                                    </template>
                                </select>

                                <!-- Quantity Controls (+ / -) -->
                                <div class="flex items-center border border-slate-200 bg-white rounded-lg overflow-hidden shrink-0">
                                    <button type="button" @click="decreaseQty(item)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 font-bold text-xs active:bg-slate-200">-</button>
                                    <input type="number" step="any" min="0.01" x-model.number="item.quantity" @input="recalcItem(item)" 
                                           class="w-10 sm:w-12 text-center text-xs font-bold text-slate-800 focus:outline-none py-1">
                                    <button type="button" @click="increaseQty(item)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 font-bold text-xs active:bg-slate-200">+</button>
                                </div>

                                <!-- Item Subtotal -->
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-extrabold text-slate-900 block" x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-400 p-6 text-center" x-cloak>
                        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-slate-600">Keranjang Belanja Masih Kosong</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 max-w-[200px]">Pilih produk dari katalog atau scan barcode untuk menambah belanjaan.</p>
                        <button type="button" @click="activeMobileTab = 'catalog'" class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-xs hover:bg-emerald-700 lg:hidden">
                            &larr; Buka Katalog Produk
                        </button>
                    </div>
                </div>

                <!-- Cart Summary & Checkout Button -->
                <div class="p-3 sm:p-4 border-t border-slate-200 bg-slate-50 shrink-0 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal Barang:</span>
                        <span class="font-bold text-slate-800" x-text="formatRupiah(cartSubtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Diskon Transaksi (Rp):</span>
                        <input type="number" min="0" x-model.number="transactionDiscount" @input="recalcTotals()" 
                               class="w-24 bg-white border border-slate-200 rounded-lg px-2 py-0.5 text-right font-medium text-xs focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-slate-900">
                        <span class="text-sm font-bold">TOTAL BAYAR:</span>
                        <span class="text-lg font-black text-emerald-600" x-text="formatRupiah(grandTotal)"></span>
                    </div>

                    <button type="button" 
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <span>Lanjut ke Pembayaran &rarr;</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- MODAL PEMBAYARAN -->
    <div x-show="showPaymentModal" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50" 
         x-cloak>
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-slate-200 max-h-[92vh] flex flex-col"
             @click.outside="showPaymentModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Pembayaran Kasir</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Selesaikan transaksi kasir</p>
                </div>
                <button type="button" @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto py-3 sm:py-4 space-y-3.5 text-xs">
                
                <!-- Tagihan -->
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800">Total Tagihan:</span>
                    <span class="text-lg sm:text-xl font-black text-emerald-700" x-text="formatRupiah(grandTotal)"></span>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Cara Pembayaran:</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" @click="setPaymentMethod('cash')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            Tunai (Cash)
                        </button>
                        <button type="button" @click="setPaymentMethod('transfer')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'transfer' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            Transfer Bank
                        </button>
                        <button type="button" @click="setPaymentMethod('qris')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'qris' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            QRIS
                        </button>
                        <button type="button" @click="setPaymentMethod('debit')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'debit' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            Debit Card
                        </button>
                        <button type="button" @click="setPaymentMethod('dp')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'dp' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            Uang Muka (DP)
                        </button>
                        <button type="button" @click="setPaymentMethod('tempo')"
                                class="py-2 px-2.5 rounded-xl border text-center font-bold text-xs transition"
                                :class="paymentMethod === 'tempo' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            Tempo (Piutang)
                        </button>
                    </div>
                </div>

                <!-- Input Uang Diterima -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Uang Diterima (Rp):</label>
                    <input type="number" x-model.number="paidAmount" @input="recalcChange()"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 sm:py-2.5 text-base font-black text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white">
                </div>

                <!-- Quick Cash Buttons for Cashier -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar" x-show="paymentMethod === 'cash'">
                    <button type="button" @click="paidAmount = grandTotal; recalcChange()" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] whitespace-nowrap">Uang Pas</button>
                    <button type="button" @click="paidAmount = 20000; recalcChange()" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] whitespace-nowrap">20.000</button>
                    <button type="button" @click="paidAmount = 50000; recalcChange()" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] whitespace-nowrap">50.000</button>
                    <button type="button" @click="paidAmount = 100000; recalcChange()" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] whitespace-nowrap">100.000</button>
                    <button type="button" @click="paidAmount = 200000; recalcChange()" 
                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] whitespace-nowrap">200.000</button>
                </div>

                <!-- Kembalian / Sisa Piutang Display -->
                <div class="p-3 rounded-xl border border-slate-200 flex items-center justify-between"
                     :class="changeAmount >= 0 ? 'bg-slate-50' : 'bg-amber-50 border-amber-200'">
                    <span class="font-semibold text-slate-600" x-text="changeAmount >= 0 ? 'Kembalian:' : 'Sisa Piutang (Kredit):'"></span>
                    <span class="text-sm sm:text-base font-extrabold" 
                          :class="changeAmount >= 0 ? 'text-slate-800' : 'text-amber-700'"
                          x-text="formatRupiah(Math.abs(changeAmount))"></span>
                </div>

                <!-- Jatuh Tempo jika DP / Tempo -->
                <div x-show="paymentMethod === 'dp' || paymentMethod === 'tempo' || changeAmount < 0" x-cloak>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Jatuh Tempo Piutang:</label>
                    <input type="date" x-model="dueDate" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>

            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="showPaymentModal = false" class="px-3.5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold text-xs">
                    Batal
                </button>
                <button type="button" 
                        @click="processCheckout()" 
                        :disabled="isProcessing"
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                    <span x-show="!isProcessing">Konfirmasi & Cetak Struk</span>
                    <span x-show="isProcessing">Memproses...</span>
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL STRUK SUKSES & CETAK -->
    <div x-show="showReceiptModal" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50" 
         x-cloak>
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-slate-200 flex flex-col text-center"
             @click.outside="showReceiptModal = false">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">Transaksi Berhasil!</h3>
            <p class="text-xs text-slate-500 mb-4 font-mono" x-text="completedInvoiceNumber"></p>

            <div class="space-y-2">
                <a :href="receiptUrl" target="_blank" 
                   class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Buka / Cetak Struk Kasir
                </a>
                <button type="button" @click="resetPosAfterSuccess()" 
                        class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Alpine POS State Logic -->
    <script>
        function posApp() {
            return {
                allProducts: {!! json_encode($products) !!},
                searchQuery: '',
                selectedCategory: null,
                priceType: 'retail', // 'retail' or 'wholesale'
                selectedCustomerId: '',
                activeMobileTab: 'catalog', // 'catalog' or 'cart'
                addedToastText: '',
                showToast: false,
                toastTimeout: null,
                
                // Cart state
                cart: [],
                transactionDiscount: 0,
                taxPercentage: {{ $taxPercentage }},
                
                // Payment state
                showPaymentModal: false,
                paymentMethod: 'cash',
                paidAmount: 0,
                changeAmount: 0,
                dueDate: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
                isProcessing: false,

                // Success Receipt Modal
                showReceiptModal: false,
                completedInvoiceNumber: '',
                receiptUrl: '',

                initPos() {
                    // Only autofocus search on desktop / large screen to avoid auto-opening soft keyboard on mobile
                    if (window.innerWidth >= 1024 && this.$refs.barcodeSearchInput) {
                        this.$refs.barcodeSearchInput.focus();
                    }
                },

                get cartTotalItems() {
                    return this.cart.reduce((sum, i) => sum + Number(i.quantity || 0), 0);
                },

                get filteredProducts() {
                    return this.allProducts.filter(p => {
                        const matchCat = this.selectedCategory === null || p.category_id === this.selectedCategory;
                        const q = this.searchQuery.toLowerCase();
                        const matchSearch = !q || p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q) || (p.barcode && p.barcode.toLowerCase().includes(q));
                        return matchCat && matchSearch;
                    });
                },

                setPriceType(type) {
                    this.priceType = type;
                    // Recalculate price in cart
                    this.cart.forEach(item => {
                        this.recalcItemPricing(item);
                    });
                },

                handleBarcodeOrSearch() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) return;

                    // Exact match on barcode or SKU
                    const exact = this.allProducts.find(p => (p.barcode && p.barcode.toLowerCase() === q) || p.sku.toLowerCase() === q);
                    if (exact) {
                        this.addToCart(exact);
                        this.searchQuery = '';
                        return;
                    }

                    // Single match on search
                    if (this.filteredProducts.length === 1) {
                        this.addToCart(this.filteredProducts[0]);
                        this.searchQuery = '';
                    }
                },

                addToCart(product) {
                    const baseUnitObj = product.units.find(u => u.is_base) || product.units[0];
                    const existingIndex = this.cart.findIndex(i => i.product_id === product.id && Number(i.unit_id) === Number(baseUnitObj.unit_id));
                    
                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity += 1;
                        this.recalcItem(this.cart[existingIndex]);
                    } else {
                        const unitPrice = this.priceType === 'wholesale' ? (baseUnitObj.wholesale_price || baseUnitObj.selling_price) : baseUnitObj.selling_price;

                        const newItem = {
                            cart_id: 'c_' + product.id + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                            product_id: product.id,
                            sku: product.sku,
                            product_name: product.name,
                            base_unit_id: product.base_unit_id,
                            base_unit_code: product.base_unit_code,
                            base_selling_price: Number(product.selling_price) || 0,
                            base_wholesale_price: Number(product.wholesale_price || product.selling_price) || 0,
                            unit_id: baseUnitObj.unit_id,
                            available_units: product.units,
                            quantity: 1,
                            unit_price: Number(unitPrice) || 0,
                            discount_amount: 0,
                            subtotal: Number(unitPrice) || 0,
                        };
                        this.cart.push(newItem);
                    }

                    // Tactile notification
                    this.addedToastText = '+ ' + product.name;
                    this.showToast = true;
                    clearTimeout(this.toastTimeout);
                    this.toastTimeout = setTimeout(() => { this.showToast = false; }, 1200);
                },

                onItemUnitChange(item) {
                    const unitObj = item.available_units.find(u => Number(u.unit_id) === Number(item.unit_id));
                    if (unitObj) {
                        let price = this.priceType === 'wholesale' ? Number(unitObj.wholesale_price) : Number(unitObj.selling_price);
                        const factor = Number(unitObj.conversion_factor) || 1;
                        const basePrice = this.priceType === 'wholesale' 
                            ? (Number(item.base_wholesale_price) || Number(item.base_selling_price) || 0) 
                            : (Number(item.base_selling_price) || 0);

                        // If package price is <= 0 or mistakenly set <= base price when factor > 1
                        if (!price || price <= 0 || (factor > 1 && price <= basePrice)) {
                            price = Math.round(basePrice * factor);
                        }
                        item.unit_price = price;
                        this.recalcItem(item);
                    }
                },

                recalcItemPricing(item) {
                    const unitObj = item.available_units.find(u => Number(u.unit_id) === Number(item.unit_id));
                    if (unitObj) {
                        let price = this.priceType === 'wholesale' ? Number(unitObj.wholesale_price) : Number(unitObj.selling_price);
                        const factor = Number(unitObj.conversion_factor) || 1;
                        const basePrice = this.priceType === 'wholesale' 
                            ? (Number(item.base_wholesale_price) || Number(item.base_selling_price) || 0) 
                            : (Number(item.base_selling_price) || 0);

                        if (!price || price <= 0 || (factor > 1 && price <= basePrice)) {
                            price = Math.round(basePrice * factor);
                        }
                        item.unit_price = price;
                        this.recalcItem(item);
                    }
                },

                getItemUnitCode(item) {
                    const unitObj = item.available_units.find(u => Number(u.unit_id) === Number(item.unit_id));
                    return unitObj ? unitObj.unit_code : (item.base_unit_code || 'PCS');
                },

                increaseQty(item) {
                    item.quantity = Math.round((item.quantity + 1) * 100) / 100;
                    this.recalcItem(item);
                },

                decreaseQty(item) {
                    if (item.quantity > 1) {
                        item.quantity = Math.round((item.quantity - 1) * 100) / 100;
                        this.recalcItem(item);
                    }
                },

                recalcItem(item) {
                    item.subtotal = Math.max(0, Math.round((item.quantity * item.unit_price - item.discount_amount) * 100) / 100);
                },

                recalcTotals() {
                    this.recalcChange();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    this.cart = [];
                    this.transactionDiscount = 0;
                },

                get cartSubtotal() {
                    return this.cart.reduce((sum, i) => sum + i.subtotal, 0);
                },

                get grandTotal() {
                    return Math.max(0, this.cartSubtotal - (this.transactionDiscount || 0));
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.paidAmount = this.grandTotal;
                    this.recalcChange();
                    this.showPaymentModal = true;
                },

                setPaymentMethod(m) {
                    this.paymentMethod = m;
                    if (m === 'tempo') {
                        this.paidAmount = 0;
                    } else if (m === 'dp') {
                        this.paidAmount = Math.round(this.grandTotal * 0.3); // 30% DP default
                    } else if (this.paidAmount === 0) {
                        this.paidAmount = this.grandTotal;
                    }
                    this.recalcChange();
                },

                recalcChange() {
                    this.changeAmount = Math.round((this.paidAmount - this.grandTotal) * 100) / 100;
                },

                async processCheckout() {
                    if (this.isProcessing) return;

                    if ((this.paymentMethod === 'tempo' || this.paymentMethod === 'dp' || this.paidAmount < this.grandTotal) && !this.selectedCustomerId) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Customer',
                            text: 'Customer wajib dipilih untuk transaksi Tempo / DP / Piutang.',
                            confirmButtonColor: '#10b981',
                        });
                        return;
                    }

                    this.isProcessing = true;

                    const payload = {
                        customer_id: this.selectedCustomerId || null,
                        price_type: this.priceType,
                        discount_amount: this.transactionDiscount || 0,
                        tax_amount: 0,
                        paid_amount: this.paidAmount,
                        payment_method: this.paymentMethod,
                        due_date: (this.paymentMethod === 'tempo' || this.paymentMethod === 'dp' || this.paidAmount < this.grandTotal) ? this.dueDate : null,
                        items: this.cart.map(i => ({
                            product_id: i.product_id,
                            unit_id: i.unit_id,
                            quantity: i.quantity,
                            unit_price: i.unit_price,
                            discount_amount: i.discount_amount,
                        })),
                    };

                    try {
                        const res = await fetch("{{ route('pos.checkout') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.showPaymentModal = false;
                            this.completedInvoiceNumber = data.sale.invoice_number;
                            this.receiptUrl = data.receipt_url;
                            this.showReceiptModal = true;
                        } else {
                            window.Swal.fire({
                                icon: 'error',
                                title: 'Gagal Transaksi',
                                text: data.message || 'Terjadi kesalahan sistem.',
                                confirmButtonColor: '#ef4444',
                            });
                        }
                    } catch (err) {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Error Jaringan',
                            text: err.message,
                            confirmButtonColor: '#ef4444',
                        });
                    } finally {
                        this.isProcessing = false;
                    }
                },

                resetPosAfterSuccess() {
                    this.showReceiptModal = false;
                    this.clearCart();
                    window.location.reload(); // Refresh to update stocks
                },

                formatRupiah(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
                }
            };
        }
    </script>
</body>
</html>
