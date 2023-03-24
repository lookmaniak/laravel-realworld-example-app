<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function madrasah() {
        return $this->belongsTo(Madrasah::class);
    }

    public function data_rombel() {
        return $this->hasMany(DataRombel::class);
    }
    
    public function siswa() {
        return $this->hasMany(DataRombel::class)->with('siswa');
    }
    
    public function rombel() {
        return $this->hasMany(DataRombel::class)->with('rombel');
    }
    
    public function tagihan() {
        return $this->hasMany(Tagihan::class)->with('item_pembayaran');
    }
}
