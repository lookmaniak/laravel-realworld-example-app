<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahun_pelajaran() {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function item_pembayaran() {
        return $this->belongsTo(ItemPembayaran::class, 'item_pembayaran_id');
    }

    public function pembayaran() {
        return $this->hasMany(JurnalPembayaran::class);
    }

   /*  public function pembayaran() {
        return $this->hasMany(Pembayaran::class);
    } */
}
