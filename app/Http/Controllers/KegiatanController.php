<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kegiatan;

class KegiatanController extends Controller
{
    protected function daftar(Request $request) {
       
        $judul = 'DAFTAR KEGIATAN';
        $fa = 'tasks';
        $data = Kegiatan::all();
        
        return view('data.kegiatan.daftar_kegiatan', compact('judul', 'fa', 'data'));
    }
    
    protected function simpan(Request $request) {
        $request->validate([
                'nama' => 'string:255 | required',
            ], $request->all());
            
        $res = Kegiatan::create([
                'nama' => $request->nama
            ]);
        
        return redirect(route('daftar_kegiatan'))->with('success', 'Kegiatan berhasil disimpan!');
    }
    
    protected function edit(Request $request) {
        $judul = 'EDIT KEGIATAN';
        $fa = 'tasks';
        $data = Kegiatan::find($request->id);
        
        return view('data.kegiatan.edit_kegiatan', compact('judul', 'fa', 'data'));
    }
    
    protected function update(Request $request) {
        
        $request->validate([
                'nama' => 'string:255 | required',
            ], $request->all());
            
        $data = Kegiatan::find($request->id);
        $data->nama = $request->nama;
        $data->save();
        
        return redirect(route('daftar_kegiatan'))->with('success', 'Kegiatan berhasil diperbarui!');
    }
    
    protected function hapus(Request $request) {
        $data = Kegiatan::findOrFail($request->id);
        if($data->delete() > 0) {
            return response()->json(['status' => true, 'pesan' => 'Kegiatan berhasil dihapus!']);
        } else { 
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan!']);
        }
    }
}
