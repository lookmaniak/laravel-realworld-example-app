<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    protected function daftar() {
        $judul = 'DAFTAR AKUN SIMPANAN';
        $fa = 'landmark';
        $data = Bank::all();
        
        return view('data.bank.daftar_bank', compact('judul', 'fa', 'data'));
    }
    
    protected function simpan(Request $request) {
        $request->validate([
                'nama' => 'string:max(255) | required',
                'saldo_awal' => 'integer | required',
            ], $request->all());
            
        $res = Bank::create([
                'nama' => $request->nama,
                'saldo' => $request->saldo_awal,
            ]);
        
        return redirect(route('daftar_bank'))->with('success', 'Akun Simpanan berhasil disimpan!');
    }
    
    protected function edit(Request $request) {
        $judul = 'EDIT AKUN SIMPANAN';
        $fa = 'landmark';
        $data = Bank::find($request->id);
        
        return view('data.bank.edit_bank', compact('judul', 'fa', 'data'));
    }
    
    protected function update(Request $request) {
        
        $request->validate([
                'nama' => 'string:max(255) | required',
                'saldo_awal' => 'integer | required',
            ], $request->all());
            
        $data = Bank::find($request->id);
        $data->nama = $request->nama;
        $data->save();
        
        return redirect(route('daftar_bank'))->with('success', 'Akun Simpanan berhasil diperbarui!');
    }
    
    protected function hapus(Request $request) {
        $data = Bank::findOrFail($request->id);
        if($data->delete() > 0) {
            return response()->json(['status' => true, 'pesan' => 'Akun simpanan berhasil dihapus!']);
        } else { 
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan!']);
        }
        
        
    }
}
