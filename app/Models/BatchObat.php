<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatchObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'batch_obat';

    protected $fillable = [
        'obat_id',
        'suplier_id',
        'no_batch',
        'tanggal_kedaluwarsa',
        'jumlah_masuk',
        'jumlah_saat_ini',
        'harga_beli',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kedaluwarsa' => 'date',
            'jumlah_masuk' => 'integer',
            'jumlah_saat_ini' => 'integer',
            'harga_beli' => 'decimal:2',
        ];
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function suplier(): BelongsTo
    {
        return $this->belongsTo(Suplier::class, 'suplier_id');
    }

    public function stokMutasis(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'batch_obat_id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return Carbon::parse($this->tanggal_kedaluwarsa)->lte(Carbon::today());
    }

    public function getHariMenujuExpiredAttribute(): int
    {
        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_kedaluwarsa), false);
    }

    public function getStatusKedaluwarsaAttribute(): string
    {
        $days = $this->hari_menuju_expired;
        if ($days <= 0) {
            return 'kedaluwarsa';
        }
        if ($days <= 30) {
            return 'kritis_30';
        }
        if ($days <= 60) {
            return 'peringatan_60';
        }
        if ($days <= 90) {
            return 'waspada_90';
        }

        return 'aman';
    }
}
