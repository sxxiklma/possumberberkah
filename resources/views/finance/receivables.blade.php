@extends('layouts.app', ['title' => 'Buku Piutang Customer', 'activeAccordion' => 'finance'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Buku Piutang Pelanggan (Customer Receivables)</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola penjualan tempo, uang muka (DP), dan pencatatan cicilan pelunasan</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl text-right">
        <span class="text-[10px] text-amber-800 font-semibold uppercase tracking-wider block">Total Piutang Berjalan:</span>
        <span class="text-base font-black text-amber-700">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('finance.receivables') }}" class="flex gap-3 text-xs">
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Status Piutang</option>
                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Dibayar Sebagian / DP</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Lunas</option>
            </select>
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
            <a href="{{ route('finance.receivables') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Customer</th>
                        <th class="p-3.5">No Faktur</th>
                        <th class="p-3.5">Tanggal Faktur</th>
                        <th class="p-3.5">Jatuh Tempo</th>
                        <th class="p-3.5 text-right">Total Tagihan</th>
                        <th class="p-3.5 text-right">Sudah Dibayar</th>
                        <th class="p-3.5 text-right font-extrabold text-amber-700">Sisa Piutang</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receivables as $rec)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-bold text-slate-800">
                                {{ $rec->customer?->name }}
                                <div class="text-[10px] text-slate-400 font-normal">Telp: {{ $rec->customer?->phone ?? '-' }}</div>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-700">{{ $rec->sale?->invoice_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $rec->sale?->sale_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-semibold {{ $rec->isOverdue() ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $rec->due_date?->format('d/m/Y') ?? '-' }}
                                @if($rec->isOverdue())
                                    <span class="block text-[10px] text-red-500 font-bold">TERLAMBAT</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-medium text-slate-800">Rp {{ number_format($rec->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-semibold text-emerald-600">Rp {{ number_format($rec->paid_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-black text-amber-600">
                                Rp {{ number_format($rec->remaining_balance, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $rec->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($rec->isOverdue() ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ $rec->status === 'paid' ? 'LUNAS' : ($rec->isOverdue() ? 'JATUH TEMPO' : strtoupper($rec->status)) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($rec->remaining_balance > 0)
                                    <button type="button" onclick="openPayReceivableModal({{ json_encode($rec) }})"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition">
                                        Bayar Cicilan
                                    </button>
                                @else
                                    <span class="text-slate-400 font-medium text-[11px]">&check; Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Belum ada data piutang customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $receivables->links() }}</div>
    </div>

</div>

<!-- Modal Bayar Cicilan Piutang -->
<div id="payModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-2">Pencatatan Pembayaran Piutang</h3>
        <p class="text-xs text-slate-500 mb-4" id="modalCustomerName"></p>

        <form id="payForm" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 flex items-center justify-between">
                <span class="text-amber-800 font-semibold">Sisa Tagihan Piutang:</span>
                <span class="font-extrabold text-amber-700 text-sm" id="modalRemaining"></span>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="any" min="1" name="amount" id="payAmount" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-black text-sm focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="qris">QRIS</option>
                    <option value="debit">Debit Card</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan</label>
                <input type="text" name="notes" placeholder="Contoh: Cicilan ke-1 tunai di toko"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('payModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayReceivableModal(rec) {
        document.getElementById('modalCustomerName').innerText = `Customer: ${rec.customer?.name} (Faktur: ${rec.sale?.invoice_number})`;
        document.getElementById('modalRemaining').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(rec.remaining_balance);
        document.getElementById('payAmount').value = rec.remaining_balance;
        document.getElementById('payAmount').max = rec.remaining_balance;
        document.getElementById('payForm').action = `/finance/receivables/${rec.id}/pay`;
        document.getElementById('payModal').classList.remove('hidden');
    }
</script>
@endsection

