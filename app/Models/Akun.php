<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function anak() {
        return $this->hasMany(Akun::class, 'akun_induk');
    }
    
    public function induk() {
        return $this->belongsTo(Akun::class, 'akun_induk');
    }
    
    public function pos_kredit() {
        return $this->hasMany(JurnalInOut::class, 'kredit_akun_id');
    }
    
    public function pos_debit() {
        return $this->hasMany(JurnalInOut::class, 'debit_akun_id');
    }
}
