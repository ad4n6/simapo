<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'role_target',
        'judul',
        'pesan',
        'tipe',
        'link',
        'dibaca_at',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSudahDibacaAttribute(): bool
    {
        return $this->dibaca_at !== null;
    }
}
