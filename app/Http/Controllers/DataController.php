<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Jenjang;

class DataController extends Controller
{
    //
    protected function jenjang() {
        return response()->json(['jenjang' => Jenjang::all()], 200);
    }

    protected function kabupaten(Request $request) {
        $data_kabupaten = Kabupaten::where('provinsi_id', $request->id)->get();
        $kabupaten_options = '<option selected>Pilih nama kabupaten/kota</option>';
        foreach($data_kabupaten as $kabupaten) {
            $kabupaten_options = $kabupaten_options.'<option value="'.$kabupaten->id.'">'.$kabupaten->nama.'</option>';
        }
        
        return response()->json(['status' => true, 'pesan' => 'success!', 'data' => $kabupaten_options], 200);
    }
    
    protected function kecamatan(Request $request) {
        $data_kecamatan = Kecamatan::where('kabupaten_id', $request->id)->get();
        $kecamatan_options = '<option selected>Pilih nama kecamatan</option>';
        foreach($data_kecamatan as $kecamatan) {
            $kecamatan_options = $kecamatan_options.'<option value="'.$kecamatan->id.'">'.$kecamatan->nama.'</option>';
        }
        
        return response()->json(['status' => true, 'pesan' => 'success!', 'data' => $kecamatan_options], 200);
    }
    
    protected function desa(Request $request) {
        $data_desa = Desa::where('kecamatan_id', $request->id)->get();
        $desa_options = '<option selected>Pilih nama desa</option>';
        foreach($data_desa as $desa) {
            $desa_options = $desa_options.'<option value="'.$desa->id.'">'.$desa->nama.'</option>';
        }
        
        return response()->json(['status' => true, 'pesan' => 'success!', 'data' => $desa_options], 200);
    }
}
