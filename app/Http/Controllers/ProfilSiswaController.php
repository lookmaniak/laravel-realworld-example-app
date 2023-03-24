<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provinsi;
use App\Models\Penghasilan;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;

class ProfilSiswaController extends Controller
{
    protected function index() {
        $judul = 'FORM PENDAFTARAN PESERTA DIDIK BARU';
        $fa = 'tasks';
        $data_provinsi = Provinsi::all();
        $data_penghasilan = Penghasilan::all();
        $data_pekerjaan = Pekerjaan::all();
        $data_pendidikan = Pendidikan::all();
        
        return view('data.ppdb.form_pendaftaran', compact('judul', 'fa', 'data_provinsi', 'data_penghasilan', 'data_pekerjaan', 'data_pendidikan'));
    }
    
    protected function simpan(Request $request) {
        //return dd($request->tanggal_lahir);
        $request->validate([
                'nama_lengkap' => 'required',
                'jenis_kelamin' => 'in:L,P',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required',
                'provinsi_id' => 'integer | required',
                'kecamatan_id' => 'integer | required',
                'kabupaten_id' => 'integer | required',
                'desa_id' => 'integer | required',
                'status_ibu' => 'in:1,2,3 | required',
                'status_ayah' => 'in:1,2,3 | required',
                'tanggal_lahir' => 'required',
                'pendidikan_ayah' => 'integer',
                'pendidikan_ibu' => 'integer',
                'penghasilan_id' => 'integer',
                'agama' => 'required',
            ], $request->all());
        $var = SiswaBaru::create($request->all());
        
        return dd($var);
    }
}
