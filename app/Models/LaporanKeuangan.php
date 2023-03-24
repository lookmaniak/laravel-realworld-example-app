<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKeuangan extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function harta() {
        return $this->hasOne(Akun::class, 'harta');
    }
    
    public function kewajiban() {
        return $this->hasOne(Akun::class, 'kewajiban');
    }
    
    public function modal() {
        return $this->hasOne(Akun::class, 'modal');
    }
    
    public function pendapatan() {
        return $this->hasOne(Akun::class, 'pendapatan');
    }
    
    public function biaya() {
        return $this->hasOne(Akun::class, 'biaya');
    }
}
