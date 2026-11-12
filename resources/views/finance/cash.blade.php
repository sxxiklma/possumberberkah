@extends('layouts.app')

@section('title', 'Buku Kas Toko')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, defaultType: 'cash_in' }">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Buku Kas (Arus Kas)</h1>
            <p class="text-slate-500 text-sm mt-0.5">Monitoring pergerakan kas masuk, kas keluar, dan mutasi kas operasional toko.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="defaultType = 'cash_in'; showModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Kas Masuk
            </button>
            <button @click="defaultType = 'cash_out'; showModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Kas Keluar
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kas Masuk (Inflow)</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi penerimaan kas</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kas Keluar (Outflow)</p>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalCashOut, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi pengeluaran kas</p>
            </div>
            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Saldo Akhir Kas</p>
                <p class="text-2xl font-black {{ $balance >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mt-1">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Kas Masuk - Kas Keluar</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('finance.cash') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Arus Kas</label>
                <select name="type" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Arus Kas</option>
                    <option value="cash_in" {{ $type === 'cash_in' ? 'selected' : '' }}>Kas Masuk (In)</option>
                    <option value="cash_out" {{ $type === 'cash_out' ? 'selected' : '' }}>Kas Keluar (Out)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('finance.cash') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Histori Transaksi Kas</h2>
            <span class="text-xs text-slate-500">Total {{ $transactions->total() }} catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No Transaksi</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">{{ $trx->transaction_number }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') : '-' }}</td>
                        <td class="py-3.5 px-4">
                            @if($trx->type === 'cash_in')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                    Kas Masuk
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    Kas Keluar
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">{{ $trx->category }}</td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">{{ $trx->description ?? '-' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 uppercase">
                                {{ $trx->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold {{ $trx->type === 'cash_in' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $trx->type === 'cash_in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $trx->user?->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Belum ada riwayat transaksi arus kas pada rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Catat Kas Masuk / Keluar -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100" @click.away="showModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900" x-text="defaultType === 'cash_in' ? 'Tambah Catatan Kas Masuk' : 'Tambah Catatan Kas Keluar'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('finance.cash.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Transaksi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition text-sm font-semibold"
                               :class="defaultType === 'cash_in' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" name="type" value="cash_in" x-model="defaultType" class="text-emerald-600 focus:ring-emerald-500">
                            Kas Masuk (+)
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition text-sm font-semibold"
                               :class="defaultType === 'cash_out' ? 'border-rose-500 bg-rose-50/50 text-rose-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" name="type" value="cash_out" x-model="defaultType" class="text-rose-600 focus:ring-rose-500">
                            Kas Keluar (-)
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" required placeholder="cth: Setoran Modal / Listrik & Air / Operasional"
                           class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="1" name="amount" required placeholder="0"
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan / Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Catatan tambahan..."
                              class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

