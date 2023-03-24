<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAnggaran;

class TahunAnggaranController extends Controller
{
     protected function daftar(Request $request) {
       
        $judul = 'DAFTAR TAHUN ANGGARAN';
        $fa = 'calendar';
        $data = TahunAnggaran::all();
        
        return view('data.tahun_anggaran.daftar_tahun_anggaran', compact('judul', 'fa', 'data'));
    }
    
    protected function simpan(Request $request) {
        $request->validate([
                'tahun' => 'integer | required',
            ], $request->all());
            
        $res = TahunAnggaran::create([
                'tahun' => $request->tahun
            ]);
        
        return redirect(route('daftar_tahun_anggaran'))->with('success', 'Tahun anggaran berhasil disimpan!');
    }
    
    protected function edit(Request $request) {
        $judul = 'EDIT TAHUN ANGGARAN';
        $fa = 'calendar';
        $data = TahunAnggaran::find($request->id);
        
        return view('data.tahun_anggaran.edit_tahun_anggaran', compact('judul', 'fa', 'data'));
    }
    
    protected function update(Request $request) {
        
        $request->validate([
                'tahun' => 'integer | required',
            ], $request->all());
            
        $data = TahunAnggaran::find($request->id);
        $data->tahun = $request->tahun;
        $data->save();
        
        return redirect(route('daftar_tahun_anggaran'))->with('success', 'Tahun anggaran berhasil diperbarui!');
    }
    
    protected function hapus(Request $request) {
        $data = TahunAnggaran::findOrFail($request->id);
        if($data->delete() > 0) {
            return response()->json(['status' => true, 'pesan' => 'Tahun anggaran berhasil dihapus!']);
        } else { 
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan!']);
        }
    }
}
