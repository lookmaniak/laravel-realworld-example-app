<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jurnal_pembayaran() {
        return $this->hasMany(JurnalPembayaran::class);
    }

    public function tagihan() {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'kasir_id');
    }
    
    public function rombel() {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }
}
