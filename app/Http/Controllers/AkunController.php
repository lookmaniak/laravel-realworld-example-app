<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;

class AkunController extends Controller
{
    public function kode_baru($id) {
        if($id == 0) {
            $anak_induk = Akun::where('akun_induk', $id)->get();
            $min = 1000;
            $max = 9000;
            $step = 1000;
        } else {
            $induk = Akun::where('id', $id)->with('anak_akun')->first();
            
            if(!$induk) return 'notfound';
            
            $anak_induk = $induk->anak_akun;
            if(($induk->kode % 1000) < 10) {
                $min = ($induk->kode - ($induk->kode % 1000)) + 100;
                $max = ($induk->kode - ($induk->kode % 1000)) + 900;
                $step = 100;
            } else {
                $min = ($induk->kode - ($induk->kode % 100)) + 1;
                $max = ($induk->kode - ($induk->kode % 100)) + 99;
                $step = 1;
            }
            
        }
        
        foreach($anak_induk as $key => $value) {
            while($min < $value->kode) {
                $min += $step;
            }
            $min += $step;
        }
        
        return $min;
    }
    
    public function get_kode($id) {
        if($id == 0) {
            $anak_akun = Akun::where('akun_induk', $id)->orderBy('kode', 'asc')->get();
            $min = 1000;
            $max = 9000;
            $step = 1000;
        } else {
            $akun_induk = Akun::find($id);
            $anak_akun = $akun_induk->anak->sortBy('id');            
            if(intval($akun_induk->kode[1]) == 0) {
                $min = (intval($akun_induk->kode[0]) * 1000) + 100;
                $max = (intval($akun_induk->kode[0]) * 1000) + 900;
                $step = 100;
            } else if(intval($akun_induk->kode[3]) > 0) {
                return response()->json(['status' => false, 'kode' => 'Akun yang anda pilih tidak dapat dijadikan induk!'], 200);
            } else {
                $min = (intval($akun_induk->kode[0]) * 1000) + (intval($akun_induk->kode[1]) * 100) + 1;
                $max = (intval($akun_induk->kode[0]) * 1000) + (intval($akun_induk->kode[1]) * 100) + 99;
                $step = 1;
            }
        }
        
        $val = Array();
        
        foreach($anak_akun as $akun) {
            $val[] = $akun->kode;
        }
        
        for($min; $min <= $max; $min += $step) {
            if(!in_array($min, $val)) {
                return response()->json(['status' => true, 'kode' => $min], 200);
            }
        }
        
        return response()->json(['status' => true, 'kode' => 'Akun yang anda pilih tidak dapat dijadikan induk!'], 200);
    }
    
    protected function daftar(Request $request) {
       
        $judul = 'DAFTAR KODE AKUN';
        $fa = 'asterisk';
        $data = Akun::orderBy('kode')->get();
        $parent_saat_ini = $this->kode_baru($request->id);
        
        return view('data.akun.daftar_akun', compact('judul', 'fa', 'data', 'parent_saat_ini'));
    }
    
    protected function simpan(Request $request) {
        $request->validate([
                'nama' => 'string:max(255) | required',
                'kode' => 'integer | required',
            ], $request->all());
            
        $res = Akun::create([
                'nama' => $request->nama,
                'kode' => $request->kode,
                'akun_induk' => $request->akun_induk,
            ]);
        
        return redirect(route('daftar_kode_akun'))->with('success', 'Kode Akun berhasil disimpan!');
    }
    
    protected function edit(Request $request) {
        if($request->id < 6) return redirect()->back()->with('error', 'Akun tidak dapat diedit!');
        
        $judul = 'EDIT KODE AKUN';
        $fa = 'asterisk';
        $data = Akun::find($request->id);
        $data_akun = Akun::orderBy('kode')->get();
        
        return view('data.akun.edit_akun', compact('judul', 'fa', 'data', 'data_akun'));
    }
    
    protected function update(Request $request) {
        if($request->id < 6) return redirect()->back()->with('error', 'Akun tidak dapat diedit!');
        
        $request->validate([
                'nama' => 'string:max(255) | required',
                'kode' => 'integer | required',
            ], $request->all());
            
        $data = Akun::find($request->id);
        $data->nama = $request->nama;
        $data->kode = $request->kode;
        $data->akun_induk = $request->akun_induk;
        $data->save();
        
        return redirect(route('daftar_kode_akun'))->with('success', 'Kode Akun berhasil diperbarui!');
    }
    
    protected function hapus(Request $request) {
        if($request->id < 6) return redirect()->back()->with('error', 'Akun tidak dapat dihapus!');
        
        $data = Akun::findOrFail($request->id);
        if($data->delete() > 0) {
            return response()->json(['status' => true, 'pesan' => 'Kode Akun berhasil dihapus!']);
        } else { 
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan!']);
        }
        
        
    }
}
