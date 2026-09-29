<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suplier';

    protected $fillable = [
        'nama',
        'kontak_person',
        'telepon',
        'email',
        'alamat',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class);
    }

    public function batchObats(): HasMany
    {
        return $this->hasMany(BatchObat::class);
    }

    public function returPembelians(): HasMany
    {
        return $this->hasMany(ReturPembelian::class);
    }
}
