<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'obat';

    protected $fillable = [
        'kode',
        'nama',
        'nama_generik',
        'kategori_id',
        'satuan_id',
        'harga_beli',
        'harga_jual',
        'stok_minimum',
        'lokasi_rak',
        'butuh_resep',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok_minimum' => 'integer',
            'butuh_resep' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(BatchObat::class, 'obat_id');
    }

    /**
     * Active available batches ordered by FEFO (First Expired First Out)
     */
    public function availableBatches(): HasMany
    {
        return $this->hasMany(BatchObat::class, 'obat_id')
            ->where('tanggal_kedaluwarsa', '>', Carbon::today())
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc');
    }

    public function stokMutasis(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'obat_id');
    }

    public function getTotalStokAttribute(): int
    {
        // Only non-expired stock is sellable and counts towards available stock
        return (int) $this->batches()
            ->where('tanggal_kedaluwarsa', '>', Carbon::today())
            ->sum('jumlah_saat_ini');
    }

    public function getAllStokAttribute(): int
    {
        return (int) $this->batches()->sum('jumlah_saat_ini');
    }

    public function getExpiredStokAttribute(): int
    {
        return (int) $this->batches()
            ->where('tanggal_kedaluwarsa', '<=', Carbon::today())
            ->sum('jumlah_saat_ini');
    }

    public function getStatusStokAttribute(): string
    {
        $stok = $this->total_stok;
        if ($stok <= 0) {
            return 'kosong';
        }
        if ($stok <= $this->stok_minimum) {
            return 'menipis';
        }

        return 'aman';
    }

    public function getStatusKedaluwarsaAttribute(): string
    {
        $batches = $this->batches()->where('jumlah_saat_ini', '>', 0)->get();
        if ($batches->isEmpty()) {
            return 'tidak_ada_stok';
        }

        $today = Carbon::today();
        $hasExpired = false;
        $hasNearExpired = false;

        foreach ($batches as $batch) {
            $expDate = Carbon::parse($batch->tanggal_kedaluwarsa);
            if ($expDate->lte($today)) {
                $hasExpired = true;
            } elseif ($expDate->diffInDays($today) <= 90) {
                $hasNearExpired = true;
            }
        }

        if ($hasExpired) {
            return 'kedaluwarsa';
        }
        if ($hasNearExpired) {
            return 'hampir_kedaluwarsa';
        }

        return 'aman';
    }

    public function scopeMenipis(Builder $query): Builder
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('tanggal_kedaluwarsa', '>', Carbon::today());
        }, '>', 0);
    }
}
