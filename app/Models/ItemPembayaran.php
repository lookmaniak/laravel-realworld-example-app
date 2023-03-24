<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPembayaran extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function madrasah() {
        return $this->belongsTo(Madrasah::class, 'madrasah_id');
    }
    
    public function tagihan() {
        return $this->hasMany(Tagihan::class);
    }
    
    public function tahun_pelajaran() {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }
}   
