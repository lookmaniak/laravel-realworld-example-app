<?php

namespace App\Http\Controllers;

use App\Models\ItemPembayaran;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;
use App\Models\Pembayaran;
use App\Models\Rombel;
use App\Models\Madrasah;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TagihanController extends Controller
{
    protected function indexSiswaHasTagihan(Request $request) {
        $k = $request->keyword;

        $tp = $request->tahun_pelajaran_id;
        $m = $request->madrasah_id;
        $i = $request->item_pembayaran_id;
        
        $madrasah = \Auth::user()->madrasah->where('id', $m)->first();
        $item_pembayaran = $madrasah?->item_pembayaran->where('id', $i)->first();

        if(!$item_pembayaran) return response()->json([
            'code' => 402, 
            'errors' => (object)[], 
            'message' => 'Item Pembayaran tidak ditemukan!' ]
            , 402);

       
        
        $tagihans = Tagihan::where('madrasah_id', $madrasah->id)
                    ->where('tahun_pelajaran_id', $tp)
                    ->where('item_pembayaran_id', $item_pembayaran->id)
                    ->where('is_deleted', false)
                    ->whereHas('siswa', function($q) use ($k){
                        return $q->where('nama', 'LIKE', '%'.$k.'%');
                    })
                    ->with('siswa', function($q) {
                        return $q->with('rombel_saat_ini');
                    })
                    ->with('pembayaran');

        if(!empty($request->sort)) {
            foreach( $request->sort as $key => $value) {
                $tagihans = $tagihans->orderBy($key, $value);
            }
        }
        $tagihans = $tagihans->paginate($request->per_page);

        return response()->json($tagihans, 200);
    }

    protected function daftar(Request $request) {
       
        $judul = 'Daftar Tagihan';
        
        $tahun_pelajaran = TahunPelajaran::all();
        
        $tp = $request->tp == null ? $tahun_pelajaran->where('status', true)->first() : $tahun_pelajaran->where('id', $request->tp)->first();
        
        $data = ItemPembayaran::whereHas('tagihan', function ($query) use ($tp) {
                    return $query->where('tahun_pelajaran_id', $tp->id);
                })->get();
                
        $periode = $tp;
        
        return view('data.tagihan.daftar', compact('data', 'judul', 'periode', 'tahun_pelajaran'));
    }
    
    protected function siswaTanpaTagihan(Request $request) {
        $tp = $request->tahun_pelajaran_id;
        $id = $request->id;
        $d = ['tp' => $tp, 'id' => $id];
        
        $item_pembayaran = ItemPembayaran::find($request->id);
        if($item_pembayaran->tagihan_berulang) {
           $data = Siswa::with('rombel_saat_ini')->where('madrasah_id', $request->madrasah_id)->where('status', true)->get(); 
        } else {
            $data = Siswa::with('rombel_saat_ini')->where('madrasah_id', $request->madrasah_id)->where('status', true)->whereDoesntHave('tagihan', function($q) use ($d) {
                return $q->where('tahun_pelajaran_id', $d['tp'])->where('item_pembayaran_id', $d['id']);
            })->get();
        }
        
        return view('data.tagihan.tabel_siswa', compact('data'));
    }

    protected function detail(Request $request) {
        $item_pembayaran = ItemPembayaran::find($request->id);
        $tahun_pelajaran = TahunPelajaran::find($request->tahun_pelajaran_id);
        $data = Tagihan::where('item_pembayaran_id', $request->id)->where('tahun_pelajaran_id', $tahun_pelajaran->id)->with('pembayaran')->get();
        //return dd($data);
        $total_tagihan_terbit = $data->where('terbit', true)->count();
        $total_tagihan_belum_terbit = $data->where('terbit', false)->count();
        $total_tagihan_lunas = $data->where('status', true)->count();
        $total_tagihan_tertunggak = $data->where('status', false)->count();
        $total_nilai_tunggakan = $data->where('status', false)->sum('nilai');
        $judul = 'Detail Tagihan';
        
        return view('data.tagihan.detail', compact('total_nilai_tunggakan', 'data', 'judul', 'tahun_pelajaran', 'item_pembayaran', 'total_tagihan_terbit', 'total_tagihan_lunas', 'total_tagihan_tertunggak'));
    }

    protected function simpan(Request $request) {
        
        //return dd($request->siswa_id);
        
        $request->validate([
            'madrasah_id' => 'integer | required',
            'tahun_pelajaran_id' => 'integer | required',
            'item_pembayaran_id' => 'integer | required',
            'nilai' => 'integer | required',
        ], $request->all());
        
        
        if($request->siswa_id == null) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih, anda harus memilih siswa terlebih dahulu!');
        }
        
        $data = array();
        
        foreach ($request->siswa_id as $id) {
            $data[]= [
                'madrasah_id' => $request->madrasah_id,
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'siswa_id' => $id,
                'item_pembayaran_id' => $request->item_pembayaran_id,
                'nilai' => $request->nilai,
                'sisa' => $request->nilai,
                'created_at' =>  Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Tagihan::insert($data);
        return redirect(route('daftar_tagihan'))->with('success', 'Data siswa berhasil disimpan!');
    }

    protected function hapus(Request $request) {
        $data = Tagihan::find($request->id);
        $data->delete();
        return redirect()->back()->with('success', 'Tagihan berhasil dihapus!');
    }

    protected function lihatTagihanPerSiswa(Request $request) {
        $data_siswa = Siswa::find($request->id);

        if(!$data_siswa) return response()->json(['status' => false]);

        $data_tagihan = Tagihan::where('status', false)->where('siswa_id', $request->id)->where('terbit', true)->with(['item_pembayaran', 'tahun_pelajaran'])->get();
        //$total_tagihan = Tagihan::where('status', false)->where('siswa_id', $request->id)->where('terbit', true)->sum('nilai');
        $total_tagihan = $data_tagihan->sum('nilai');
        $pembayar_terakhir = Pembayaran::where('siswa_id', $request->id)->orderBy('id', 'DESC')->first();
        return response()->json(
            [
                'status' => true,
                'template_daftar_tagihan' => view('data.pembayaran.template_daftar_tagihan', compact('data_tagihan', 'data_siswa', 'pembayar_terakhir' ))->render(), 
                'template_informasi_siswa' => view('data.pembayaran.template_informasi_siswa', compact('total_tagihan', 'data_tagihan', 'data_siswa'))->render(),
            ]);
    }
    
    protected function lihatHistoryPerSiswa(Request $request) {
        $data_siswa = Siswa::find($request->id);

        if(!$data_siswa) return response()->json(['status' => false]);

        return response()->json(
            [
                'status' => true,
                'template_history' => view('data.pembayaran.template_history', compact('data_siswa'))->render(),
            ]);
    }

    protected function lihatTagihanPerId(Request $request) {
        $data_tagihan = Tagihan::find($request->id);
     
        return response()->json(['status' => true, 'data' => $data_tagihan]);
    }
    
    protected function edit(Request $request) {
        $tagihan = Tagihan::where('id', $request->id)->with('item_pembayaran')->with('siswa')->first();
        $judul = "EDIT TAGIHAN";
        
        return view('data.tagihan.form_edit', compact('tagihan', 'judul'));
    }
    
    protected function update(Request $request) {
        $tagihan = Tagihan::find($request->id);
        $selisih = intval($request->nilai) - $tagihan->nilai;
        $tagihan->nilai = intval($request->nilai);
        $tagihan->sisa = $tagihan->sisa + $selisih;
        if($tagihan->sisa == 0) {
            $tagihan->status = 1;
        } else {
            $tagihan->status = 0;
        }
        $tagihan->save();
        
        return redirect(route('detail_tagihan', ['id' => $tagihan->item_pembayaran_id, 'tahun_pelajaran_id' => $tagihan->tahun_pelajaran_id]))
                ->with('success', 'Tagihan berhasil diupdate!');
    }
    
    protected function hapusData(Request $request) {
        if(count($request->tagihan_id) == 0) return response()->json(['success' => false, 'message' => 'Anda belum memilih data!'], 200);
        
        $res = Tagihan::whereIn('id', $request->tagihan_id)->delete();
        
        return response()->json(['success' => true, 'message' => $res.' Data berhasil dihapus', 'data' => $res], 200);
    }
    
    protected function siswaMenunggak(Request $r){
    
        $rombel = Rombel::orderBy('kode', 'ASC')->get();
        $tp_id = TahunPelajaran::all();
        $tp = $r->tp_id;
        
        $siswa = Siswa::where('status', true)->whereHas('rombel_saat_ini', function($q) use ($r){
            return $q->where('rombel_id', $r->rombel_id)->where('tahun_pelajaran_id', $r->tp_id);
        })->whereHas('tagihan', function ($query) {
            return $query->where('sisa', '>', 0);
        })->with('rombel_saat_ini')->with('tagihan')->get();
                                                
    
        $rombel_saat_ini = $r->rombel_id;
      //return dd($siswa);
      return response()->json(compact('siswa', 'rombel', 'rombel_saat_ini', 'tp_id', 'tp'), 200);
    }
}
