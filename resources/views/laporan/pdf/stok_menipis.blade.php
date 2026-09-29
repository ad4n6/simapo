@extends('laporan.pdf.layout')

@section('title', 'Laporan Obat Menipis dan Kosong')

@section('content')
<div class="title-block">
    <h2>LAPORAN OBAT MENIPIS & KOSONG (DEFEKTA)</h2>
    <span>Tanggal Cetak: {{ date('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="5%" class="text-center">No</th>
            <th width="15%">Kode</th>
            <th>Nama Obat</th>
            <th width="12%">Kategori</th>
            <th width="10%" class="text-center">Stok</th>
            <th width="10%" class="text-center">Min</th>
            <th width="10%" class="text-center">Rak</th>
            <th width="12%" class="text-center">Status</th>
            <th width="12%" class="text-right">Saran Order</th>
        </tr>
    </thead>
    <tbody>
        @foreach($obats as $idx => $o)
        @php
            $stok = $o->total_stok;
            $rekomendasi = max(10, ($o->stok_minimum * 3) - $stok);
        @endphp
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono">{{ $o->kode_obat }}</td>
            <td class="font-bold">{{ $o->nama }}</td>
            <td>{{ $o->kategori->nama ?? '-' }}</td>
            <td class="text-center font-bold" style="color: {{ $stok == 0 ? '#b91c1c' : '#b45309' }};">{{ number_format($stok) }}</td>
            <td class="text-center">{{ number_format($o->stok_minimum) }}</td>
            <td class="text-center font-mono">{{ $o->lokasi_rak ?? '-' }}</td>
            <td class="text-center font-bold" style="color: {{ $stok == 0 ? '#b91c1c' : '#b45309' }};">
                {{ $stok == 0 ? 'KOSONG' : 'MENIPIS' }}
            </td>
            <td class="text-right font-bold" style="color: #047857;">+{{ number_format($rekomendasi) }} {{ $o->satuan->nama ?? 'Pcs' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
