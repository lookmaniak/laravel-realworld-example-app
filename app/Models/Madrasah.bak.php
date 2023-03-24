<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasah extends Model
{
    use HasFactory;

    protected $fillable = [
        'alamat',
        'jenjang_id',
        'kode_jenjang',
        'nama',
        'nama_kepsek',
        'npsn',
    ];
}
