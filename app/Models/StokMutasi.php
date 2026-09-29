<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMutasi extends Model
{
    use HasFactory;

    protected $table = 'stok_mutasi';

    protected $fillable = [
        'obat_id',
        'batch_obat_id',
        'user_id',
        'jenis',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'referensi_tipe',
        'referensi_id',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'saldo_sebelum' => 'integer',
            'saldo_sesudah' => 'integer',
        ];
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function batchObat(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
