@extends('layouts.app')

@section('title', 'Beban Operasional Toko')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Beban Operasional</h1>
            <p class="text-slate-500 text-sm mt-0.5">Catat seluruh pengeluaran operasional toko seperti listrik, sewa, gaji, perlengkapan, dan biaya lain.</p>
        </div>
        <div>
            <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Pengeluaran
            </button>
        </div>
    </div>

    <!-- Summary KPI Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between md:col-span-1">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Beban Periode Terpilih</p>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            </div>
            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl shadow-sm text-white md:col-span-2 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-emerald-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Integrasi Otomatis Buku Kas & Laba Rugi
                </h3>
                <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                    Setiap pengeluaran operasional yang dicatat di sini secara otomatis terintegrasi langsung dengan <strong>Buku Kas Keluar</strong> dan dihitung sebagai faktor pengurang dalam <strong>Laporan Laba Rugi Bersih (Net Profit)</strong> toko.
                </p>
            </div>
            <div class="text-xs text-slate-400 mt-2">
                Rumus: Laba Bersih = Laba Kotor (Penjualan - HPP FIFO) - Beban Operasional.
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('finance.expenses') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Filter Periode
                </button>
                <a href="{{ route('finance.expenses') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Daftar Pengeluaran Operasional</h2>
            <span class="text-xs text-slate-500">Total {{ $expenses->total() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No Beban</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Kategori Beban</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">{{ $item->expense_number }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $item->expense_date ? \Carbon\Carbon::parse($item->expense_date)->format('d/m/Y') : '-' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-sm truncate">{{ $item->notes ?? '-' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                                {{ $item->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-600">
                            Rp {{ number_format($item->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $item->user?->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Belum ada catatan beban operasional pada rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Catat Beban Operasional -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100" @click.away="showModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Catat Beban Operasional Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('finance.expenses.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Beban <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Kategori Beban --</option>
                        <option value="Listrik & Air">Listrik, Air & Utilitas</option>
                        <option value="Internet & Komunikasi">Internet & Komunikasi</option>
                        <option value="Gaji & Upah Karyawan">Gaji & Upah Karyawan</option>
                        <option value="Sewa Tempat / Bangunan">Sewa Tempat / Bangunan</option>
                        <option value="Perlengkapan Toko / ATK">Perlengkapan Toko / ATK / Kertas Struk</option>
                        <option value="Transportasi & Logistik">Transportasi & Pengiriman</option>
                        <option value="Pemeliharaan & Kebersihan">Pemeliharaan & Kebersihan</option>
                        <option value="Pajak & Legalitas">Pajak & Legalitas</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Biaya (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="1" name="amount" required placeholder="0"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Beban <span class="text-rose-500">*</span></label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="cash">Kas Tunai (Petty Cash)</option>
                        <option value="transfer">Transfer Bank Toko</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan / Rincian</label>
                    <textarea name="notes" rows="2" placeholder="cth: Tagihan PLN bulan ini, kwitansi no 123..."
                              class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

