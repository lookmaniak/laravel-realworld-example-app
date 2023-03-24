<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;
    protected $guarded = [];
   
    public function madrasah() {
        return $this->belongsTo(Madrasah::class);
    }
    
    public function pembayaran() {
        return $this->hasMany(Pembayaran::class);
    }
    public function profil() {
        return $this->hasOne(ProfilSiswa::class);
    }
    
    public function profil_siswa() {
        return $this->hasOne(ProfilSiswa::class);
    }

    public function akun_jajan() {
        return $this->hasOne(AkunJajan::class, 'siswa_id');
    }
    
    public function jurnal_jajan() {
        return $this->hasMany(JurnalJajan::class, 'siswa_id');
    }

    public function tagihan() {
        return $this->hasMany(Tagihan::class);
    }

    public function data_rombel() {
        return $this->hasMany(DataRombel::class);
    }
    
    public function rombel_saat_ini() {
        return $this->hasOne(DataRombel::class)->where('is_deleted', false)->with('rombel')->latest();
    }
    
    public function kelengkapan_profil() {
        if($this->profil) {
            $count = 0;
            $null = 0;
            foreach($this->profil->toArray() as $key => $value) {
                if(empty($value) || $value == '-') {
                    $null++;
                }
                $count++;
            }
            return intval((($count - $null)/$count) * 100);
        } else {
            return 0;
        }
    }
    
    public function kelengkapan_berkas() {
        $data = [   
                    'fc_kk',
                    'fc_ktp_ayah',
                    'fc_ktp_ibu',
                    'fc_akte',
                    'fc_skhun',
                    'fc_ijazah',
                    'skl',
                    'skkb',
                    'fc_kip',
                    'fc_pkh',
                    'fc_kis',
                    'pas_foto'
                ];
                
      if($this->profil) {
            $kurang = '';
            $val = $this->profil->toArray();
            
            foreach($data as $d) {
                
                if(($val[$d.'_min'] - $val[$d]) > 0) {
                    if($d == 'fc_kip' || $d == 'fc_pkh' || $d == 'fc_kis'){
                        if($this->profil->penghasilan_id < 3) {
                            $kurang .= ' <span class="badge badge-danger">'.$d.' '.($val[$d.'_min'] - $val[$d]).'</span> ';
                        }
                    } else {
                        $kurang .= ' <span class="badge badge-danger">'.$d.' '.($val[$d.'_min'] - $val[$d]).'</span> ';
                    }
                }
                
            }
            
            return $kurang;
        } else {
            return 0;
        }  
    }
}
