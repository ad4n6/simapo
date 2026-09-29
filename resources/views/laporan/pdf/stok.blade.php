@extends('laporan.pdf.layout')

@section('title', 'Laporan Nilai Persediaan')

@section('content')
<div class="title-block">
    <h2>LAPORAN NILAI PERSEDIAAN STOK OBAT</h2>
    <span>Tanggal Cetak: {{ date('d/m/Y') }}</span>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="12%">Kode</th>
            <th>Nama Obat</th>
            <th width="12%">Kategori</th>
            <th width="10%" class="text-center">Stok</th>
            <th width="14%" class="text-right">Harga Beli</th>
            <th width="14%" class="text-right">Total HPP</th>
            <th width="14%" class="text-right">Total Jual</th>
        </tr>
    </thead>
    <tbody>
        @foreach($obats as $idx => $o)
        @php
            $stok = $o->total_stok;
            $valBeli = $o->batches->sum(fn($b) => $b->jumlah_saat_ini * $b->harga_beli);
            $valJual = $stok * $o->harga_jual;
        @endphp
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-mono">{{ $o->kode_obat }}</td>
            <td class="font-bold">{{ $o->nama }}</td>
            <td>{{ $o->kategori->nama ?? '-' }}</td>
            <td class="text-center font-bold">{{ number_format($stok) }} {{ $o->satuan->nama ?? 'Pcs' }}</td>
            <td class="text-right font-mono">Rp {{ number_format($o->harga_beli, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($valBeli, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold">Rp {{ number_format($valJual, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="6" class="text-right">TOTAL VALUASI PERSEDIAAN:</td>
            <td class="text-right font-mono">Rp {{ number_format($totalNilaiBeli, 0, ',', '.') }}</td>
            <td class="text-right font-mono">Rp {{ number_format($totalNilaiJual, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
