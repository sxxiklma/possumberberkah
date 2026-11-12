<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $sale->invoice_number }} - {{ $store['name'] }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 10px;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; margin: 6px 0; }
        .border-bottom { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 0; }
        .btn-print {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="no-print text-center">
        <button class="btn-print" onclick="window.print()">Cetak Struk (Print)</button>
    </div>

    <div class="receipt">
        <!-- Header -->
        <div class="text-center">
            <h2 style="margin: 0; font-size: 15px;" class="font-bold">{{ $store['name'] }}</h2>
            <p style="margin: 2px 0; font-size: 11px;">{{ $store['address'] }}</p>
            <p style="margin: 2px 0; font-size: 11px;">Telp/WA: {{ $store['phone'] }}</p>
        </div>

        <div class="border-top"></div>

        <!-- Meta -->
        <div>
            <div>Faktur : {{ $sale->invoice_number }}</div>
            <div>Tgl    : {{ $sale->sale_date->format('d/m/Y H:i') }}</div>
            <div>Kasir  : {{ $sale->user?->name }}</div>
            <div>Mitra  : {{ $sale->customer?->name ?? 'Customer Umum' }}</div>
            <div>Metode : {{ strtoupper($sale->payment_method) }} ({{ strtoupper($sale->payment_status) }})</div>
        </div>

        <div class="border-top"></div>

        <!-- Items Table -->
        <table>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td colspan="3" class="font-bold">{{ $item->product?->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 45%;">{{ (float)$item->quantity }} {{ $item->unit?->code }} x {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td style="width: 20%;" class="text-right">
                            @if($item->discount_amount > 0)
                                -{{ number_format($item->discount_amount, 0, ',', '.') }}
                            @endif
                        </td>
                        <td style="width: 35%;" class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-top"></div>

        <!-- Totals -->
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right font-bold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount_amount > 0)
                <tr>
                    <td>Diskon Transaksi</td>
                    <td class="text-right">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($sale->tax_amount > 0)
                <tr>
                    <td>Pajak</td>
                    <td class="text-right">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="font-bold" style="font-size: 13px;">TOTAL BAYAR</td>
                <td class="text-right font-bold" style="font-size: 13px;">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar Diterima</td>
                <td class="text-right font-bold">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
            @if($sale->change_amount > 0)
                <tr>
                    <td>Kembalian</td>
                    <td class="text-right font-bold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($sale->total_amount > $sale->paid_amount)
                <tr>
                    <td class="font-bold" style="color: #000;">Sisa Piutang (Tempo)</td>
                    <td class="text-right font-bold">Rp {{ number_format($sale->total_amount - $sale->paid_amount, 0, ',', '.') }}</td>
                </tr>
                @if($sale->due_date)
                    <tr>
                        <td style="font-size: 10px;">Jatuh Tempo</td>
                        <td class="text-right" style="font-size: 10px;">{{ $sale->due_date->format('d/m/Y') }}</td>
                    </tr>
                @endif
            @endif
        </table>

        <div class="border-top"></div>

        <!-- Footer -->
        <div class="text-center" style="font-size: 11px; white-space: pre-line; margin-top: 6px;">
            {{ $store['footer'] }}
        </div>
    </div>

</body>
</html>

