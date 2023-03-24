<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Madrasah;
use Carbon\Carbon;


class ApiController extends Controller
{
    public function getSiswaById(Request $request) {
        
        $siswa = Siswa::with('madrasah')->with('profil')->with('rombel_saat_ini')->where('id', $request->id)->first();
        
        return response()->json(['success' => true, 'results' => $siswa], 200);
    }
    
    public function getActivation(Request $request) {
        $siswa = Siswa::with('madrasah')->with('pembayaran', function($q){
            return $q->orderBy('id', 'DESC')->with('jurnal_pembayaran')->with('user');
        })->with('tagihan', function($q){
            return $q->where('status', 0)->with('item_pembayaran')->with('tahun_pelajaran');
        })->with('jurnal_jajan', function($q){
            //return $q->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])->orderBy('id', 'DESC');
            return $q->take(30)->orderBy('id', 'DESC');
        })
        ->whereHas('profil', function($q) use ($request) {
            return $q->where('no_kontak', $request->no_kontak);
        })->with('rombel_saat_ini')->with('profil')->where('kode_aktivasi', $request->kode_aktivasi)->first();
        $siswa->jenjang = $siswa->madrasah->jenjang;
        $siswa->total_tagihan = $siswa->tagihan->sum('sisa');
        $siswa->saldo_tabungan = $siswa->jurnal_jajan->first() ? $siswa->jurnal_jajan->first()->sisa_saldo : '0';
        
        
        return response()->json(['success' => true, 'results' => $siswa], 200);
    }
    
    //Siswa
    public function indexSiswa() {
        try {
            $data = Siswa::with('madrasah')->with('profil')->with('rombel_saat_ini')->get();
            $count = $data->count();
            $success = true;
            $message= 'Success!';
            $error = Array();
        } catch (\Exception $e){
            $data = null;
            $count = 0;
            $success = false;
            $message= 'Failed!';
            $error = $e;
        }
        
        return response($data, 200)->header('X-Total-Count', $data->count());
    }
    
    public function showSiswa(Request $request) {
        try {
            $data = Siswa::with('madrasah')->with('profil')->with('rombel_saat_ini')->where('id', $request->id)->first();
            $count = $data->count();
            $success = true;
            $message= 'Success!';
            $error = Array();
        } catch (\Exception $e){
            $data = null;
            $count = 0;
            $success = false;
            $message= 'Failed!';
            $error = $e;
        }
        
        return response()->json($data, 200);
    }
    
    public function showMadrasah(Request $request) {
        try {
            $data = Madrasah::where('id', $request->id)->first();
            $count = $data->count();
            $success = true;
            $message= 'Success!';
            $error = Array();
        } catch (\Exception $e){
            $data = null;
            $count = 0;
            $success = false;
            $message= 'Failed!';
            $error = $e;
        }
        
        return response($data, 200)->header('X-Total-Count', $data->count());
    }
}
