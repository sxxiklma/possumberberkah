@extends('layouts.app', ['title' => 'Buku Hutang Supplier', 'activeAccordion' => 'finance'])

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Buku Hutang Supplier (Supplier Payables)</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola kewajiban pembayaran tempo pembelian barang dagangan ke pemasok</p>
    </div>
    <div class="bg-rose-50 border border-rose-200 px-4 py-2 rounded-xl text-right">
        <span class="text-[10px] text-rose-800 font-semibold uppercase tracking-wider block">Total Hutang Supplier:</span>
        <span class="text-base font-black text-rose-700">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('finance.payables') }}" class="flex gap-3 text-xs">
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Status Hutang</option>
                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Dibayar Sebagian</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Lunas</option>
            </select>
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Filter</button>
            <a href="{{ route('finance.payables') }}" class="py-2 px-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Supplier</th>
                        <th class="p-3.5">No PO</th>
                        <th class="p-3.5">Tanggal Pembelian</th>
                        <th class="p-3.5">Jatuh Tempo</th>
                        <th class="p-3.5 text-right">Total Tagihan</th>
                        <th class="p-3.5 text-right">Sudah Dibayar</th>
                        <th class="p-3.5 text-right font-extrabold text-rose-700">Sisa Hutang</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payables as $pay)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-bold text-slate-800">
                                {{ $pay->supplier?->name }}
                                <div class="text-[10px] text-slate-400 font-normal">Telp: {{ $pay->supplier?->phone ?? '-' }}</div>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-700">{{ $pay->purchase?->purchase_number }}</td>
                            <td class="p-3.5 text-slate-500">{{ $pay->purchase?->purchase_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 font-semibold {{ $pay->isOverdue() ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $pay->due_date?->format('d/m/Y') ?? '-' }}
                                @if($pay->isOverdue())
                                    <span class="block text-[10px] text-red-500 font-bold">TERLAMBAT</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-medium text-slate-800">Rp {{ number_format($pay->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-semibold text-emerald-600">Rp {{ number_format($pay->paid_amount, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-black text-rose-600">
                                Rp {{ number_format($pay->remaining_balance, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $pay->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($pay->isOverdue() ? 'bg-red-50 text-red-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ $pay->status === 'paid' ? 'LUNAS' : ($pay->isOverdue() ? 'JATUH TEMPO' : strtoupper($pay->status)) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($pay->remaining_balance > 0)
                                    <button type="button" onclick="openPayPayableModal({{ json_encode($pay) }})"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition">
                                        Bayar Hutang
                                    </button>
                                @else
                                    <span class="text-slate-400 font-medium text-[11px]">&check; Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Belum ada data hutang supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $payables->links() }}</div>
    </div>

</div>

<!-- Modal Bayar Hutang -->
<div id="payableModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-2">Pencatatan Pembayaran Hutang Supplier</h3>
        <p class="text-xs text-slate-500 mb-4" id="modalSupplierName"></p>

        <form id="payableForm" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="p-3 bg-rose-50 rounded-xl border border-rose-100 flex items-center justify-between">
                <span class="text-rose-800 font-semibold">Sisa Tagihan Hutang:</span>
                <span class="font-extrabold text-rose-700 text-sm" id="modalPayableRemaining"></span>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="any" min="1" name="amount" id="payablePayAmount" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-black text-sm focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="transfer">Transfer Bank</option>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="giro">Giro / Cek</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan</label>
                <input type="text" name="notes" placeholder="Contoh: Transfer via BCA"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('payableModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayPayableModal(pay) {
        document.getElementById('modalSupplierName').innerText = `Supplier: ${pay.supplier?.name} (PO: ${pay.purchase?.purchase_number})`;
        document.getElementById('modalPayableRemaining').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(pay.remaining_balance);
        document.getElementById('payablePayAmount').value = pay.remaining_balance;
        document.getElementById('payablePayAmount').max = pay.remaining_balance;
        document.getElementById('payableForm').action = `/finance/payables/${pay.id}/pay`;
        document.getElementById('payableModal').classList.remove('hidden');
    }
</script>
@endsection

