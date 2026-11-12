@extends('layouts.app', ['title' => 'Riwayat Transaksi Kasir', 'activeAccordion' => 'transactions'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Riwayat Penjualan Kasir</h1>
        <p class="text-xs text-slate-500 mt-1">Daftar transaksi penjualan kasir dan opsi pembatalan faktur (Void Reversal)</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('transactions.sales.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
            Lihat Halaman Penjualan
        </a>
        <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buka Kasir POS
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                <tr>
                    <th class="p-3.5">No Faktur</th>
                    <th class="p-3.5">Tanggal</th>
                    <th class="p-3.5">Customer</th>
                    <th class="p-3.5">Kasir</th>
                    <th class="p-3.5">Metode Bayar</th>
                    <th class="p-3.5">Total Bayar</th>
                    <th class="p-3.5">HPP FIFO</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/60 {{ $sale->status === 'void' ? 'bg-red-50/30' : '' }}">
                        <td class="p-3.5 font-bold text-slate-800 font-mono">{{ $sale->invoice_number }}</td>
                        <td class="p-3.5 text-slate-500">{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                        <td class="p-3.5 text-slate-700 font-medium">{{ $sale->customer?->name ?? 'Customer Umum' }}</td>
                        <td class="p-3.5 text-slate-600">{{ $sale->user?->name }}</td>
                        <td class="p-3.5 uppercase font-semibold text-[10px] text-slate-500">{{ $sale->payment_method }}</td>
                        <td class="p-3.5 font-bold text-emerald-600">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="p-3.5 text-slate-500">Rp {{ number_format($sale->total_cogs, 0, ',', '.') }}</td>
                        <td class="p-3.5">
                            @if($sale->status === 'void')
                                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">DIBATALKAN (VOID)</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase">
                                    {{ $sale->payment_status }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank" 
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Cetak Struk">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>

                                @if($sale->status !== 'void')
                                    @can('pos.void-sale')
                                        <button type="button" onclick="voidTransaction('{{ $sale->id }}', '{{ $sale->invoice_number }}')"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Batalkan Faktur (Void)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-400">Belum ada transaksi penjualan yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100">
        {{ $sales->links() }}
    </div>
</div>

<script>
    async function voidTransaction(saleId, invoiceNumber) {
        const { value: reason } = await window.Swal.fire({
            title: `Batalkan Faktur ${invoiceNumber}?`,
            text: "Stok produk akan otomatis dikembalikan ke batch gudang asal (FIFO Reversal).",
            input: 'text',
            inputLabel: 'Alasan Pembatalan Faktur:',
            inputPlaceholder: 'Contoh: Salah input pesanan / Barang rusak...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan Transaksi!',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan pembatalan wajib diisi!';
                }
            }
        });

        if (reason) {
            try {
                const res = await fetch(`/pos/void/${saleId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const data = await res.json();
                if (data.success) {
                    await window.Swal.fire('Berhasil', data.message, 'success');
                    window.location.reload();
                } else {
                    window.Swal.fire('Gagal', data.message || 'Gagal membatalkan faktur.', 'error');
                }
            } catch (err) {
                window.Swal.fire('Error', err.message, 'error');
            }
        }
    }
</script>
@endsection

