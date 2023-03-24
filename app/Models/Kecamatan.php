<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;
    
    public function profil() {
        return $this->hasMany(ProfilSiswa::class, 'kecamatan_id');
    }
}
