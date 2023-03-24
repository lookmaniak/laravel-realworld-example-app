<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasah extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tahun_pelajaran() {
        return $this->hasMany(TahunPelajaran::class, 'madrasah_id');
    }

    public function data_rombel() {
        return $this->hasMany(DataRombel::class, 'madrasah_id');
    }

    public function jenjang() {
        return $this->belongsTo(Jenjang::class);
    }
    
    public function siswa() {
        return $this->hasMany(Siswa::class, 'madrasah_id');
    }

    public function item_pembayaran() {
        return $this->hasMany(ItemPembayaran::class, 'madrasah_id');
    }
}
