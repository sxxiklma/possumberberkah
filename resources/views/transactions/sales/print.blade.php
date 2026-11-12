<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #fff;
            color: #1e293b;
            padding: 30px;
            font-size: 12px;
            line-height: 1.5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            padding: 30px;
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .store-info h1 {
            font-size: 20px;
            font-weight: 800;
            color: #059669;
            letter-spacing: -0.5px;
        }
        .store-info p {
            color: #475569;
            font-size: 11px;
            margin-top: 3px;
            max-width: 420px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .invoice-title p {
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
            color: #059669;
            margin-top: 2px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            font-size: 11.5px;
        }
        .meta-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 15px;
        }
        .meta-card h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #cbd5e1;
            font-size: 11px;
        }
        td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .totals-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 15px;
        }
        .notes-box {
            max-width: 420px;
            font-size: 11px;
            color: #475569;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .totals-table {
            width: 300px;
            font-size: 12px;
        }
        .totals-table td {
            padding: 5px 0;
            border: none;
        }
        .totals-table tr.grand-total td {
            font-size: 14px;
            font-weight: 800;
            color: #059669;
            border-top: 2px solid #cbd5e1;
            padding-top: 8px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            text-align: center;
        }
        .sign-box {
            padding-top: 60px;
            border-top: 1px solid #94a3b8;
            font-weight: 600;
            color: #334155;
            font-size: 11.5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-tempo { background: #fef3c7; color: #92400e; }
        .badge-void { background: #fee2e2; color: #991b1b; }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            background: #059669;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
        }
        .btn-secondary {
            background: #64748b;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .container { border: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn">🖨️ Cetak Faktur Ini</button>
    <a href="{{ route('transactions.sales.show', $sale) }}" class="btn btn-secondary">&larr; Kembali ke Detail Transaksi</a>
</div>

<div class="container">
    <div class="header">
        <div class="store-info">
            <h1>{{ $store['name'] }}</h1>
            <p style="font-weight: 600; color: #059669;">{{ $store['tagline'] }}</p>
            <p>{{ $store['address'] }}</p>
            <p>Telp / WhatsApp: {{ $store['phone'] }} | Email: {{ $store['email'] }}</p>
        </div>
        <div class="invoice-title">
            <h2>FAKTUR PENJUALAN</h2>
            <p>{{ $sale->invoice_number }}</p>
            <div style="margin-top: 6px;">
                @if($sale->status === 'void')
                    <span class="badge badge-void">BATAL (VOID)</span>
                @elseif($sale->payment_status === 'paid')
                    <span class="badge badge-paid">LUNAS</span>
                @else
                    <span class="badge badge-tempo">TEMPO (PIUTANG)</span>
                @endif
            </div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-card">
            <h3>DITUJUKAN KEPADA</h3>
            <p><strong>{{ $sale->customer?->name ?? 'Customer Umum (Walk-In)' }}</strong></p>
            @if($sale->customer)
                <p>Telp: {{ $sale->customer->phone ?: '-' }}</p>
                <p>Alamat: {{ $sale->customer->address ?: '-' }}</p>
            @endif
        </div>
        <div class="meta-card">
            <h3>INFORMASI TRANSAKSI</h3>
            <p>Tanggal: <strong>{{ $sale->sale_date->format('d/m/Y H:i') }} WIB</strong></p>
            <p>Petugas: <strong>{{ $sale->user?->name ?? 'Kasir' }}</strong></p>
            <p>Metode Pembayaran: <strong>{{ strtoupper($sale->payment_method) }}</strong> ({{ $sale->price_type === 'wholesale' ? 'Grosir' : 'Retail' }})</p>
            @if($sale->payment_status !== 'paid' && $sale->due_date)
                <p style="color: #b45309; font-weight: bold;">Jatuh Tempo: {{ $sale->due_date->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th>Nama Produk & Spesifikasi</th>
                <th style="width: 80px;" class="text-center">Satuan</th>
                <th style="width: 70px;" class="text-center">Qty</th>
                <th style="width: 110px;" class="text-right">Harga Satuan</th>
                <th style="width: 90px;" class="text-right">Diskon</th>
                <th style="width: 120px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $idx => $item)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong>
                        <div style="font-size: 10px; color: #64748b; font-family: monospace;">SKU: {{ $item->product?->sku }}</div>
                    </td>
                    <td class="text-center font-mono" style="font-weight: 700;">{{ $item->unit?->code }}</td>
                    <td class="text-center" style="font-weight: 700;">{{ (float)$item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #dc2626;">
                        {{ $item->discount_amount > 0 ? '- Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="notes-box">
            <strong style="color: #0f172a;">Catatan:</strong>
            <p style="margin-top: 3px;">{{ $sale->notes ?: 'Terima kasih telah berbelanja di Toko Sumber Berkah. Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan kecuali ada perjanjian sebelumnya.' }}</p>
            <p style="margin-top: 6px; font-size: 10px; color: #94a3b8;">{{ $store['footer'] }}</p>
        </div>

        <table class="totals-table">
            <tr>
                <td style="color: #64748b;">Subtotal:</td>
                <td class="text-right" style="font-weight: 600;">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount_amount > 0)
                <tr>
                    <td style="color: #dc2626;">Diskon Faktur:</td>
                    <td class="text-right" style="color: #dc2626; font-weight: 600;">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($sale->tax_amount > 0)
                <tr>
                    <td style="color: #64748b;">Pajak PPN:</td>
                    <td class="text-right" style="font-weight: 600;">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>Total Tagihan:</td>
                <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Jumlah Dibayar:</td>
                <td class="text-right" style="font-weight: 700;">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
            @if($sale->change_amount > 0)
                <tr>
                    <td style="color: #059669;">Kembalian:</td>
                    <td class="text-right" style="color: #059669; font-weight: 700;">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            @elseif($sale->total_amount > $sale->paid_amount)
                <tr>
                    <td style="color: #b45309; font-weight: bold;">Sisa Piutang:</td>
                    <td class="text-right" style="color: #b45309; font-weight: 800;">Rp {{ number_format($sale->total_amount - $sale->paid_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="signatures">
        <div>
            <p style="margin-bottom: 50px; color: #64748b;">Tanda Terima Pelanggan,</p>
            <div class="sign-box">
                ( {{ $sale->customer?->name ?? 'Pelanggan' }} )
            </div>
        </div>
        <div>
            <p style="margin-bottom: 50px; color: #64748b;">Hormat Kami,</p>
            <div class="sign-box">
                ( {{ $sale->user?->name ?? 'Toko Sumber Berkah' }} )
            </div>
        </div>
    </div>
</div>

</body>
</html>

