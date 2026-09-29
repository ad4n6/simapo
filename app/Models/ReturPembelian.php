<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturPembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'retur_pembelian';

    protected $fillable = [
        'no_retur',
        'pembelian_id',
        'suplier_id',
        'user_id',
        'tanggal_retur',
        'total_nilai',
        'status',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_retur' => 'date',
            'total_nilai' => 'decimal:2',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function suplier(): BelongsTo
    {
        return $this->belongsTo(Suplier::class, 'suplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReturPembelianDetail::class, 'retur_pembelian_id');
    }
}
