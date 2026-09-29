@extends('laporan.pdf.layout')

@section('title', 'Laporan Pembelian & Hutang')

@section('content')
<div class="title-block">
    <h2>LAPORAN PEMBELIAN & HUTANG DISTRIBUTOR (PBF)</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="16%">No. Faktur</th>
            <th width="10%">Tgl Faktur</th>
            <th>Distributor PBF</th>
            <th width="8%" class="text-center">Jenis</th>
            <th width="14%" class="text-right">Total Faktur</th>
            <th width="14%" class="text-right">Terbayar</th>
            <th width="14%" class="text-right">Sisa Hutang</th>
            <th width="10%" class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pembelians as $idx => $p)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono font-bold">{{ $p->no_faktur }}</td>
            <td>{{ \Carbon\Carbon::parse($p->tanggal_faktur)->format('d/m/Y') }}</td>
            <td>{{ $p->suplier->nama }}</td>
            <td class="text-center">{{ ucfirst($p->jenis_pembayaran) }}</td>
            <td class="text-right font-mono">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</td>
            <td class="text-right font-mono">Rp {{ number_format($p->total_terbayar, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold" style="color: {{ $p->sisa_hutang > 0 ? '#b91c1c' : '#047857' }};">
                Rp {{ number_format($p->sisa_hutang, 0, ',', '.') }}
            </td>
            <td class="text-center font-bold">{{ ucfirst($p->status_pembayaran) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
            <td class="text-right font-mono">Rp {{ number_format($totalNilaiFaktur, 0, ',', '.') }}</td>
            <td class="text-right font-mono">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
            <td class="text-right font-mono" style="color: #b91c1c;">Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endsection
