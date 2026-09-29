<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategori';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    public function obats(): HasMany
    {
        return $this->hasMany(Obat::class, 'kategori_id');
    }
}
