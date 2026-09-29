<?php

namespace App\Services;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\StokMutasi;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class StokService
{
    /**
     * Kurangi stok obat menggunakan metode FEFO (First Expired First Out).
     * Batch yang sudah kedaluwarsa tidak boleh dijual/dikurangi untuk penjualan!
     *
     * @return array<int, array{batch_id: int, no_batch: string, qty: int, harga_beli: float}>
     *
     * @throws Exception
     */
    public function kurangiStokFEFO(
        Obat $obat,
        int $qtyDibutuhkan,
        string $referensiTipe,
        int $referensiId,
        ?int $userId = null,
        string $keterangan = ''
    ): array {
        $today = Carbon::today();

        // Ambil batch yang masih aktif, belum expired, dan memiliki stok > 0, urut tanggal expired terdekat
        $batches = BatchObat::where('obat_id', $obat->id)
            ->where('tanggal_kedaluwarsa', '>', $today)
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->lockForUpdate()
            ->get();

        $totalTersedia = $batches->sum('jumlah_saat_ini');
        if ($totalTersedia < $qtyDibutuhkan) {
            throw new Exception("Stok obat {$obat->nama} tidak mencukupi (Tersedia: {$totalTersedia}, Diminta: {$qtyDibutuhkan}).");
        }

        $sisaKebutuhan = $qtyDibutuhkan;
        $batchDeductions = [];

        foreach ($batches as $batch) {
            if ($sisaKebutuhan <= 0) {
                break;
            }

            $diambil = min($batch->jumlah_saat_ini, $sisaKebutuhan);
            $saldoSebelum = $batch->jumlah_saat_ini;
            $saldoSesudah = $saldoSebelum - $diambil;

            $batch->jumlah_saat_ini = $saldoSesudah;
            $batch->save();

            // Catat ke kartu stok (stok_mutasi)
            StokMutasi::create([
                'obat_id' => $obat->id,
                'batch_obat_id' => $batch->id,
                'user_id' => $userId,
                'jenis' => 'keluar',
                'jumlah' => $diambil,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'keterangan' => $keterangan ?: "Pengurangan FEFO Batch {$batch->no_batch}",
            ]);

            $batchDeductions[] = [
                'batch_id' => $batch->id,
                'no_batch' => $batch->no_batch,
                'qty' => $diambil,
                'harga_beli' => (float) $batch->harga_beli,
            ];

            $sisaKebutuhan -= $diambil;
        }

        return $batchDeductions;
    }

    /**
     * Tambah stok obat ke batch (dari pembelian / penerimaan barang).
     */
    public function tambahStokBatch(
        Obat $obat,
        string $noBatch,
        string $tanggalKedaluwarsa,
        int $qty,
        float $hargaBeli,
        ?int $suplierId = null,
        string $referensiTipe = 'PenerimaanBarang',
        int $referensiId = 0,
        ?int $userId = null,
        string $keterangan = ''
    ): BatchObat {
        return DB::transaction(function () use (
            $obat,
            $noBatch,
            $tanggalKedaluwarsa,
            $qty,
            $hargaBeli,
            $suplierId,
            $referensiTipe,
            $referensiId,
            $userId,
            $keterangan
        ) {
            // Cek apakah batch sudah ada untuk obat ini
            $batch = BatchObat::where('obat_id', $obat->id)
                ->where('no_batch', $noBatch)
                ->first();

            if ($batch) {
                $saldoSebelum = $batch->jumlah_saat_ini;
                $batch->jumlah_masuk += $qty;
                $batch->jumlah_saat_ini += $qty;
                $batch->harga_beli = $hargaBeli;
                $batch->tanggal_kedaluwarsa = $tanggalKedaluwarsa;
                if ($suplierId) {
                    $batch->suplier_id = $suplierId;
                }
                $batch->save();
            } else {
                $saldoSebelum = 0;
                $batch = BatchObat::create([
                    'obat_id' => $obat->id,
                    'suplier_id' => $suplierId,
                    'no_batch' => $noBatch,
                    'tanggal_kedaluwarsa' => $tanggalKedaluwarsa,
                    'jumlah_masuk' => $qty,
                    'jumlah_saat_ini' => $qty,
                    'harga_beli' => $hargaBeli,
                ]);
            }

            // Update harga beli acuan obat
            $obat->harga_beli = $hargaBeli;
            $obat->save();

            // Catat kartu stok
            StokMutasi::create([
                'obat_id' => $obat->id,
                'batch_obat_id' => $batch->id,
                'user_id' => $userId,
                'jenis' => 'masuk',
                'jumlah' => $qty,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $batch->jumlah_saat_ini,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'keterangan' => $keterangan ?: "Penerimaan Batch {$batch->no_batch}",
            ]);

            return $batch;
        });
    }

    /**
     * Kembalikan stok ke batch (misal saat transaksi penjualan di-void atau ada retur).
     */
    public function kembalikanStokBatch(
        int $batchId,
        int $qty,
        string $referensiTipe,
        int $referensiId,
        ?int $userId = null,
        string $keterangan = ''
    ): void {
        $batch = BatchObat::lockForUpdate()->find($batchId);
        if (! $batch) {
            return;
        }

        $saldoSebelum = $batch->jumlah_saat_ini;
        $batch->jumlah_saat_ini += $qty;
        $batch->save();

        StokMutasi::create([
            'obat_id' => $batch->obat_id,
            'batch_obat_id' => $batch->id,
            'user_id' => $userId,
            'jenis' => ($referensiTipe === 'ReturPenjualan') ? 'retur_penjualan' : 'penyesuaian',
            'jumlah' => $qty,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $batch->jumlah_saat_ini,
            'referensi_tipe' => $referensiTipe,
            'referensi_id' => $referensiId,
            'keterangan' => $keterangan ?: "Pengembalian stok Batch {$batch->no_batch}",
        ]);
    }
}
