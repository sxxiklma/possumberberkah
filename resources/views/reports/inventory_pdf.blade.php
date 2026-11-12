<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Persediaan - TOKO SUMBER BERKAH</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16pt; margin: 0; color: #047857; text-transform: uppercase; }
        .header p { margin: 3px 0 0; font-size: 9pt; color: #64748b; }
        .report-title { font-size: 13pt; font-weight: bold; margin: 15px 0 5px; text-align: center; text-transform: uppercase; }
        .period { text-align: center; font-size: 9pt; color: #64748b; margin-bottom: 20px; }
        .summary-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-box td { padding: 8px 12px; border: 1px solid #cbd5e1; font-size: 9pt; }
        .summary-title { background: #f8fafc; font-weight: bold; color: #475569; }
        .summary-val { font-weight: bold; font-size: 11pt; text-align: right; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 8.5pt; }
        table.data th { background: #f1f5f9; color: #334155; font-weight: bold; text-transform: uppercase; font-size: 7.5pt; border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .footer { margin-top: 30px; width: 100%; font-size: 9pt; }
        .signature-box { float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ \App\Models\Setting::get('store_name', 'TOKO SUMBER BERKAH') }}</h1>
        <p>{{ \App\Models\Setting::get('store_address', 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Kabupaten Jombang, Jawa Timur 64181') }} | Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
    </div>

    <div class="report-title">Laporan Penilaian Persediaan Barang (Inventory Valuation)</div>
    <div class="period">Kondisi Stok per: {{ date('d F Y') }}</div>

    <table class="summary-box">
        <tr>
            <td class="summary-title" width="30%">Total Nilai Aset Stok (FIFO)</td>
            <td class="summary-val" width="30%" style="color: #047857;">Rp {{ number_format($totalValuation, 0, ',', '.') }}</td>
            <td class="summary-title" width="20%">Total Unit Fisik</td>
            <td class="summary-val" width="20%">{{ number_format($totalQuantity, 0, ',', '.') }} Item</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">SKU</th>
                <th width="28%">Nama Produk</th>
                <th width="12%">Kategori</th>
                <th width="10%" class="text-right">Stok Fisik</th>
                <th width="15%" class="text-right">HPP Modal (Rp)</th>
                <th width="15%" class="text-right">Nilai Aset (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $idx => $p)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="font-mono">{{ $p->sku }}</td>
                <td><strong>{{ $p->name }}</strong><br><small style="color: #64748b;">{{ $p->composite_stock }}</small></td>
                <td>{{ $p->category?->name ?? '-' }}</td>
                <td class="text-right font-mono">{{ (float)$p->current_stock }} {{ $p->baseUnit?->symbol }}</td>
                <td class="text-right font-mono">{{ number_format($p->cost_price, 0, ',', '.') }}</td>
                <td class="text-right font-mono" style="font-weight: bold; color: #047857;">
                    {{ number_format($p->inventory_value, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="4" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right font-mono">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right font-mono" style="color: #047857;">Rp {{ number_format($totalValuation, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
            <br><br><br>
            <p style="border-top: 1px solid #94a3b8; padding-top: 5px;">( Kepala Gudang & Logistik )</p>
        </div>
    </div>
</body>
</html>

