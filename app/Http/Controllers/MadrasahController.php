<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Madrasah;
use App\Models\Jenjang;

class MadrasahController extends Controller
{
    protected function index() {
        $data = \Auth::user()->madrasah()->with('jenjang')->get();//Madrasah::with('jenjang')->get();

        return response()->json([ 'madrasahs' => $data], 200);
    }

    protected function create(Request $request) {
        $request->validate([
            'nama' => 'max:255 | required',
            'kode_jenjang' => 'max:32 | required',
            'npsn' => 'max:19',
	        'jenjang_id' => 'integer | required',
            'alamat' => 'max:1000',
            'nama_kepsek' => 'max:255 | required',
        ]);

        $data = $request->only([
            'nama',
            'kode_jenjang',
            'npsn',
            'jenjang_id',
            'alamat',
            'nama_kepsek',
        ]);
        $data['user_id'] = \Auth::user()->id;

        $data = Madrasah::create($data);
        $data['jenjang'] = $data->jenjang;

        return response()->json(['madrasah' => $data], 200);
    }

    protected function update(Request $request) {
        $request->validate([
            'nama' => 'max:255 | required',
            'kode_jenjang' => 'max:32 | required',
            'npsn' => 'max:19',
	        'jenjang_id' => 'integer | required',
            'alamat' => 'max:1000',
            'nama_kepsek' => 'max:255 | required',
        ], $request->all());

        $data = \Auth::user()->madrasah()->where('id', $request->id)->first();
        $data->update([
            'nama' => $request->nama,
            'kode_jenjang' => $request->kode_jenjang,
            'npsn' => $request->npsn,
	        'jenjang_id' => $request->jenjang_id,
            'alamat' => $request->alamat,
            'nama_kepsek' => $request->nama_kepsek,
        ]);
        $data->jenjang = $request->jenjang;

        return response()->json(['madrasah' => $data], 200);
    }

    protected function delete(Request $request) {
        $data = \Auth::user()->madrasah()->where('id', $request->id)->first();
        
        if(!$data) return response()->json(['code' => 402, 'message' => 'Data not found!' , 'errors' => (Object)[]], 402);

        $data->is_deleted = true;
        $data->deleted_by = \Auth::user()->id;
        $data->deleted_at = date("Y-m-d H:i:s");
        $data->save();
        
        return response()->json(['madrasah' => $data], 200);
    }
}
