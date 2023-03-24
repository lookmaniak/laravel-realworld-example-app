<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $guarded =[];
    
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function closer() {
        return $this->belongsTo(User::class, 'closed_by');
    }
    
    public function target() {
        switch($this->target) {
            case 99:
                return 'Public';
                break;
            case 0:
                return 'Admin PPDB';
                break;
            case 1:
                return 'Admin Aplikasi';
                break;
            case 2:
                return 'Staff Keuangan';
                break;
            case 3:
                return 'Staff Uang Tabungan';
                break;
            case 4:
                return 'Staff Tata Usaha';
                break;
            default:
                return 'Not found';
                break;
                
        }
    }
}
