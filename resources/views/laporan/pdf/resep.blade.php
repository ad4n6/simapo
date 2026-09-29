@extends('laporan.pdf.layout')

@section('title', 'Laporan Obat Resep')

@section('content')
<div class="title-block">
    <h2>LAPORAN PENJUALAN OBAT RESEP DOKTER</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="14%">No. Resep</th>
            <th width="10%">Tanggal</th>
            <th width="15%">Nama Pasien</th>
            <th width="15%">Dokter</th>
            <th>Obat & Batch Diserahkan</th>
            <th width="13%" class="text-right">Total Resep</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reseps as $idx => $r)
        @php
            $tot = 0;
            foreach($r->penjualans as $p) { $tot += $p->total_akhir; }
        @endphp
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono font-bold">{{ $r->no_resep }}</td>
            <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}</td>
            <td class="font-bold">{{ $r->nama_pasien }}</td>
            <td>{{ $r->nama_dokter }}</td>
            <td>
                @foreach($r->penjualans as $p)
                    @foreach($p->details as $d)
                        <div>• {{ $d->obat->nama }} (Batch: {{ $d->no_batch }}) - {{ $d->jumlah }} {{ $d->obat->satuan->nama ?? 'Pcs' }}</div>
                    @endforeach
                @endforeach
            </td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($tot, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
