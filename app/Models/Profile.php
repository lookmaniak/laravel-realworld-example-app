<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'address',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'postal_code',
        'no_kontak',
    ];

    public function province() {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district() {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function city() {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function village() {
        return $this->belongsTo(Village::class, 'village_id');
    }
}
