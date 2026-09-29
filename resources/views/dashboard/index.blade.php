@extends('layouts.app')

@section('title', 'Dashboard')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">
            Selamat Datang, {{ auth()->user()->nama }} 👋
        </h1>
        <p class="text-xs text-gray-500 mt-1">
            Ringkasan operasional dan inventori Apotek Bintan 88 hari ini, {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}.
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if(in_array(auth()->user()->role, ['owner', 'apoteker', 'kasir']))
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buka Kasir POS</span>
            </a>
        @endif
        @if(in_array(auth()->user()->role, ['owner', 'admin_gudang', 'apoteker']))
            <a href="{{ route('pembelian.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-2xs transition-colors">
                <span>+ Faktur Masuk</span>
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Omset Hari Ini -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Omset Hari Ini</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs">💰</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-emerald-700 truncate">
                    Rp {{ number_format($omsetHariIni, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">{{ $transaksiHariIni }} Transaksi Selesai</div>
            </div>
        </div>

        <!-- Transaksi Kasir -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Transaksi</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs">🧾</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-gray-900">
                    {{ $transaksiHariIni }} Nota
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Penjualan hari ini</div>
            </div>
        </div>

        <!-- Stok Menipis -->
        <a href="{{ route('laporan.stokMenipis') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Stok Menipis</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs">⚠️</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-amber-600">
                    {{ $obatMenipis }} Obat
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Mencapai batas min</div>
            </div>
        </a>

        <!-- Stok Kosong -->
        <a href="{{ route('laporan.stokMenipis') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between hover:border-rose-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Stok Kosong</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs">🚨</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-rose-600">
                    {{ $obatKosong }} Obat
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Stok habis total (0)</div>
            </div>
        </a>

        <!-- Hampir Expired -->
        <a href="{{ route('laporan.expired') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between hover:border-purple-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Hampir ED</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600 text-xs">⏳</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-purple-600">
                    {{ $obatHampirExpired }} Batch
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Kedaluwarsa ≤ 30 hari</div>
            </div>
        </a>

        <!-- Hutang Jatuh Tempo -->
        <a href="{{ route('pembelian.hutang') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between hover:border-rose-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Hutang Tempo</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs">💳</span>
            </div>
            <div class="mt-3">
                <div class="text-base sm:text-lg font-bold font-mono text-rose-600 truncate">
                    Rp {{ number_format($hutangJatuhTempo, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Jatuh tempo ≤ 3 hari</div>
            </div>
        </a>
    </div>

    <!-- Charts Section (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Tren Penjualan 7 Hari Terakhir (8 Kolom) -->
        <div class="lg:col-span-8 bg-white p-5 rounded-3xl border border-gray-100 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Tren Omset Penjualan (7 Hari Terakhir)</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Perkembangan total nominal transaksi kasir yang berhasil</p>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">
                    Rupiah (IDR)
                </span>
            </div>
            <div class="h-64 relative">
                <canvas id="chartTrenPenjualan"></canvas>
            </div>
        </div>

        <!-- Chart 2: Top 5 Obat Terlaris (4 Kolom) -->
        <div class="lg:col-span-4 bg-white p-5 rounded-3xl border border-gray-100 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Top 5 Obat Terlaris</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Kuantitas produk paling diminati</p>
                </div>
                <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg">
                    Item
                </span>
            </div>
            <div class="h-64 relative">
                <canvas id="chartTopObat"></canvas>
            </div>
        </div>
    </div>

    <!-- Lower Section: Recent Sales & Alerts Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Transaksi Terbaru (7 Kolom) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Transaksi Kasir Terkini</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">5 transaksi terakhir yang diproses</p>
                </div>
                <a href="{{ route('penjualan.index') }}" class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                            <th class="py-2.5 px-4">No. Transaksi</th>
                            <th class="py-2.5 px-4">Kasir</th>
                            <th class="py-2.5 px-4 text-center">Jenis</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($recentPenjualans as $trx)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3 px-4 font-mono font-bold text-gray-900">
                                    <a href="{{ route('penjualan.show', $trx->id) }}" class="hover:underline hover:text-emerald-700">
                                        {{ $trx->no_transaksi }}
                                    </a>
                                    <div class="text-[10px] text-gray-400 font-sans">{{ $trx->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="py-3 px-4 text-gray-700">{{ $trx->user->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $trx->jenis_transaksi === 'resep' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ ucfirst($trx->jenis_transaksi) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                    Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $trx->status === 'selesai' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ ucfirst($trx->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada transaksi hari ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notifikasi & Peringatan Terkini (5 Kolom) -->
        <div class="lg:col-span-5 bg-white p-5 rounded-3xl border border-gray-100 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Pemberitahuan & Peringatan</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Notifikasi otomatis sistem & Telegram</p>
                    </div>
                    <span class="text-xs bg-slate-100 text-slate-700 font-bold px-2.5 py-0.5 rounded-full">
                        {{ count($recentNotif) }} Notifikasi
                    </span>
                </div>

                <div class="space-y-2.5">
                    @forelse($recentNotif as $notif)
                        <div class="p-3 rounded-2xl border border-gray-100 bg-slate-50/60 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900">{{ $notif->judul }}</span>
                                <span class="text-[10px] text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-600 text-[11px] leading-relaxed">{{ $notif->pesan }}</p>
                            @if($notif->link)
                                <a href="{{ $notif->link }}" class="text-[11px] text-emerald-600 font-semibold hover:underline inline-block pt-0.5">
                                    Tindak Lanjuti &rarr;
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic py-6 text-center">Tidak ada pemberitahuan baru.</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Action Info per Role -->
            <div class="pt-4 border-t border-gray-100 mt-3 flex items-center justify-between text-xs">
                <span class="text-gray-500 font-medium">Masuk sebagai: <strong class="text-gray-900">{{ ucfirst(auth()->user()->role) }}</strong></span>
                <a href="{{ route('laporan.penjualan') }}" class="text-emerald-600 font-semibold hover:underline">Semua Laporan &rarr;</a>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js CDN for visual rendering -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Chart Tren Penjualan 7 Hari
        const ctxTren = document.getElementById('chartTrenPenjualan')?.getContext('2d');
        if (ctxTren) {
            new Chart(ctxTren, {
                type: 'line',
                data: {
                    labels: @json($chartDates),
                    datasets: [{
                        label: 'Omset Penjualan (Rp)',
                        data: @json($chartSales),
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#059669',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value >= 1000000 ? (value / 1000000) + ' Jt' : (value / 1000) + ' Rb');
                                },
                                font: { size: 10 }
                            },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Chart Top 5 Obat
        const ctxTop = document.getElementById('chartTopObat')?.getContext('2d');
        if (ctxTop) {
            new Chart(ctxTop, {
                type: 'bar',
                data: {
                    labels: @json($topObatLabels),
                    datasets: [{
                        label: 'Jumlah Terjual',
                        data: @json($topObatValues),
                        backgroundColor: [
                            '#059669',
                            '#0d9488',
                            '#0284c7',
                            '#6366f1',
                            '#8b5cf6'
                        ],
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { font: { size: 10 } },
                            grid: { color: '#f1f5f9' }
                        },
                        y: {
                            ticks: {
                                font: { size: 10 },
                                callback: function(value) {
                                    const label = this.getLabelForValue(value);
                                    return label.length > 14 ? label.substr(0, 14) + '...' : label;
                                }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
