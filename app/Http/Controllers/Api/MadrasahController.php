<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Madrasah;
use App\Http\Resources\Api\MadrasahResourceCollection;
use App\Http\Resources\Api\MadrasahResource;
use App\Http\Requests\Api\CreateMadrasahRequest;

class MadrasahController extends Controller
{
    public function index(Request $r)
    {
        $madrasahs = Madrasah::where('user_id', \Auth::user()->id)->get();

        return new MadrasahResourceCollection($madrasahs);
    }

    public function create(CreateMadrasahRequest $r) {

        return new MadrasahResource($r->madrasah);
    }

    public function update(CreateMadrasahRequest $r) {

        return response()->json($r->madrasah, 200);//new MadrasahResource($r->madrasah);
    }

}
