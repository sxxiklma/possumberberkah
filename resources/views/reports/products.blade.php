@extends('layouts.app')

@section('title', 'Laporan Analisis Produk')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'fast' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Analisis Pergerakan & Profitabilitas Produk</h1>
            <p class="text-slate-500 text-sm mt-0.5">Klasifikasi performa produk: Fast Moving, Slow Moving, Dead Stock, serta Produk Paling Menguntungkan.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.products') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
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
                <a href="{{ route('reports.products') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tab navigation pills -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <button @click="activeTab = 'fast'"
                :class="activeTab === 'fast' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Fast Moving ({{ count($fastMoving) }})
        </button>
        <button @click="activeTab = 'profit'"
                :class="activeTab === 'profit' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Margin Tertinggi ({{ count($profitableProducts) }})
        </button>
        <button @click="activeTab = 'slow'"
                :class="activeTab === 'slow' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Slow Moving ({{ count($slowMoving) }})
        </button>
        <button @click="activeTab = 'dead'"
                :class="activeTab === 'dead' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            Dead Stock / 0 Terjual ({{ count($noMovement) }})
        </button>
    </div>

    <!-- Tab 1: Fast Moving -->
    <div x-show="activeTab === 'fast'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-emerald-900">Produk Fast Moving (Perputaran Cepat)</h3>
                <p class="text-xs text-emerald-700">Produk dengan volume penjualan tinggi dalam periode terpilih (≥ 20 unit).</p>
            </div>
            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-xs">{{ count($fastMoving) }} Produk</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-semibold">
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-right">Terjual (Dasar)</th>
                        <th class="py-3 px-4 text-right">Omset (Rp)</th>
                        <th class="py-3 px-4 text-right">HPP FIFO (Rp)</th>
                        <th class="py-3 px-4 text-right">Laba Kotor (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($fastMoving as $item)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-mono text-xs font-semibold text-slate-800">{{ $item['product']->sku }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $item['product']->name }}</td>
                        <td class="py-3 px-4 text-xs text-slate-600">{{ $item['product']->category?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-700">
                            {{ (float)$item['sold_qty'] }} {{ $item['product']->baseUnit?->symbol }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-900">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($item['cogs'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600">Rp {{ number_format($item['profit'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">Tidak ada produk fast moving pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Most Profitable -->
    <div x-show="activeTab === 'profit'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-indigo-50/50 border-b border-indigo-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-indigo-900">Produk Margin Tertinggi (Kontributor Laba Terbesar)</h3>
                <p class="text-xs text-indigo-700">Diurutkan berdasarkan perolehan laba kotor tertinggi (Omset - HPP FIFO).</p>
            </div>
            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 font-bold rounded-lg text-xs">{{ count($profitableProducts) }} Produk</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-semibold">
                        <th class="py-3 px-4">Peringkat</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-right">Unit Terjual</th>
                        <th class="py-3 px-4 text-right">Omset (Rp)</th>
                        <th class="py-3 px-4 text-right">HPP FIFO (Rp)</th>
                        <th class="py-3 px-4 text-right">Kontribusi Laba (Rp)</th>
                        <th class="py-3 px-4 text-right">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profitableProducts as $idx => $item)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-bold text-slate-400 text-center">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">
                            {{ $item['product']->name }}
                            <span class="block text-xs font-mono text-slate-400">{{ $item['product']->sku }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-slate-700">{{ (float)$item['sold_qty'] }} {{ $item['product']->baseUnit?->symbol }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-900">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($item['cogs'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-indigo-600">Rp {{ number_format($item['profit'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-800">
                            {{ $item['revenue'] > 0 ? number_format(($item['profit'] / $item['revenue']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">Belum ada data profitabilitas produk pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Slow Moving -->
    <div x-show="activeTab === 'slow'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-amber-50/50 border-b border-amber-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-amber-900">Produk Slow Moving (Perputaran Lambat)</h3>
                <p class="text-xs text-amber-700">Produk dengan kuantitas penjualan rendah (1 s/d 5 unit) selama periode evaluasi.</p>
            </div>
            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs">{{ count($slowMoving) }} Produk</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-semibold">
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-right">Stok Gudang</th>
                        <th class="py-3 px-4 text-right">Terjual</th>
                        <th class="py-3 px-4 text-right">Total Omset (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($slowMoving as $item)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-mono text-xs font-semibold text-slate-800">{{ $item['product']->sku }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $item['product']->name }}</td>
                        <td class="py-3 px-4 text-right font-medium text-slate-700">{{ (float)$item['product']->current_stock }} {{ $item['product']->baseUnit?->symbol }}</td>
                        <td class="py-3 px-4 text-right font-bold text-amber-600">{{ (float)$item['sold_qty'] }} {{ $item['product']->baseUnit?->symbol }}</td>
                        <td class="py-3 px-4 text-right font-medium text-slate-900">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400">Tidak ada produk dalam kategori slow moving pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Dead Stock -->
    <div x-show="activeTab === 'dead'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-rose-50/50 border-b border-rose-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-rose-900">Dead Stock (0 Terjual)</h3>
                <p class="text-xs text-rose-700">Produk aktif yang tidak mengalami pergerakan penjualan sama sekali dalam periode ini.</p>
            </div>
            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-bold rounded-lg text-xs">{{ count($noMovement) }} Produk</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-semibold">
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-right">Stok Fisik Tersimpan</th>
                        <th class="py-3 px-4 text-right">Nilai Modal Tertimbun (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($noMovement as $item)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-mono text-xs font-semibold text-slate-800">{{ $item['product']->sku }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $item['product']->name }}</td>
                        <td class="py-3 px-4 text-xs text-slate-600">{{ $item['product']->category?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-right font-medium text-slate-800">{{ (float)$item['product']->current_stock }} {{ $item['product']->baseUnit?->symbol }}</td>
                        <td class="py-3 px-4 text-right font-bold text-rose-600">Rp {{ number_format($item['product']->inventory_value, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400">Semua produk mengalami transaksi penjualan! Bagus sekali.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

