@extends('laporan.pdf.layout')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="title-block">
    <h2>LAPORAN LABA RUGI KOTOR SEDERHANA</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table" style="max-width: 500px; margin: 0 auto 20px auto;">
    <thead>
        <tr>
            <th colspan="2" style="background-color: #0f766e; color: #fff; text-align: left; font-size: 11px;">
                KOMPONEN KEUANGAN APOTEK
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 8px;"><strong>1. Penjualan Kotor (Gross Sales)</strong></td>
            <td class="text-right font-mono font-bold" style="padding: 8px;">Rp {{ number_format($grossSales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; color: #b91c1c;">Potongan Harga / Diskon Kasir</td>
            <td class="text-right font-mono font-bold" style="padding: 8px; color: #b91c1c;">- Rp {{ number_format($totalDiskon, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td style="padding: 8px;"><strong>PENJUALAN BERSIH (NET SALES)</strong></td>
            <td class="text-right font-mono font-bold" style="padding: 8px; color: #0f766e;">Rp {{ number_format($netSales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px;"><strong>2. Beban Pokok Penjualan (HPP / Biaya Beli Obat)</strong></td>
            <td class="text-right font-mono font-bold" style="padding: 8px;">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #0f766e; color: #fff; font-weight: bold; font-size: 11px;">
            <td style="padding: 10px;">LABA KOTOR APOTEK (GROSS PROFIT)</td>
            <td class="text-right font-mono" style="padding: 10px;">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td style="padding: 8px;">Persentase Gross Profit Margin</td>
            <td class="text-right font-mono" style="padding: 8px;">{{ number_format($marginPersen, 1) }}%</td>
        </tr>
    </tbody>
</table>
@endsection
