@extends('laporan.pdf.layout')

@section('title', 'Laporan Obat Terlaris')

@section('content')
<div class="title-block">
    <h2>LAPORAN PENJUALAN PER PRODUK OBAT (TERLARIS)</h2>
    <span>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="5%" class="text-center">Rank</th>
            <th width="15%">Kode</th>
            <th>Nama Obat</th>
            <th width="12%">Kategori</th>
            <th width="10%" class="text-center">Terjual</th>
            <th width="15%" class="text-right">Total Omset</th>
            <th width="15%" class="text-right">Gross Margin</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $idx => $it)
        <tr>
            <td class="text-center font-bold">{{ $idx + 1 }}</td>
            <td class="font-mono">{{ $it->obat->kode_obat }}</td>
            <td class="font-bold">{{ $it->obat->nama }}</td>
            <td>{{ $it->obat->kategori->nama ?? '-' }}</td>
            <td class="text-center font-bold">{{ number_format($it->total_qty) }} {{ $it->obat->satuan->nama ?? 'Pcs' }}</td>
            <td class="text-right font-mono">Rp {{ number_format($it->total_omset, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($it->total_omset - $it->total_hpp, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
