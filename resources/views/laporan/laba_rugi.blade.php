@extends('layouts.app')

@section('title', 'Laporan Laba Rugi Sederhana')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Laba Rugi Apotek (Sederhana)</h1>
        <p class="text-xs text-gray-500 mt-1">Perhitungan perolehan omset penjualan, potongan diskon, beban pokok penjualan (HPP), dan laba kotor apotek.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <span>Unduh PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('laporan.labaRugi') }}" class="flex flex-col sm:flex-row gap-3 text-xs">
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="py-2.5 px-6 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Hitung Laba Rugi
                </button>
            </div>
        </form>
    </div>

    <!-- Income Statement Presentation Card -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
        <div class="text-center border-b border-gray-100 pb-4">
            <h2 class="text-lg font-bold text-gray-900">APOTEK BINTAN 88</h2>
            <h3 class="text-sm font-semibold text-gray-600 mt-0.5">LAPORAN LABA RUGI KOTOR</h3>
            <p class="text-xs text-gray-400 mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="space-y-4 text-xs font-medium">
            <!-- Pendapatan Operasional -->
            <div class="space-y-2">
                <div class="font-bold text-gray-900 uppercase tracking-wider text-xs border-b border-gray-100 pb-1">1. Pendapatan Penjualan</div>
                <div class="flex justify-between py-1 text-gray-600 pl-4">
                    <span>Penjualan Obat Kotor (Gross Sales):</span>
                    <span class="font-mono font-bold text-gray-800">Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 text-rose-600 pl-4">
                    <span>Potongan Harga / Diskon Kasir:</span>
                    <span class="font-mono font-bold">- Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 text-emerald-800 bg-emerald-50/50 px-4 rounded-xl font-bold border border-emerald-100">
                    <span>Penjualan Bersih (Net Sales):</span>
                    <span class="font-mono text-sm">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Beban Pokok Penjualan -->
            <div class="space-y-2 pt-2">
                <div class="font-bold text-gray-900 uppercase tracking-wider text-xs border-b border-gray-100 pb-1">2. Harga Pokok Penjualan (HPP)</div>
                <div class="flex justify-between py-1 text-gray-600 pl-4">
                    <span>Total Biaya Beli Obat Terjual (FEFO Cost):</span>
                    <span class="font-mono font-bold text-gray-800">Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 text-slate-800 bg-slate-50 px-4 rounded-xl font-bold border border-slate-200">
                    <span>Total HPP:</span>
                    <span class="font-mono text-sm">Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Laba Kotor Akhir -->
            <div class="pt-4 border-t-2 border-emerald-500">
                <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-600 text-white shadow-md">
                    <div>
                        <div class="text-xs font-semibold text-emerald-100 uppercase">LABA KOTOR APOTEK (GROSS PROFIT)</div>
                        <div class="text-2xl font-bold font-mono mt-1">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-emerald-100">Gross Margin</div>
                        <div class="text-xl font-mono font-bold mt-1">{{ number_format($marginPersen, 1) }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
