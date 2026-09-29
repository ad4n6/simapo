<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturPenjualan extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan';

    protected $fillable = [
        'no_retur',
        'penjualan_id',
        'user_id',
        'total_pengembalian',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'total_pengembalian' => 'decimal:2',
        ];
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReturPenjualanDetail::class, 'retur_penjualan_id');
    }
}
