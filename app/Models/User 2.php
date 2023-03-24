<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function jenis_akun() {
        switch ($this->level) {
            case 0:
                return 'Admin';
                break;
            case 1:
                return 'Teller';
                break;
            case 2:
                return 'Client';
                break;
            case 3:
                return 'Staff TU';
                break;
            case 4:
                return 'Staff Keuangan';
                break;
            default:
                return 'Unknown';
                break;
        }
    }
    
    public function jurnal_jajan() {
        return $this->hasMany(JurnalJajan::class);
    }
    
    public function profil() {
        return $this->hasOne(Profile::class);
    }
    
}
