<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_order';

    protected $fillable = [
        'no_po',
        'suplier_id',
        'user_id',
        'tanggal_po',
        'estimasi_tanggal_terima',
        'total_perkiraan',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_po' => 'date',
            'estimasi_tanggal_terima' => 'date',
            'total_perkiraan' => 'decimal:2',
        ];
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
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id');
    }

    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'purchase_order_id');
    }
}
