@extends('laporan.pdf.layout')

@section('title', 'Laporan Retur')

@section('content')
<div class="title-block">
    <h2>LAPORAN RETUR PENJUALAN & PEMBELIAN</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<h3 style="font-size: 11px; margin: 10px 0 5px 0;">1. RETUR PENJUALAN (DARI PELANGGAN)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="18%">No. Retur</th>
            <th width="18%">Ref. Transaksi</th>
            <th width="12%">Tanggal</th>
            <th>Alasan</th>
            <th width="15%" class="text-right">Total Refund</th>
        </tr>
    </thead>
    <tbody>
        @forelse($returPenjualans as $idx => $r)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono font-bold">{{ $r->no_retur }}</td>
            <td class="font-mono">{{ $r->penjualan->no_transaksi }}</td>
            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}</td>
            <td>{{ $r->alasan }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($r->total_pengembalian, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">Tidak ada retur penjualan.</td></tr>
        @endforelse
    </tbody>
</table>

<h3 style="font-size: 11px; margin: 15px 0 5px 0;">2. RETUR PEMBELIAN (KE DISTRIBUTOR PBF)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="18%">No. Retur</th>
            <th width="20%">Distributor PBF</th>
            <th width="12%">Tanggal</th>
            <th>Alasan</th>
            <th width="15%" class="text-right">Nilai Retur</th>
        </tr>
    </thead>
    <tbody>
        @forelse($returPembelians as $idx => $r)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono font-bold">{{ $r->no_retur }}</td>
            <td class="font-bold">{{ $r->suplier->nama }}</td>
            <td>{{ \Carbon\Carbon::parse($r->tanggal_retur)->format('d/m/Y') }}</td>
            <td>{{ $r->alasan }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($r->total_nilai, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">Tidak ada retur pembelian.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
