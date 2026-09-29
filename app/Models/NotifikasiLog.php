<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifikasiLog extends Model
{
    use HasFactory;

    protected $table = 'notifikasi_log';

    protected $fillable = [
        'jenis',
        'pesan',
        'tujuan',
        'status',
        'response_error',
        'entitas_tipe',
        'entitas_id',
    ];
}
