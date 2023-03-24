<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProfilSiswa extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function umur() {
        return Carbon::parse($this->tanggal_lahir)->age;
    }
    
    public function provinsi() {
        return $this->belongsTo(Provinsi::class);
    }
    
    public function kabupaten() {
        return $this->belongsTo(Kabupaten::class);
    }
    
    public function kecamatan() {
        return $this->belongsTo(Kecamatan::class);
    }
    
    public function desa() {
        return $this->belongsTo(Desa::class);
    }
    
    public function siswa() {
        return $this->belongsTo(Siswa::class);
    }
    
    public function ayah_pendidikan() {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_ayah');
    }
    
    public function umur_ayah() {
        return Carbon::parse($this->tanggal_lahir_ayah)->age;
    }
    
    public function ayah_pekerjaan() {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_ayah');
    }
    
    public function ayah_status() {
        if(!isset($this->status_ayah)) return '';
        
        switch($this->status_ayah) {
            case 1:
                return 'Masih Hidup';
                break;
            case 2:
                return 'Sudah Meninggal';
                break;
            default:
                return 'Tidak Diketahui';
        }
    }
    
    public function ibu_pendidikan() {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_ibu');
    }
    
    public function umur_ibu() {
        return Carbon::parse($this->tanggal_lahir_ibu)->age;
    }
    
    public function ibu_pekerjaan() {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_ibu');
    }
    
    public function ibu_status() {
        switch($this->status_ibu) {
            case 1:
                return 'Masih Hidup';
                break;
            case 2:
                return 'Sudah Meninggal';
                break;
            default:
                return 'Tidak Diketahui';
        }
    }
    
    public function penghasilan_orangtua() {
        return $this->belongsTo(Penghasilan::class, 'penghasilan_id');
    }
}
