<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalInOut extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected function madrasah() {
        return $this->belongsTo(Madrasah::class);
    }
    
    protected function tahun_anggaran() {
        return $this->belongsTo(TahunAnggaran::class);
    }
    
    protected function kegiatan() {
        return $this->belongsTo(Kegiatan::class);
    }
    
    protected function akun_debit() {
        return $this->belongsTo(Akun::class, 'debit_akun_id');
    }
    
    protected function akun_kredit() {
        return $this->belongsTo(Akun::class, 'kredit_akun_id');
    }
    
    protected function user() {
        return $this->belongsTo(User::class);
    }
}
