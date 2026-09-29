<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_detail';

    protected $fillable = [
        'retur_pembelian_id',
        'obat_id',
        'batch_obat_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_pembelian_id');
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
