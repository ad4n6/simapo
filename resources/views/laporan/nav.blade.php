@php
    $currentRoute = request()->route()->getName();
    $reports = [
        ['route' => 'laporan.penjualan', 'label' => '💰 Penjualan', 'roles' => ['owner', 'apoteker']],
        ['route' => 'laporan.penjualanObat', 'label' => '🏆 Obat Terlaris', 'roles' => ['owner', 'apoteker']],
        ['route' => 'laporan.resep', 'label' => '📋 Obat Resep', 'roles' => ['owner', 'apoteker']],
        ['route' => 'laporan.pembelian', 'label' => '🚚 Pembelian & Hutang', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.stok', 'label' => '📦 Nilai Persediaan', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.stokMenipis', 'label' => '⚠️ Stok Menipis/Kosong', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.expired', 'label' => '⏳ Obat Kedaluwarsa', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.retur', 'label' => '↩️ Retur Barang', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.mutasiStok', 'label' => '📑 Mutasi Kartu Stok', 'roles' => ['owner', 'apoteker', 'admin_gudang']],
        ['route' => 'laporan.labaRugi', 'label' => '📈 Laba Rugi', 'roles' => ['owner']],
        ['route' => 'laporan.activityLog', 'label' => '🛡️ Log Aktivitas', 'roles' => ['owner']],
    ];
    $userRole = auth()->user()->role;
@endphp

<div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-xs mb-6 overflow-x-auto">
    <div class="flex items-center gap-1.5 min-w-max text-xs font-semibold">
        @foreach($reports as $r)
            @if(in_array($userRole, $r['roles']))
                @php $isActive = $currentRoute === $r['route']; @endphp
                <a href="{{ route($r['route']) }}"
                    class="px-3 py-2 rounded-xl transition-all {{ $isActive ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    {{ $r['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
