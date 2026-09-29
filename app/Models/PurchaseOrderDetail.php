<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_detail';

    protected $fillable = [
        'purchase_order_id',
        'obat_id',
        'jumlah_dipesan',
        'jumlah_diterima',
        'harga_satuan_perkiraan',
        'subtotal_perkiraan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_dipesan' => 'integer',
            'jumlah_diterima' => 'integer',
            'harga_satuan_perkiraan' => 'decimal:2',
            'subtotal_perkiraan' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
