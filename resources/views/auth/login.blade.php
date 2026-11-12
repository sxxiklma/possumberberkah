<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TOKO SUMBER BERKAH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        [x-cloak] { display: none !important; }
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased selection:bg-emerald-500 selection:text-white text-slate-100 bg-slate-950" 
      x-data="{ 
          email: '{{ old('email') }}', 
          password: '', 
          showPassword: false,
          selectedRole: null,
          fillAccount(email, pass, roleName) {
              this.email = email;
              this.password = pass;
              this.selectedRole = roleName;
              if (window.showToast) {
                  window.showToast('info', 'Akun ' + roleName + ' terpilih');
              }
          }
      }">

    <div class="min-h-screen grid lg:grid-cols-12 relative overflow-hidden">

        <!-- ======================================================== -->
        <!-- LEFT COLUMN: Minimalist Brand & Identity Showcase        -->
        <!-- (Clean, uncluttered, elegant spacing)                     -->
        <!-- ======================================================== -->
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-6 flex-col justify-between p-12 xl:p-16 relative bg-slate-950 border-r border-slate-800/80 overflow-hidden">
            
            <!-- Subtle Ambient Backdrop -->
            <div class="absolute inset-0 bg-grid-pattern opacity-50 pointer-events-none"></div>
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Brand Top Header -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-500 via-emerald-400 to-teal-300 text-slate-950 font-black text-lg shadow-lg shadow-emerald-500/20 border border-emerald-300/30">
                        SB
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400 border-2 border-slate-950"></span>
                        </span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-base tracking-tight text-white">TOKO SUMBER BERKAH</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">v2.0</span>
                        </div>
                        <p class="text-xs text-slate-400 font-medium">Pusat Bahan Kue & Perlengkapan Roti</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/80 border border-slate-800 text-slate-300 text-xs backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium text-[11px] text-slate-300">Peterongan, Jombang</span>
                </div>
            </div>

            <!-- Minimalist Hero Content -->
            <div class="relative z-10 my-auto py-12 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Sistem POS & Inventori Terintegrasi
                </div>

                <h1 class="text-3xl xl:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Kelola Penjualan, Stok FIFO & Keuangan Toko dalam <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">Satu Sistem.</span>
                </h1>

                <p class="text-sm text-slate-400 mt-5 leading-relaxed font-normal">
                    Platform operasional kasir pintar, konversi multi-satuan, pelacakan persediaan batch FIFO, dan akuntansi toko yang cepat dan akurat.
                </p>

                <!-- Minimalist Location Pill -->
                <div class="mt-8 flex items-center gap-2 text-xs text-slate-400 bg-slate-900/60 border border-slate-800/80 rounded-xl px-4 py-3 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Peterongan, Jombang</span>
                </div>
            </div>

            <!-- Footer Left -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-500 pt-6 border-t border-slate-800/60">
                <p>&copy; {{ date('Y') }} TOKO SUMBER BERKAH. All rights reserved.</p>
                <div class="flex items-center gap-3">
                    <span>Sistem Terverifikasi</span>
                    <span>•</span>
                    <span>Jawa Timur</span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- RIGHT COLUMN: Modern Minimalist Auth Form                -->
        <!-- ======================================================== -->
        <div class="col-span-12 lg:col-span-6 xl:col-span-6 flex flex-col justify-between p-6 sm:p-10 lg:p-12 xl:p-16 bg-slate-900/90 relative z-10 backdrop-blur-xl">

            <!-- Mobile Brand Header (Visible only on mobile/tablet) -->
            <div class="lg:hidden flex items-center gap-3 mb-8 pb-4 border-b border-slate-800">
                <div class="flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 font-black text-lg shadow-md shadow-emerald-500/20">
                    SB
                </div>
                <div>
                    <h1 class="font-extrabold text-base text-white">TOKO SUMBER BERKAH</h1>
                    <p class="text-xs text-slate-400">Pusat Bahan Kue & Perlengkapan Roti Peterongan</p>
                </div>
            </div>

            <div class="w-full max-w-md mx-auto my-auto">
                
                <!-- Form Greeting -->
                <div class="mb-7">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Portal Akses Aman
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Selamat Datang 👋</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Masukkan kredensial akun Anda untuk membuka dashboard toko.
                    </p>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs flex items-start gap-3 animate-shake">
                        <div class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold block text-rose-200">Gagal Masuk</span>
                            <span class="text-rose-300/90 leading-relaxed">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Email / Username Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Email atau Username <span class="text-emerald-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input type="text" name="email" id="emailInput" x-model="email" required autofocus
                                   placeholder="nama@sumberberkah.com / username"
                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-white text-xs sm:text-sm placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Password Input with Toggle Visibility -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">
                                Kata Sandi (Password) <span class="text-emerald-400">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500">Min. 6 Karakter</span>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="passwordInput" x-model="password" required
                                   placeholder="••••••••"
                                   class="w-full bg-slate-950/80 border border-slate-800 rounded-xl pl-10 pr-11 py-3 text-white text-xs sm:text-sm placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200 font-medium">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition-colors focus:outline-none cursor-pointer"
                                    title="Tampilkan / Sembunyikan Password">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Options Row -->
                    <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                            <span class="hover:text-slate-300 transition-colors">Ingat sesi saya</span>
                        </label>
                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-500">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Koneksi Aman
                        </span>
                    </div>

                    <!-- Submit CTA Button -->
                    <button type="submit" 
                            class="w-full mt-3 py-3.5 px-5 rounded-xl bg-gradient-to-r from-emerald-500 via-emerald-400 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 active:scale-[0.99] transition-all duration-200 cursor-pointer flex items-center justify-center gap-2">
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <!-- ======================================================== -->
                <!-- MODERN DEMO ACCOUNT SELECTOR (Role Quick-Switch)         -->
                <!-- ======================================================== -->
                <div class="mt-8 pt-6 border-t border-slate-800">
                    <div class="flex items-center justify-between mb-3.5">
                        <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Akun Demo Cepat
                        </span>
                        <span class="text-[10px] text-slate-500">Klik untuk mengisi otomatis</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <!-- 1. Kasir POS (Highlighted Primary) -->
                        <button type="button" @click="fillAccount('kasir@sumberberkah.com', 'password', 'Kasir POS')"
                                :class="selectedRole === 'Kasir POS' ? 'border-emerald-500 bg-emerald-500/15 text-emerald-300 shadow-sm shadow-emerald-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-emerald-400/90">POS</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-emerald-300">Kasir Toko</div>
                            <div class="text-[10px] text-slate-500 truncate">kasir@...</div>
                        </button>

                        <!-- 2. Super Admin -->
                        <button type="button" @click="fillAccount('superadmin@sumberberkah.com', 'password', 'Super Admin')"
                                :class="selectedRole === 'Super Admin' ? 'border-purple-500 bg-purple-500/15 text-purple-300 shadow-sm shadow-purple-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-purple-400/90">Root</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-purple-300">Super Admin</div>
                            <div class="text-[10px] text-slate-500 truncate">superadmin@...</div>
                        </button>

                        <!-- 3. Admin Toko -->
                        <button type="button" @click="fillAccount('admin@sumberberkah.com', 'password', 'Admin Toko')"
                                :class="selectedRole === 'Admin Toko' ? 'border-indigo-500 bg-indigo-500/15 text-indigo-300 shadow-sm shadow-indigo-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-indigo-400/90">Admin</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-indigo-300">Admin Toko</div>
                            <div class="text-[10px] text-slate-500 truncate">admin@...</div>
                        </button>

                        <!-- 4. Staff Gudang -->
                        <button type="button" @click="fillAccount('gudang@sumberberkah.com', 'password', 'Staff Gudang')"
                                :class="selectedRole === 'Staff Gudang' ? 'border-amber-500 bg-amber-500/15 text-amber-300 shadow-sm shadow-amber-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-amber-400/90">Gudang</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-amber-300">Staff Gudang</div>
                            <div class="text-[10px] text-slate-500 truncate">gudang@...</div>
                        </button>

                        <!-- 5. Finance -->
                        <button type="button" @click="fillAccount('finance@sumberberkah.com', 'password', 'Finance')"
                                :class="selectedRole === 'Finance' ? 'border-blue-500 bg-blue-500/15 text-blue-300 shadow-sm shadow-blue-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-blue-400/90">Kas</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-blue-300">Keuangan</div>
                            <div class="text-[10px] text-slate-500 truncate">finance@...</div>
                        </button>

                        <!-- 6. Owner -->
                        <button type="button" @click="fillAccount('owner@sumberberkah.com', 'password', 'Owner')"
                                :class="selectedRole === 'Owner' ? 'border-rose-500 bg-rose-500/15 text-rose-300 shadow-sm shadow-rose-500/10' : 'border-slate-800 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-950'"
                                class="p-2.5 rounded-xl border text-left transition-all duration-200 group cursor-pointer">
                            <div class="flex items-center justify-between">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-rose-400/90">Owner</span>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-white group-hover:text-rose-300">Pemilik Toko</div>
                            <div class="text-[10px] text-slate-500 truncate">owner@...</div>
                        </button>
                    </div>

                    <div class="mt-3 flex items-center justify-center gap-1.5 text-[11px] text-slate-500">
                        <span>Kata Sandi Default:</span>
                        <code class="px-1.5 py-0.5 rounded bg-slate-950 border border-slate-800 text-emerald-400 font-mono text-[10px]">password</code>
                    </div>
                </div>
            </div>

            <!-- Mobile Footer (Visible on mobile/tablet) -->
            <div class="lg:hidden mt-8 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} TOKO SUMBER BERKAH • Peterongan, Jombang</p>
            </div>

        </div>

    </div>

</body>
</html>
