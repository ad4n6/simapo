<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'no_transaksi',
        'user_id',
        'shift_kasir_id',
        'pelanggan_id',
        'resep_id',
        'jenis_transaksi',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'total_akhir',
        'metode_pembayaran',
        'jumlah_bayar',
        'kembalian',
        'status',
        'void_alasan',
        'void_user_id',
        'void_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'diskon_persen' => 'decimal:2',
            'diskon_nominal' => 'decimal:2',
            'total_akhir' => 'decimal:2',
            'jumlah_bayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
            'void_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_user_id');
    }

    public function shiftKasir(): BelongsTo
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_kasir_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function resep(): BelongsTo
    {
        return $this->belongsTo(Resep::class, 'resep_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public function returs(): HasMany
    {
        return $this->hasMany(ReturPenjualan::class, 'penjualan_id');
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
