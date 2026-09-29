<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftKasir extends Model
{
    use HasFactory;

    protected $table = 'shift_kasir';

    protected $fillable = [
        'user_id',
        'waktu_buka',
        'waktu_tutup',
        'modal_awal',
        'total_penjualan_tunai',
        'total_penjualan_non_tunai',
        'uang_fisik_akhir',
        'selisih',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_buka' => 'datetime',
            'waktu_tutup' => 'datetime',
            'modal_awal' => 'decimal:2',
            'total_penjualan_tunai' => 'decimal:2',
            'total_penjualan_non_tunai' => 'decimal:2',
            'uang_fisik_akhir' => 'decimal:2',
            'selisih' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'shift_kasir_id');
    }
}
