<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan_detail';

    protected $fillable = [
        'retur_penjualan_id',
        'obat_id',
        'batch_obat_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'kembalikan_ke_stok',
        'kondisi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'kembalikan_ke_stok' => 'boolean',
        ];
    }

    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'retur_penjualan_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function batchObat(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }
}
