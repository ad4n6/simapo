<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran {{ $penjualan->no_transaksi }} - Apotek Bintan 88</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace, monospace;
        }
        body {
            width: 78mm;
            margin: 0 auto;
            padding: 8px 10px;
            color: #000;
            background: #fff;
            font-size: 11px;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 1px 0;
            font-size: 10px;
        }
        .meta-table, .items-table, .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 10px;
            padding: 1px 0;
        }
        .items-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
            font-size: 10px;
        }
        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .total-table td {
            padding: 1px 0;
        }
        .footer {
            margin-top: 10px;
            font-size: 9px;
            line-height: 1.2;
        }
        .no-print {
            margin-top: 15px;
            text-align: center;
        }
        .btn-print {
            padding: 6px 16px;
            background: #059669;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-family: sans-serif;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header Apotek -->
    <div class="header text-center">
        <h1 class="font-bold">APOTEK BINTAN 88</h1>
        <p>Jl. Bintan No. 88, Tanjungpinang, Kepri</p>
        <p>Telp: (0771) 888-888 | WA: 0812-3456-7890</p>
        <p>SIA: 503/SIA-088/DPMPTSP/2024</p>
        <p>Apoteker: apt. Hendra Wijaya, S.Farm.</p>
        <p>SIPA: 19890515/SIPA_2172/2024/2001</p>
    </div>

    <div class="divider"></div>

    <!-- Metadata Transaksi -->
    <table class="meta-table">
        <tr>
            <td width="30%">No. Nota</td>
            <td width="5%">:</td>
            <td class="font-bold">{{ $penjualan->no_transaksi }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($penjualan->created_at)->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $penjualan->user->nama ?? 'Kasir' }}</td>
        </tr>
        @if($penjualan->pelanggan)
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>{{ $penjualan->pelanggan->nama }}</td>
        </tr>
        @endif
        @if($penjualan->jenis_transaksi === 'resep' && $penjualan->resep)
        <tr>
            <td>No. Resep</td>
            <td>:</td>
            <td>{{ $penjualan->resep->no_resep }}</td>
        </tr>
        <tr>
            <td>Pasien / Dr</td>
            <td>:</td>
            <td>{{ $penjualan->resep->nama_pasien }} / {{ $penjualan->resep->nama_dokter }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- Rincian Item -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">Item Obat</th>
                <th class="text-right" width="20%">Qty x Harga</th>
                <th class="text-right" width="25%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->details as $item)
            <tr>
                <td colspan="3" class="font-bold" style="padding-top: 3px;">
                    {{ $item->obat->nama }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 9px; color: #444;">
                    [Batch: {{ $item->no_batch ?? '-' }}]
                </td>
                <td class="text-right">
                    {{ $item->jumlah }} x {{ number_format($item->harga_jual, 0, ',', '.') }}
                </td>
                <td class="text-right font-bold">
                    {{ number_format($item->subtotal, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Ringkasan Total & Pembayaran -->
    <table class="total-table">
        <tr>
            <td class="text-left">Subtotal</td>
            <td class="text-right font-bold">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($penjualan->diskon_nominal > 0)
        <tr>
            <td class="text-left">Diskon ({{ (float)$penjualan->diskon_persen }}%)</td>
            <td class="text-right">- Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left font-bold" style="font-size: 12px;">TOTAL AKHIR</td>
            <td class="text-right font-bold" style="font-size: 12px;">Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Metode Bayar</td>
            <td class="text-right font-bold">{{ strtoupper($penjualan->metode_pembayaran) }}</td>
        </tr>
        @if($penjualan->metode_pembayaran === 'tunai')
        <tr>
            <td class="text-left">Jumlah Bayar</td>
            <td class="text-right">Rp {{ number_format($penjualan->jumlah_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left font-bold">Kembalian</td>
            <td class="text-right font-bold">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- Catatan & Footer -->
    <div class="footer text-center">
        <p class="font-bold">*** TERIMA KASIH ***</p>
        <p>SEMOGA LEKAS SEMBUH & SEHAT SELALU</p>
        <p style="margin-top: 4px; font-size: 8px;">
            Obat yang sudah dibeli tidak dapat ditukar atau dikembalikan demi jaminan mutu dan higienitas obat.
        </p>
        <p style="margin-top: 4px; font-size: 8px;">
            Dicetak otomatis oleh Sistem SIMAPO Apotek Bintan 88
        </p>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Ulang</button>
        <button class="btn-print" style="background: #475569;" onclick="window.close()">Tutup Jendela</button>
    </div>

</body>
</html>
