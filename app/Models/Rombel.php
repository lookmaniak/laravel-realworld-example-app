<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rombel extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function data_rombel() {
        return $this->hasMany(DataRombel::class, 'rombel_id');
    }

    public function madrasah() {
        return $this->belongsTo(Madrasah::class);
    }

    public function tahun_pelajaran() {
        return $this->belongsTo(TahunPelajaran::class);
    }
}
