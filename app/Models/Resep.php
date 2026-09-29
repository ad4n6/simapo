<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    use HasFactory;

    protected $table = 'resep';

    protected $fillable = [
        'no_resep',
        'dokter_id',
        'nama_dokter',
        'nama_pasien',
        'tanggal_resep',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_resep' => 'date',
        ];
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'resep_id');
    }
}
