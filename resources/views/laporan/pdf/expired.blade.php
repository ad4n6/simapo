@extends('laporan.pdf.layout')

@section('title', 'Laporan Obat Kedaluwarsa')

@section('content')
<div class="title-block">
    <h2>LAPORAN OBAT KEDALUWARSA & MENDEKATI EXPIRED</h2>
    <span>Tanggal Cetak: {{ date('d/m/Y') }}</span>
</div>

<h3 style="font-size: 11px; margin: 10px 0 5px 0; color: #b91c1c;">1. BATCH OBAT SUDAH KEDALUWARSA (TIDAK BOLEH DIJUAL)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama Obat</th>
            <th width="15%">No. Batch</th>
            <th width="12%" class="text-center">Tanggal ED</th>
            <th width="12%" class="text-center">Sisa Stok</th>
            <th width="15%" class="text-right">Harga Beli</th>
            <th width="15%" class="text-right">Potensi Kerugian</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expiredBatches as $idx => $b)
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-bold">{{ $b->obat->nama }}</td>
            <td class="font-mono">{{ $b->no_batch }}</td>
            <td class="text-center font-bold font-mono" style="color: #b91c1c;">{{ \Carbon\Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y') }}</td>
            <td class="text-center font-bold">{{ $b->jumlah_saat_ini }} {{ $b->obat->satuan->nama ?? 'Pcs' }}</td>
            <td class="text-right font-mono">Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
            <td class="text-right font-mono font-bold" style="color: #b91c1c;">Rp {{ number_format($b->jumlah_saat_ini * $b->harga_beli, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">Tidak ada batch obat kedaluwarsa.</td></tr>
        @endforelse
    </tbody>
</table>

<h3 style="font-size: 11px; margin: 15px 0 5px 0; color: #b45309;">2. BATCH OBAT MENDEKATI KEDALUWARSA (≤ 90 HARI)</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama Obat</th>
            <th width="15%">No. Batch</th>
            <th width="12%" class="text-center">Tanggal ED</th>
            <th width="12%" class="text-center">Sisa Hari</th>
            <th width="12%" class="text-center">Sisa Stok</th>
            <th width="15%" class="text-right">Nilai Aset</th>
        </tr>
    </thead>
    <tbody>
        @forelse($nearExpiredBatches as $idx => $b)
        @php $days = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($b->tanggal_kedaluwarsa)); @endphp
        <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td class="font-bold">{{ $b->obat->nama }}</td>
            <td class="font-mono">{{ $b->no_batch }}</td>
            <td class="text-center font-mono">{{ \Carbon\Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y') }}</td>
            <td class="text-center font-bold" style="color: #b45309;">{{ $days }} Hari</td>
            <td class="text-center font-bold">{{ $b->jumlah_saat_ini }} {{ $b->obat->satuan->nama ?? 'Pcs' }}</td>
            <td class="text-right font-mono">Rp {{ number_format($b->jumlah_saat_ini * $b->harga_beli, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">Tidak ada batch obat mendekati kedaluwarsa.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
