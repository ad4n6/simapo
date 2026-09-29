<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemusnahanObat extends Model
{
    use HasFactory;

    protected $table = 'pemusnahan_obat';

    protected $fillable = [
        'no_pemusnahan',
        'user_id',
        'obat_id',
        'batch_obat_id',
        'tanggal',
        'jumlah',
        'alasan',
        'saksi',
        'metode_pemusnahan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
