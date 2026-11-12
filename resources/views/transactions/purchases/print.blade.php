<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Bukti Pembelian #{{ $purchase->purchase_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #333; margin: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #10b981; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #10b981; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f8fafc; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div>
            <div class="title">{{ \App\Models\Setting::get('store_name', 'TOKO SUMBER BERKAH') }}</div>
            <p style="margin: 3px 0;">Pusat Bahan Kue & Perlengkapan Roti Terlengkap</p>
            <p style="margin: 0; color: #666;">{{ \App\Models\Setting::get('store_address', 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Kabupaten Jombang, Jawa Timur 64181') }} &bull; Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin: 0; font-size: 14px;">BUKTI PEMBELIAN BARANG (PO)</h2>
            <p style="margin: 3px 0; font-family: monospace; font-size: 13px; font-weight: bold;">{{ $purchase->purchase_number }}</p>
            <p style="margin: 0; color: #666;">Tanggal: {{ $purchase->purchase_date->format('d F Y') }}</p>
        </div>
    </div>

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <strong>Kepada Supplier:</strong><br>
                {{ $purchase->supplier?->name }}<br>
                {{ $purchase->supplier?->address }}<br>
                Telp: {{ $purchase->supplier?->phone }}
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <strong>Gudang Penerima:</strong><br>
                {{ $purchase->warehouse?->name }}<br>
                Petugas: {{ $purchase->user?->name }}<br>
                Status Fisik: {{ strtoupper($purchase->status) }}<br>
                Status Bayar: <strong>{{ strtoupper($purchase->payment_status === 'paid' ? 'LUNAS' : ($purchase->payment_status === 'partial' ? 'DP (Uang Muka)' : 'TEMPO (Hutang)')) }}</strong>
                @if($purchase->due_date && $purchase->payment_status !== 'paid')
                    <br>Jatuh Tempo: <strong>{{ $purchase->due_date->format('d/m/Y') }}</strong>
                @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th style="text-align: center;">Jumlah Beli</th>
                <th style="text-align: center;">Satuan Dasar</th>
                <th class="text-right">Harga Satuan (Rp)</th>
                <th class="text-right">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $item->product?->sku }}</td>
                    <td class="font-bold">{{ $item->product?->name }}</td>
                    <td style="text-align: center;">{{ (float)$item->quantity }} {{ $item->unit?->code }}</td>
                    <td style="text-align: center;">{{ (float)$item->base_quantity }} {{ $item->product?->baseUnit?->code }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">TOTAL PEMBELIAN:</td>
                <td class="text-right font-bold" style="font-size: 13px;">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right">Nominal Terbayar:</td>
                <td class="text-right font-bold">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right font-bold" style="color: #c026d3;">Sisa Hutang:</td>
                <td class="text-right font-bold" style="color: #c026d3;">Rp {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center;">
        <div style="width: 200px;">
            <p>Penerima Gudang,</p>
            <br><br><br>
            <p style="border-top: 1px solid #333; margin: 0;">( {{ $purchase->user?->name }} )</p>
        </div>
        <div style="width: 200px;">
            <p>Supplier / Pengirim,</p>
            <br><br><br>
            <p style="border-top: 1px solid #333; margin: 0;">( {{ $purchase->supplier?->name }} )</p>
        </div>
    </div>

</body>
</html>

