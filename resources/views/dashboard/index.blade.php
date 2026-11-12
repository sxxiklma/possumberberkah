@extends('layouts.app', ['title' => 'Dashboard Utama', 'activeAccordion' => ''])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Ringkasan Operasional Toko</h1>
        <p class="text-xs text-slate-500 mt-1">Pantau performa penjualan, persediaan gudang, dan keuangan secara realtime.</p>
    </div>

    <!-- Date Period Filters -->
    <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs">
        <a href="{{ route('dashboard', ['period' => 'today']) }}" 
           class="px-3 py-1.5 rounded-lg font-medium transition {{ $period === 'today' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            Hari Ini
        </a>
        <a href="{{ route('dashboard', ['period' => 'this_week']) }}" 
           class="px-3 py-1.5 rounded-lg font-medium transition {{ $period === 'this_week' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            Minggu Ini
        </a>
        <a href="{{ route('dashboard', ['period' => 'this_month']) }}" 
           class="px-3 py-1.5 rounded-lg font-medium transition {{ $period === 'this_month' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            Bulan Ini
        </a>
        <a href="{{ route('dashboard', ['period' => 'this_year']) }}" 
           class="px-3 py-1.5 rounded-lg font-medium transition {{ $period === 'this_year' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
            Tahun Ini
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid (11 Metrics) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. Penjualan Periode -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Penjualan</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-lg sm:text-xl font-bold text-slate-800">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">Penjualan bersih terbayar</p>
            </div>
        </div>

        <!-- 2. Laba Kotor (FIFO Actual) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Keuntungan Kotor (FIFO)</span>
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-lg sm:text-xl font-bold text-teal-700">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">Penjualan - HPP FIFO batch</p>
            </div>
        </div>

        <!-- 3. Pembelian / Barang Masuk -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Pembelian (PO)</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-lg sm:text-xl font-bold text-slate-800">Rp {{ number_format($totalPurchasesAmount, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">Barang masuk diterima</p>
            </div>
        </div>

        <!-- 4. Nilai Persediaan Toko -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Nilai Persediaan</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-lg sm:text-xl font-bold text-purple-700">Rp {{ number_format($totalInventoryValuation, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">Total aset barang di gudang</p>
            </div>
        </div>

    </div>

    <!-- Secondary Row Metrics: Piutang, Hutang, Stok Menipis, Expired -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Piutang Customer -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-semibold">Piutang Customer</span>
                <a href="{{ route('finance.receivables') }}" class="text-emerald-600 hover:underline text-[11px]">Buku Piutang &rarr;</a>
            </div>
            <div class="mt-2">
                <span class="text-base sm:text-lg font-bold text-amber-600">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-400">Total tagihan belum lunas</p>
            </div>
        </div>

        <!-- Hutang Supplier -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-semibold">Hutang Supplier</span>
                <a href="{{ route('finance.payables') }}" class="text-emerald-600 hover:underline text-[11px]">Buku Hutang &rarr;</a>
            </div>
            <div class="mt-2">
                <span class="text-base sm:text-lg font-bold text-rose-600">Rp {{ number_format($totalPayables, 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-400">Kewajiban bayar supplier</p>
            </div>
        </div>

        <!-- Stok Menipis Alert -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-semibold">Stok Menipis</span>
                <span class="px-2 py-0.5 rounded-full {{ $lowStockProducts->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} text-[10px] font-bold">
                    {{ $lowStockProducts->count() }} Produk
                </span>
            </div>
            <div class="mt-2">
                <span class="text-base sm:text-lg font-bold text-slate-800">{{ $lowStockProducts->count() }} Item</span>
                <p class="text-[10px] text-slate-400">Perlu reorder pembelian</p>
            </div>
        </div>

        <!-- Hampir Kedaluwarsa -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-semibold">Hampir Expired</span>
                <span class="px-2 py-0.5 rounded-full {{ $expiringBatches->count() > 0 ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600' }} text-[10px] font-bold">
                    {{ $expiringBatches->count() }} Batch
                </span>
            </div>
            <div class="mt-2">
                <span class="text-base sm:text-lg font-bold text-slate-800">{{ $expiringBatches->count() }} Batch</span>
                <p class="text-[10px] text-slate-400">Jatuh tempo < 30 hari</p>
            </div>
        </div>

    </div>

    <!-- Charts Section (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sales & Purchase Trend (Line/Bar Chart) -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Tren Penjualan & Pembelian (7 Hari Terakhir)</h2>
                    <p class="text-[11px] text-slate-400">Data realtime nominal transaksi yang terjadi di toko</p>
                </div>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Breakdown (Doughnut Chart) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 mb-1">Metode Pembayaran</h2>
                <p class="text-[11px] text-slate-400 mb-4">Distribusi transaksi kasir berdasarkan cara bayar</p>
                <div class="h-52 relative flex items-center justify-center">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs space-y-1">
                @foreach($paymentMethodsData as $pm)
                    <div class="flex items-center justify-between text-slate-600">
                        <span class="capitalize font-medium">{{ $pm->payment_method }} ({{ $pm->count }})</span>
                        <span class="font-bold">Rp {{ number_format($pm->total, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom Lists: Transaksi Terbaru & Produk Terlaris -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Sales Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Transaksi Penjualan Terbaru</h2>
                    <p class="text-[11px] text-slate-400">Faktur kasir yang baru saja diselesaikan</p>
                </div>
                <a href="{{ route('pos.history') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-3">Faktur</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Metode</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3 font-semibold text-slate-800">{{ $sale->invoice_number }}</td>
                                <td class="p-3 text-slate-600">{{ $sale->customer?->name ?? 'Customer Umum' }}</td>
                                <td class="p-3 font-bold text-emerald-600">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td class="p-3 uppercase text-[10px] font-semibold text-slate-500">{{ $sale->payment_method }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sale->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ strtoupper($sale->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-slate-400">Belum ada transaksi penjualan terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Produk Paling Laris (Fast Moving)</h2>
                    <p class="text-[11px] text-slate-400">Berdasarkan volume kuantitas terjual</p>
                </div>
                <a href="{{ route('reports.products') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Analisis Detail &rarr;</a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($topProducts as $idx => $item)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/60 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div>
                                <h3 class="font-bold text-xs text-slate-800 leading-tight">{{ $item->product?->name }}</h3>
                                <span class="text-[10px] text-slate-400">SKU: {{ $item->product?->sku }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold text-slate-800">{{ (float)$item->total_qty }} {{ $item->product?->baseUnit?->code }}</span>
                            <p class="text-[10px] text-emerald-600 font-semibold">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-xs text-slate-400 py-6">Belum ada data penjualan tercatat.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Sales & Purchases Trend Chart
        const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
        if (trendCtx) {
            new window.Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartDates) !!},
                    datasets: [
                        {
                            label: 'Penjualan (Rp)',
                            data: {!! json_encode($salesChartData) !!},
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                        },
                        {
                            label: 'Pembelian (Rp)',
                            data: {!! json_encode($purchasesChartData) !!},
                            backgroundColor: '#93c5fd',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val/1000000) + ' Jt' : (val/1000) + ' Rb'),
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // 2. Payment Methods Doughnut Chart
        const pmCtx = document.getElementById('paymentMethodsChart')?.getContext('2d');
        if (pmCtx) {
            const pmData = {!! json_encode($paymentMethodsData) !!};
            new window.Chart(pmCtx, {
                type: 'doughnut',
                data: {
                    labels: pmData.map(d => d.payment_method.toUpperCase()),
                    datasets: [{
                        data: pmData.map(d => d.total),
                        backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: Rp ${new Intl.NumberFormat('id-ID').format(ctx.raw)}`
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

