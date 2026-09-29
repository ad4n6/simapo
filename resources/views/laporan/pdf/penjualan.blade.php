@extends('laporan.pdf.layout')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="title-block">
    <h2>LAPORAN PENJUALAN APOTEK</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="18%">No. Transaksi</th>
            <th width="15%">Waktu</th>
            <th width="14%">Kasir</th>
            <th width="14%">Pelanggan</th>
            <th width="7%">Metode</th>
            <th width="14%" class="text-right">Total Akhir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($penjualans as $idx => $p)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono font-bold">{{ $p->no_transaksi }}</td>
            <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</td>
            <td>{{ $p->user->nama ?? '-' }}</td>
            <td>{{ $p->pelanggan->nama ?? 'Umum' }}</td>
            <td class="text-center font-bold">{{ strtoupper($p->metode_pembayaran) }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="6" class="text-right">TOTAL OMSET BERSIH ({{ count($penjualans) }} TRANSAKSI):</td>
            <td class="text-right font-mono">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
