<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRombel extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function madrasah() {
        return $this->belongsTo(Madrasah::class);
    }

    public function tahun_pelajaran() {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function rombel() {
        return $this->belongsTo(Rombel::class);
    }

    public function siswa() {
        return $this->belongsTo(Siswa::class);
    }

}
