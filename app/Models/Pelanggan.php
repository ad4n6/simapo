<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggan';

    protected $fillable = [
        'nama',
        'telepon',
        'alamat',
    ];

    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class);
    }
}
