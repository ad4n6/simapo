@extends('laporan.pdf.layout')

@section('title', 'Laporan Mutasi Stok')

@section('content')
<div class="title-block">
    <h2>LAPORAN MUTASI KARTU STOK OBAT</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="14%">Waktu</th>
            <th>Nama Obat</th>
            <th width="12%">No. Batch</th>
            <th width="10%" class="text-center">Jenis</th>
            <th width="8%" class="text-center">Awal</th>
            <th width="8%" class="text-center">Mutasi</th>
            <th width="8%" class="text-center">Akhir</th>
            <th width="14%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allMutasis as $idx => $m)
        @php
            $isPlus = in_array($m->jenis, ['masuk', 'retur_penjualan']) || ($m->jenis === 'penyesuaian' && $m->saldo_sesudah >= $m->saldo_sebelum);
        @endphp
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</td>
            <td class="font-bold">{{ $m->obat->nama }}</td>
            <td class="font-mono">{{ $m->batchObat->no_batch ?? '-' }}</td>
            <td class="text-center font-bold">{{ strtoupper(str_replace('_', ' ', $m->jenis)) }}</td>
            <td class="text-center">{{ number_format($m->saldo_sebelum) }}</td>
            <td class="text-center font-bold" style="color: {{ $isPlus ? '#047857' : '#b91c1c' }};">
                {{ $isPlus ? '+' : '-' }}{{ number_format($m->jumlah) }}
            </td>
            <td class="text-center font-bold">{{ number_format($m->saldo_sesudah) }}</td>
            <td>{{ $m->keterangan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
