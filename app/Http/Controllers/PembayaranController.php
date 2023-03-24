<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\AkunJajan;
use App\Models\JurnalMasuk;
use App\Models\JurnalPembayaran;
use Carbon\Carbon;
use Auth;

class PembayaranController extends Controller
{
    //
    protected function tambah() {
        $data_siswa = Siswa::select(['id', 'nama'])->with('rombel_saat_ini')->where('status', true)->get();
        $judul = "BAYAR TAGIHAN";
        return view('data.pembayaran.form', compact('data_siswa', 'judul'));
    }

    protected function prosesPembayaran(Request $request){
        return response()->json($request);
        $tagihan = Tagihan::whereIn('id', $request->tagihan_id)->get();
        return dd($tagihan);
    }

    protected function simpan(Request $request) {
        $nomor = Pembayaran::max('nomor');
        $nomor++;
        $pembayaran = Pembayaran::create([
            'siswa_id' => $request->data_kwitansi['siswa_id'],
            'rombel_id' => $request->data_kwitansi['rombel_id'],
            'kasir_id' => Auth::user()->id,
            'pembayar' => $request->data_kwitansi['pembayar'],
            'nilai_kwitansi' => $request->data_kwitansi['nilai_kwitansi'],
            'jumlah_pembayaran' => $request->data_kwitansi['jumlah_pembayaran'],
            'nomor' => $nomor,
            'no_kwitansi' => 'YMQ-APP-'.$nomor,
        ]);

        if(!$pembayaran) {
            return response()->json(['status' => false, 'pesan' => 'Terjadi kesalahan saat hendak menyimpan pembayaran!']);
        }

        $data_jurnal = array();
        $tagihan_ids = array();

        foreach ($request->data_pembayaran as $tagihan) {
            $data_jurnal[] = [
                'pembayaran_id' => $pembayaran->id,
                'tagihan_id' => $tagihan['tagihan_id'],
                'jenis_pembayaran' => $tagihan['jenis_pembayaran'],
                'item_pembayaran_deskripsi' => $tagihan['item_pembayaran_deskripsi'],
                'nilai' => $tagihan['nilai'],
                'nilai_pembayaran' => $tagihan['nilai_pembayaran'],
                'keterangan' => $tagihan['keterangan'],
                'created_at' =>  Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            if($tagihan['jenis_pembayaran'] == 'titipan-jajan') {
                $kelompok = Siswa::find($request->data_kwitansi['siswa_id']);
                $akun_jajan = AkunJajan::where('siswa_id', $request->data_kwitansi['siswa_id'])->first();
                if(!$akun_jajan) {
                    $akun_jajan = AkunJajan::create([
                        'siswa_id' => $kelompok->id,
                        'saldo' => 0,
                        'boleh_lebih' => 1,
                        'batas_harian' => 15000,
                    ]);
                }
                $success = JurnalMasuk::create([
                    'siswa_id' => $request->data_kwitansi['siswa_id'],
                    'nilai' => $tagihan['nilai_pembayaran'],
                    'jenis' => 'MASUK',
                    'kelompok' => $kelompok->jenis_kelamin,
                    'keterangan' => $tagihan['keterangan'],
                    'sisa_saldo' => $akun_jajan->saldo,
                    'user_id' => Auth::user()->id,
                ]);

            }

            array_push($tagihan_ids, [
                'id' => $tagihan['tagihan_id'],
                'sisa' => intval($tagihan['nilai']) - intval($tagihan['nilai_pembayaran']),
                'status' => intval($tagihan['nilai']) - intval($tagihan['nilai_pembayaran']) <= 0 ? 1 : 0,
            ]);
        }

        $res = JurnalPembayaran::insert($data_jurnal);

        if($res) {
            foreach ($tagihan_ids as $val) {
                if($val['id'] == 0) continue;
                $tagihan = Tagihan::find($val['id']);
                $tagihan->update($val);
            }
        }
        return response()->json(['status' => true, 'id' => $pembayaran->id]);

    }
    
    protected function cancelPembayaran(Request $request) {
        $pembayaran = Pembayaran::where('id', $request->id)->with('jurnal_pembayaran', function($q){
            return $q->with('tagihan');
        })->first();
        
        
        if(!$pembayaran) {
            return response()->json(['status' => false, 'pesan' => 'Pembayaran tidak ditemukan']);
        }
        
        //copy instance avoid direct modification error
        $_pembayaran = $pembayaran;
        
        foreach($_pembayaran->jurnal_pembayaran as $jurnal) {
            
            //copy instance
            $_jurnal = $jurnal;
            
            //cek jika jurnal tidak ada referensi tagihan, maka langsung hapus saja
            if(!$_jurnal->tagihan) {
                $_jurnal->delete();
                continue;
            };
            
            //jika ada referensi tagihan, maka amankan beberapa nilai data
            //ambil nilai pembayarannya di jurnal
            $nilai_pembayaran = $_jurnal->nilai_pembayaran; 
            
            //copy instance
            $_tagihan = $_jurnal->tagihan;
            
            //masukin lagi nilai yang dibayarankan pada jurnal, ke sisa pembayaran
            $_tagihan->sisa += $nilai_pembayaran;
            
            //kalau sisa tagihan masih ada, maka ganti statusnya ke false
            if($nilai_pembayaran > 0) {
                $_tagihan->status =   false;
            }
            
            if($_tagihan->save()) {
                $_jurnal->delete();
            }
        }
        
        //pastikan dulu bahwa pembayaran yang akan dihapus sudah tidak memiliki rincian di
        //ambil data baru aja
        $confirm = Pembayaran::where('id', $request->id)->with('jurnal_pembayaran')->first();
        
        //hitung dan hapus hanya apabila tidak memiliki rincian di jurnal
        if(count($confirm->jurnal_pembayaran) == 0) {
            $confirm->delete();
            return response()->json(['status' => true, 'pesan' => 'Pembayaran berhasil dihapus'], 200);
        } else {
            
            return response()->json(['status' => false, 'pesan' => $confirm->jurnal_pembayaran], 200);
        }
        
    }

    protected function cetakKwitansi(Request $request) {
        $pembayaran = Pembayaran::find($request->id);
        $siswa = $pembayaran->siswa;
        $jurnal_pembayaran = $pembayaran->jurnal_pembayaran;

        $pdf = \PDF::loadView('data.pembayaran.kwitansi', compact('pembayaran', 'siswa', 'jurnal_pembayaran'));
        $customPaper = array(0,0,(8.5*72),(5.5*72));
        return $pdf->setPaper($customPaper, '')->stream($pembayaran->siswa->nama.'-'.$pembayaran->no_kwitansi.'.pdf');
    }
    
    protected function cetakKwitansiKosong() {
        $pdf = \PDF::loadView('data.pembayaran.kwitansi_kosong');
        $customPaper = array(0,0,(8.5*72),(5.5*72));
        return $pdf->setPaper($customPaper, '')->stream('kwitansi_kosong.pdf');
    }

    protected function daftar(Request $request) {
        if(isset($request->keyword)) {
            $key = $request->keyword;
            $data_pembayaran = Pembayaran::with('jurnal_pembayaran')->with('siswa', function($q){
                $q->with('data_rombel');
            })->whereHas('siswa', function($q) use($key) {
                $q->where('nama', 'LIKE', '%'.$key.'%');
            })
            ->orWhereHas('jurnal_pembayaran', function($q) use($key){
                $q->where('item_pembayaran_deskripsi', 'LIKE', '%'.$key.'%')
                ->orWhere('keterangan', 'LIKE', '%'.$key.'%');
            })
            ->orWhere('no_kwitansi', $key)->orderBy('id', 'DESC')->paginate(10);
            
        } else {
            $data_pembayaran = Pembayaran::with('jurnal_pembayaran')->with('siswa', function($q){
                $q->with('data_rombel');
            })->orderBy('id', 'DESC')->paginate(10);
        }
        $judul = 'Jurnal Pembayaran';
        $keyword = $request->keyword;
        
       // return dd($data_pembayaran);
        
        return view('data.pembayaran.daftar', compact('data_pembayaran', 'judul', 'keyword'));
    }
    
    protected function rekapPenerimaan(Request $request) {
        $akun_lukman = Pembayaran::where('kasir_id', 5)->whereBetween('created_at', [$request->from, $request->to])->sum('nilai_kwitansi');
        $akun_hana = Pembayaran::where('kasir_id', 6)->whereBetween('created_at', [$request->from, $request->to])->sum('nilai_kwitansi');
        
        return response()->json(["status" => true, "data" => ["akun_lukman" => $akun_lukman, "akun_hana" => $akun_hana]], 200);
    }
}
