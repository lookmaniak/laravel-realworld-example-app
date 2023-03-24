<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunJajan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
    
    public function jurnal() {
        return $this->hasMany(JurnalJajan::class);
    }
}
