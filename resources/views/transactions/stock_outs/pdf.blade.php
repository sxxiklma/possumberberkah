<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Barang Keluar #{{ $stockOut->reference_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #333; margin: 30px; }
        .header { border-bottom: 2px solid #ef4444; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; color: #ef4444; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 7px; text-align: left; }
        .table th { background-color: #f8fafc; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="title">TOKO SUMBER BERKAH</div>
                    <p style="margin: 2px 0;">Bukti Pengeluaran Barang Non-Penjualan</p>
                    <p style="margin: 0; color: #666; font-size: 10px;">Jl. Pasar Besar No. 88, Cibinong</p>
                </td>
                <td style="text-align: right;">
                    <p style="margin: 0; font-family: monospace; font-size: 14px; font-weight: bold;">{{ $stockOut->reference_number }}</p>
                    <p style="margin: 3px 0; color: #666;">Tanggal: {{ $stockOut->date->format('d/m/Y') }}</p>
                    <p style="margin: 0; font-weight: bold; color: #ef4444;">Alasan: {{ strtoupper(str_replace('_', ' ', $stockOut->reason)) }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 12px;">
        <tr>
            <td><strong>Gudang Asal:</strong> {{ $stockOut->warehouse?->name }}</td>
            <td style="text-align: right;"><strong>Petugas Pencatat:</strong> {{ $stockOut->user?->name }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th style="text-align: center;">Jumlah Keluar</th>
                <th style="text-align: center;">Satuan Dasar</th>
                <th class="text-right">Beban HPP (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOut->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $item->product?->sku }}</td>
                    <td class="font-bold">{{ $item->product?->name }}</td>
                    <td style="text-align: center;">{{ (float)$item->quantity }} {{ $item->unit?->code }}</td>
                    <td style="text-align: center;">{{ (float)$item->base_quantity }} {{ $item->product?->baseUnit?->code }}</td>
                    <td class="text-right font-bold">{{ number_format($item->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right font-bold">TOTAL NILAI PEMBEBANAN MODAL:</td>
                <td class="text-right font-bold" style="font-size: 13px; color: #ef4444;">Rp {{ number_format($stockOut->total_cost, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; width: 100%;">
        <table style="width: 100%; text-align: center;">
            <tr>
                <td style="width: 50%;">
                    Petugas Gudang,<br><br><br><br>
                    <strong>( {{ $stockOut->user?->name }} )</strong>
                </td>
                <td style="width: 50%;">
                    Mengetahui (Pimpinan/Owner),<br><br><br><br>
                    <strong>( ..................................... )</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

