<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Village;
use App\Models\Jenjang;
use App\Http\Resources\Api\ProvinceResourceCollection;
use App\Http\Resources\Api\CityResourceCollection;
use App\Http\Resources\Api\DistrictResourceCollection;
use App\Http\Resources\Api\VillageResourceCollection;
use App\Http\Resources\Api\ProfileResource;

class DataController extends Controller
{
    public function getProvinces(Request $request) {
        $profile = $request->user()->profile();

        if(empty($profile->province_id)) {

          return new ProvinceResourceCollection(Province::all());

        } else {

            return [
                'provinces' => new ProvinceResourceCollection(Province::all()),
                'cities' => new CityResourceCollection(City::where('province_id', $profile->province_id)->get()),
                'districts' => new DistrictResourceCollection(District::where('city_id', $profile->city_id)->get()),
                'villages' => new VillageResourceCollection(Village::where('district_id', $profile->district_id)->get()),
            ];

        }
    }

    public function getCities(Request $request) {
        return new CityResourceCollection(City::where('province_id', $request->province_id)->get());
    }

    public function getDistricts(Request $request) {
        return new DistrictResourceCollection(District::where('city_id', $request->city_id)->get());
    }

    public function getVillages(Request $request) {
        return new VillageResourceCollection(Village::where('district_id', $request->district_id)->get());
    }

    public function getJenjang(Request $request) {
        return response()->json(['jenjangs' => Jenjang::all()], 200);
    }

}
