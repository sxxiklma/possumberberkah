<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi - TOKO SUMBER BERKAH</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16pt; margin: 0; color: #047857; text-transform: uppercase; }
        .header p { margin: 3px 0 0; font-size: 9pt; color: #64748b; }
        .report-title { font-size: 13pt; font-weight: bold; margin: 15px 0 5px; text-align: center; text-transform: uppercase; }
        .period { text-align: center; font-size: 9pt; color: #64748b; margin-bottom: 20px; }
        table.pl { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.pl td { padding: 7px 10px; font-size: 9.5pt; }
        .section-header { background: #f1f5f9; font-weight: bold; font-size: 9pt; text-transform: uppercase; color: #334155; }
        .indent { padding-left: 25px !important; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .subtotal { border-top: 1px solid #cbd5e1; font-weight: bold; }
        .grandtotal { border-top: 2px solid #047857; border-bottom: 2px solid #047857; font-weight: bold; background: #ecfdf5; font-size: 11pt; }
        .footer { margin-top: 40px; width: 100%; font-size: 9pt; }
        .signature-box { float: right; width: 220px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ \App\Models\Setting::get('store_name', 'TOKO SUMBER BERKAH') }}</h1>
        <p>{{ \App\Models\Setting::get('store_address', 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Kabupaten Jombang, Jawa Timur 64181') }} | Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
    </div>

    <div class="report-title">Laporan Laba Rugi Komprehensif (Income Statement)</div>
    <div class="period">Periode: {{ \Carbon\Carbon::parse($report['period']['start_date'])->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($report['period']['end_date'])->format('d F Y') }}</div>

    <table class="pl">
        <!-- 1. PENDAPATAN USAHA -->
        <tr class="section-header">
            <td colspan="2">1. Pendapatan Operasional Usaha</td>
        </tr>
        <tr>
            <td class="indent">Penjualan Kotor (Gross Sales)</td>
            <td class="text-right font-mono">Rp {{ number_format($report['gross_sales'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="indent">Retur Penjualan (Sales Returns)</td>
            <td class="text-right font-mono" style="color: #e11d48;">(Rp {{ number_format($report['sales_returns'], 0, ',', '.') }})</td>
        </tr>
        <tr class="subtotal">
            <td>TOTAL PENJUALAN BERSIH (NET SALES)</td>
            <td class="text-right font-mono">Rp {{ number_format($report['net_sales'], 0, ',', '.') }}</td>
        </tr>

        <!-- 2. HPP FIFO -->
        <tr><td colspan="2" style="height: 10px;"></td></tr>
        <tr class="section-header">
            <td colspan="2">2. Harga Pokok Penjualan (HPP)</td>
        </tr>
        <tr>
            <td class="indent">Harga Pokok Penjualan Metode FIFO</td>
            <td class="text-right font-mono" style="color: #e11d48;">(Rp {{ number_format($report['fifo_cogs'], 0, ',', '.') }})</td>
        </tr>
        <tr class="subtotal" style="color: #047857; font-size: 10.5pt;">
            <td>LABA KOTOR USAHA (GROSS PROFIT) [Margin: {{ $report['gross_margin_percentage'] }}%]</td>
            <td class="text-right font-mono">Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}</td>
        </tr>

        <!-- 3. BEBAN OPERASIONAL -->
        <tr><td colspan="2" style="height: 10px;"></td></tr>
        <tr class="section-header">
            <td colspan="2">3. Beban Operasional Toko</td>
        </tr>
        @forelse($report['expenses_by_category'] as $cat => $amount)
        <tr>
            <td class="indent">{{ $cat }}</td>
            <td class="text-right font-mono">Rp {{ number_format($amount, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td class="indent" style="color: #94a3b8; font-style: italic;">Tidak ada catatan beban operasional</td>
            <td class="text-right font-mono">Rp 0</td>
        </tr>
        @endforelse
        <tr class="subtotal">
            <td>TOTAL BEBAN OPERASIONAL</td>
            <td class="text-right font-mono" style="color: #e11d48;">(Rp {{ number_format($report['operational_expenses'], 0, ',', '.') }})</td>
        </tr>

        <!-- 4. LABA BERSIH USAHA -->
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr class="grandtotal">
            <td>LABA BERSIH USAHA (NET PROFIT) [Net Margin: {{ $report['net_margin_percentage'] }}%]</td>
            <td class="text-right font-mono" style="color: #047857;">Rp {{ number_format($report['net_profit'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Sidoarjo, {{ date('d F Y') }}</p>
            <p>Disetujui oleh,</p>
            <br><br><br>
            <p style="border-top: 1px solid #94a3b8; padding-top: 5px;">( Pemilik / Direktur Toko )</p>
        </div>
    </div>
</body>
</html>

