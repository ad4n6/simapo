<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dokter';

    protected $fillable = [
        'nama',
        'no_sip',
        'spesialis',
        'telepon',
        'alamat',
    ];

    public function reseps(): HasMany
    {
        return $this->hasMany(Resep::class);
    }
}
