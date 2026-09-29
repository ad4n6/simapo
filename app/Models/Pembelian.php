<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembelian';

    protected $fillable = [
        'no_faktur',
        'purchase_order_id',
        'suplier_id',
        'user_id',
        'tanggal_faktur',
        'tanggal_jatuh_tempo',
        'jenis_pembayaran',
        'status_pembayaran',
        'subtotal',
        'total_akhir',
        'total_terbayar',
        'sisa_hutang',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_faktur' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'subtotal' => 'decimal:2',
            'total_akhir' => 'decimal:2',
            'total_terbayar' => 'decimal:2',
            'sisa_hutang' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
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
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }

    public function pembayaranHutangs(): HasMany
    {
        return $this->hasMany(PembayaranHutang::class, 'pembelian_id');
    }

    public function returPembelians(): HasMany
    {
        return $this->hasMany(ReturPembelian::class, 'pembelian_id');
    }

    public function getIsHutangAttribute(): bool
    {
        return $this->jenis_pembayaran === 'kredit' && $this->status_pembayaran === 'belum_lunas' && $this->sisa_hutang > 0;
    }

    public function getHariMenujuJatuhTempoAttribute(): ?int
    {
        if (! $this->tanggal_jatuh_tempo) {
            return null;
        }

        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_jatuh_tempo), false);
    }
}
