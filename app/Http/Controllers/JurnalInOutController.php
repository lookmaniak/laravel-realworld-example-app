<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalInOut;
use App\Models\Madrasah;
use App\Models\Kegiatan;
use App\Models\TahunAnggaran;
use App\Models\Akun;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JurnalInOutController extends Controller
{
    
    protected function daftar(Request $request) {
       
        $judul = 'JURNAL UMUM';
        $fa = 'file-signature';
        $data = JurnalInOut::orderBy('tanggal_transaksi', 'desc')->get();
        $data_madrasah = Madrasah::All();
        $data_kegiatan = Kegiatan::all();
        $data_tahun_anggaran = TahunAnggaran::all();
        $data_akun = Akun::orderBy('kode', 'asc')->get();
        
       return view('data.jurnal_in_out.simpel_in_out', compact('judul', 'fa', 'data', 'data_madrasah', 'data_tahun_anggaran', 'data_akun', 'data_kegiatan'));
    }
    
    protected function simpan(Request $request) {
        //return dd($request->kredit_akun_id);
       
        $request->validate([
                'madrasah_id' => 'integer | required',
                'tahun_anggaran_id' => 'integer | required',
                'kegiatan_id' => 'integer | required',
                'tanggal_transaksi' => 'required',
                'debit_akun_id.*' => 'integer | required',
                'nilai' => 'integer | required',
                'kredit_akun_id.*' => 'integer | required',
                'deskripsi' => 'string:255 | required',
            ], $request->all());
            
        $date = new Carbon($request->tanggal_transaksi);
        $jio = JurnalInOut::whereBetween('tanggal_transaksi', [$date->firstOfMonth()->format('Y-m-d'), 
                $date->lastOfMonth()->format('Y-m-d')])->orderBy('id', 'desc')->first();
        
        $th = $jio ? date_create($jio->tanggal_transaksi)->format('ym-') : $date->format('Y-m-');
        $no = $jio ? $jio->nomor + 1 : 1;
        $res = JurnalInOut::create([
                'madrasah_id' => $request->madrasah_id,
                'tahun_anggaran_id' => $request->tahun_anggaran_id,
                'kegiatan_id' => $request->kegiatan_id,
                'debit_akun_id' => $request->debit_akun_id[0],
                'nilai_debit' => $request->nilai,
                'kredit_akun_id' => $request->kredit_akun_id[0],
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'nilai_kredit' => $request->nilai,
                'deskripsi' => $request->deskripsi,
                'no_bukti' => 'BKT-'.$th.$no,
                'no_kwitansi' => $request->no_kwitansi,
                'user_id' => Auth::user()->id,
                'nomor' => $no,
            ]);
        
        return redirect(route('daftar_jurnal_in_out'))->with('success', 'Transaksi berhasil dijurnal disimpan!');
    }
    
    protected function edit(Request $request) {
        $judul = 'EDIT JURNAL';
        $fa = 'file-signature';
        $data = JurnalInOut::find($request->id);
        $data_madrasah = Madrasah::All();
        $data_kegiatan = Kegiatan::all();
        $data_tahun_anggaran = TahunAnggaran::all();
        $data_akun = Akun::orderBy('kode', 'asc')->get();
        
        return view('data.jurnal_in_out.edit_jurnal_in_out', compact('judul', 'fa', 'data', 'data_madrasah', 'data_tahun_anggaran', 'data_akun', 'data_kegiatan'));
    }
    
    protected function update(Request $request) {
        $request->validate([
                'madrasah_id' => 'integer | required',
                'tahun_anggaran_id' => 'integer | required',
                'kegiatan_id' => 'integer | required',
                'debit_akun_id.*' => 'integer | required',
                'tanggal_transaksi' => 'required',
                'nilai' => 'integer | required',
                'kredit_akun_id.*' => 'integer | required',
                'deskripsi' => 'string:255 | required',
            ], $request->all());
            
        $res = JurnalInOut::where('id', $request->id)->update([
                'madrasah_id' => $request->madrasah_id,
                'tahun_anggaran_id' => $request->tahun_anggaran_id,
                'kegiatan_id' => $request->kegiatan_id,
                'debit_akun_id' => $request->debit_akun_id[0],
                'nilai_debit' => $request->nilai,
                'kredit_akun_id' => $request->kredit_akun_id[0],
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'nilai_kredit' => $request->nilai,
                'deskripsi' => $request->deskripsi,
                'no_kwitansi' => $request->no_kwitansi,
                'user_id' => Auth::user()->id,
            ]);
        
        return redirect(route('daftar_jurnal_in_out'))->with('success', 'Jurnal telah diperbarui diperbarui!');
    }
    
    protected function hapus(Request $request) {
        $data = JurnalInOut::findOrFail($request->id);
        if($data->delete() > 0) {
            return response()->json(['status' => true, 'pesan' => 'Jurnal berhasil dihapus!']);
        } else { 
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan!']);
        }
    }
        
    protected function neraca(Request $request) {
        $data_akun = Akun::where('akun_induk', 0)->get();
        $fa = 'balance-scale';
        $judul = 'LAPORAN NERACA';
        $data_madrasah = Madrasah::all();
        
        return view('data.jurnal_in_out.laporan_neraca', compact('data_akun', 'judul', 'fa', 'data_madrasah'));
    }
    
    protected function leger(Request $request) {
        $data_akun = Akun::orderBy('kode')->get();
        $data_madrasah = Madrasah::all();
        $data_tahun_anggaran = TahunAnggaran::all();
        $fa = 'file';
        $judul = 'BUKU BESAR';
        
        return view('data.jurnal_in_out.buku_besar', compact('data_akun', 'judul', 'fa', 'data_madrasah', 'data_tahun_anggaran'));
    }
    
    protected function jurnalBukuBesar(Request $request) {
        $akun = Akun::find($request->id);
        $tahun_anggaran = TahunAnggaran::find($request->tahun_anggaran_id);
        $madrasah = Madrasah::find($request->madrasah_id);
        $data_jurnal = JurnalInOut::where(function($q) use($request) {
                                        return $q->where('kredit_akun_id', $request->id)->orWhere('debit_akun_id', $request->id);
                                    })
                                    ->where('madrasah_id', $request->madrasah_id)
                                    ->where('tahun_anggaran_id', $request->tahun_anggaran_id)
                                    ->whereBetween('tanggal_transaksi', [$request->from, $request->to])
                                    ->orderBy('id')->paginate(10);
        //return response()->json($data_jurnal['data']);
        $viewRender = view('data.jurnal_in_out.template_buku_besar', compact('data_jurnal', 'akun', 'tahun_anggaran', 'madrasah'))->render();
        
        return response()->json(array('status' => true, 'data'=>$viewRender));
    }
}
