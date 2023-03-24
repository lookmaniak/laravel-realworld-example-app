<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\JurnalJajan;
use App\Models\AkunJajan;
use App\Models\JurnalMasuk;
use Carbon\Carbon;
use Auth;
use App\Models\User;

class JurnalJajanController extends Controller
{
    protected function ringkasan() {
        $total_banin_hari_ini = JurnalJajan::where('kelompok', 'L')->where('jenis', 'KELUAR')->where('user_id', 7)->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_banat_hari_ini = JurnalJajan::where('kelompok', 'P')->where('jenis', 'KELUAR')->where('user_id', 8)->whereDate('created_at', Carbon::today())->sum('nilai');
        $data_topup_banin = JurnalMasuk::where('kelompok', 'L')->where('jenis', 'MASUK')->where('status', 0)->orderBy('created_at', 'DESC')->get();
        $data_topup_banat = JurnalMasuk::where('kelompok', 'P')->where('jenis', 'MASUK')->where('status', 0)->orderBy('created_at', 'DESC')->get();
        return view('data.jajan.public.layout', compact('total_banin_hari_ini', 'total_banat_hari_ini', 'data_topup_banat', 'data_topup_banin'));
    }
    
    protected function daftarPengambilan() {
        $judul = "TARIK TUNAI";
        $data_siswa = Siswa::where('status_siswa', 1)->has('akun_jajan')->get();
        if(Auth::user()->level == 1) {
            $data_pengambilan = JurnalJajan::whereDate('created_at', Carbon::today())->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->paginate(20);
            $total_pengambilan_hari_ini = JurnalJajan::whereDate('created_at', Carbon::today())->sum('nilai');
            $total_pengambilan_banin = JurnalJajan::where('kelompok', 'L')->where('jenis', 'KELUAR')->where('user_id', 7)->whereDate('created_at', Carbon::today())->sum('nilai');
            $total_pengambilan_banat = JurnalJajan::where('kelompok', 'P')->where('jenis', 'KELUAR')->where('user_id', 8)->whereDate('created_at', Carbon::today())->sum('nilai');
        } else {
            $data_pengambilan = JurnalJajan::whereDate('created_at', Carbon::today())->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->paginate(20);
            $total_pengambilan_hari_ini = JurnalJajan::whereDate('created_at', Carbon::today())->sum('nilai');
            $total_pengambilan_banin = JurnalJajan::where('kelompok', 'L')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
            $total_pengambilan_banat = JurnalJajan::where('kelompok', 'P')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        }

        return view('data.jajan.public.form_pengambilan', compact('judul', 'data_siswa', 'data_pengambilan', 'total_pengambilan_hari_ini', 'total_pengambilan_banin', 'total_pengambilan_banat'));
    }

    protected function pengambilanKhusus() {
        $judul = "TARIK TUNAI";
        $data_siswa = Siswa::has('akun_jajan')->get();
        $data_pengambilan = JurnalJajan::whereDate('created_at', Carbon::today())->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->get();
        $total_pengambilan_hari_ini = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_pengambilan_banin = JurnalJajan::where('kelompok', 'L')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_pengambilan_banat = JurnalJajan::where('kelompok', 'P')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');

        return view('data.jajan.form_pengambilan_khusus', compact('judul', 'data_siswa', 'data_pengambilan', 'total_pengambilan_hari_ini', 'total_pengambilan_banin', 'total_pengambilan_banat'));
    }

    protected function simpanPengambilanKhusus(Request $request) {
        $akun_jajan = AkunJajan::where('siswa_id', $request->siswa_id)->first();

        if(!$akun_jajan) {
            return redirect()->back()->withInput()->with('error', 'Akun jajan tidak ditemukan!');
        }

        $kelompok = Siswa::find($request->siswa_id);

        $request->validate([
            'siswa_id' => 'required',
            'nilai' => 'integer | required',
            'keterangan' => 'max:255',
        ]);

        if(intval($request->nilai) > $akun_jajan->saldo) {
            return redirect()->back()->withInput()->with('error', 'Maaf, saldo anda tidak cukup!');
        }

        $success = JurnalJajan::create([
            'siswa_id' => $request->siswa_id,
            'nilai' => $request->nilai,
            'keterangan' => $request->keterangan,
            'jenis' => 'KELUAR',
            'kelompok' => $kelompok->jenis_kelamin,
            'sisa_saldo' => $akun_jajan->saldo - intval($request->nilai),
            'user_id' => Auth::user()->id,
        ]);

        if($success) {
            $akun_jajan->saldo = $akun_jajan->saldo - intval($request->nilai);
            $akun_jajan->save();
        }
        
        return redirect(route('pengambilan_saldo_khusus'))->with('success', 'Penarikan tunai atas nama <strong>'.$akun_jajan->siswa->nama.'</strong> sejumlah <strong>Rp '.number_format($request->nilai).'</strong> berhasil disimpan! Sisa saldo : <strong>Rp '.number_format($akun_jajan->saldo).'</strong>');
    }

    protected function pengambilanSaldo() {
        $judul = "TARIK TUNAI";
        $data_siswa = Siswa::has('akun_jajan')->get();
        $data_pengambilan = JurnalJajan::whereDate('created_at', Carbon::today())->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->paginate(20);
        $total_pengambilan_hari_ini = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_pengambilan_banin = JurnalJajan::where('kelompok', 'L')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_pengambilan_banat = JurnalJajan::where('kelompok', 'P')->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');

        return view('data.jajan.form_pengambilan', compact('judul', 'data_siswa', 'data_pengambilan', 'total_pengambilan_hari_ini', 'total_pengambilan_banin', 'total_pengambilan_banat'));
    }

    protected function penambahanSaldo() {
        $judul = "TOPUP SALDO";
        $data_siswa = Siswa::has('akun_jajan')->get();
        $data_penambahan = JurnalMasuk::where('status', false)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->get();
        $total_penambahan_hari_ini = JurnalJajan::where('jenis', 'MASUK')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_penambahan_banin = JurnalJajan::where('kelompok', 'L')->where('jenis', 'MASUK')->whereDate('created_at', Carbon::today())->sum('nilai');
        $total_penambahan_banat = JurnalJajan::where('kelompok', 'P')->where('jenis', 'MASUK')->whereDate('created_at', Carbon::today())->sum('nilai');

        return view('data.jajan.form_penambahan', compact('judul', 'data_siswa', 'data_penambahan', 'total_penambahan_hari_ini', 'total_penambahan_banin', 'total_penambahan_banat'));
    }

    protected function simpanPengambilan(Request $request) {
        $akun_jajan = AkunJajan::where('siswa_id', $request->siswa_id)->first();

        if(!$akun_jajan) {
            return response()->json(['pesan' => 'Akun jajan tidak ditemukan!']);
        }
/*
        if(intval($akun_jajan->pin) !== intval($request->pin)) {
            return response()->json(['pesan' => 'PIN yang kamu masukan salah!']);
        }

        if(intval($akun_jajan->pin) == 1234) {
            return response()->json(['pesan' => 'Kamu harus ganti PIN terlebih dahulu!']);
        }
*/
        $kelompok = Siswa::find($request->siswa_id);

        $request->validate([
            'siswa_id' => 'required',
            'nilai' => 'integer | required',
            'keterangan' => 'max:255',
        ]);

        if(!$akun_jajan->boleh_lebih) {
            $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
            if((intval($pengambilan_hari_ini) + intval($request->nilai)) > intval($akun_jajan->batas_harian)) {
                return response()->json(['pesan' => 'Pengambilan kamu sudah melebihi batas harian yang diizinkan!']);
            }
        }

        if(intval($request->nilai) > $akun_jajan->saldo) {
            return response()->json(['pesan' => 'Maaf, saldo kamu tidak cukup!']);
        }
        $success = JurnalJajan::create([
            'siswa_id' => $request->siswa_id,
            'nilai' => $request->nilai,
            'keterangan' => $request->keterangan,
            'jenis' => 'KELUAR',
            'kelompok' => $kelompok->jenis_kelamin,
            'sisa_saldo' => $akun_jajan->saldo - intval($request->nilai),
            'user_id' => Auth::user()->id,
        ]);

        if($success) {
            $akun_jajan->saldo = $akun_jajan->saldo - intval($request->nilai);
            $akun_jajan->save();
        }
        
        return response()->json(['pesan' => 'Permintaan jajan berhasil dibuat!', 'data' => $success, 'nama' => $akun_jajan->siswa->nama]);
        //return dd($pengambilan_hari_ini);
    }

    protected function simpanPenambahan(Request $request) {
        $kelompok = Siswa::find($request->siswa_id);

        $request->validate([
            'siswa_id' => 'required',
            'nilai' => 'integer | required',
        ]);

        $akun_jajan = AkunJajan::where('siswa_id', $request->siswa_id)->first();
        
        $success = JurnalMasuk::create([
            'siswa_id' => $request->siswa_id,
            'nilai' => $request->nilai,
            'jenis' => 'MASUK',
            'kelompok' => $kelompok->jenis_kelamin,
            'keterangan' => $request->keterangan,
            'sisa_saldo' => $akun_jajan->saldo + intval($request->nilai),
            'user_id' => Auth::user()->id,
        ]);

        return redirect(route('penambahan_saldo'))->with('warning', 'Penambahan saldo menunggu status SC..!');
        //return dd($pengambilan_hari_ini);
    }

    protected function jurnalJajan(Request $request) {
        $akun = AkunJajan::where('siswa_id', $request->id)->first();
        $judul = "LAPORAN TRANSAKSI HARIAN";
        if(!$akun) {
            return redirect()->back()->with('error', 'Akun jajan tidak ditemukan!');
        }

        $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $request->id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $pengambilan_terakhir = JurnalJajan::where('siswa_id', $request->id)->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->first();
        $penambahan_terakhir = JurnalJajan::where('siswa_id', $request->id)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->first();

        $data_akun = [
            'id' => $akun->id,
            'nama' => $akun->siswa->nama,
            'boleh_lebih' => $akun->boleh_lebih ? true : false,
            'pengambilan_hari_ini' => intval($pengambilan_hari_ini),
            'saldo' => $akun->saldo,
            'batas_harian' => $akun->batas_harian,
            'pengambilan_terakhir' => $pengambilan_terakhir,
            'penambahan_terakhir' => $penambahan_terakhir,
            'rombel_saat_ini' => $akun->siswa->rombel_saat_ini,
        ];

        $data_akun = (object) $data_akun;

        $data = JurnalJajan::where('siswa_id', $request->id)->orderBy('id', 'DESC')->get();
        return view('data.jajan.jurnal', compact('judul', 'data_akun', 'data'));
        //return response()->json($data_jurnal);
    }

    protected function lihatJurnal(Request $request) {
        $akun = AkunJajan::where('siswa_id', $request->id)->first();
        $judul = "LAPORAN TRANSAKSI HARIAN";
        if(!$akun) {
            return redirect()->back()->with('error', 'Akun jajan tidak ditemukan!');
        }
        /*
        if($akun->token_jurnal !== $request->token) {
            return redirect()->route('cek_saldo')->with('error', 'Sesi telah berakhir, silahkan cek saldo dulu!');
        }
        */
        $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $akun->siswa->id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $pengambilan_terakhir = JurnalJajan::where('siswa_id', $akun->siswa->id)->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->first();
        $penambahan_terakhir = JurnalJajan::where('siswa_id', $akun->siswa->id)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->first();

        $data_akun = [
            'id' => $akun->id,
            'nama' => $akun->siswa->nama,
            'siswa_id' => $akun->siswa->id,
            'boleh_lebih' => $akun->boleh_lebih ? true : false,
            'pengambilan_hari_ini' => intval($pengambilan_hari_ini),
            'saldo' => $akun->saldo,
            'batas_harian' => $akun->batas_harian,
            'pengambilan_terakhir' => $pengambilan_terakhir,
            'penambahan_terakhir' => $penambahan_terakhir,
        ];

        $data_akun = (object) $data_akun;
        $token_jurnal = $request->token;
        
        $data = JurnalJajan::where('siswa_id', $request->id)->orderBy('id', 'DESC')->paginate(20);
        return view('data.jajan.public.tabel_jurnal', compact('judul', 'data_akun', 'data', 'token_jurnal'));
        //return response()->json($data_jurnal);
    }

    protected function resetTokenJurnal(Request $request) {
        $akun = AkunJajan::find($request->id);
        $akun->token_jurnal = "";
        $akun->save();
        return redirect()->route('dashboard_teller');
    }
    
    protected function gantiStatus(Request $request) {
        $jurnal = JurnalMasuk::find($request->id);
        
        if(!$jurnal) {
            return response()->json(['status' => false, 'pesan' => "Data tidak ditemukan"]);
        }
        
        $jurnal->status = true;
        if(!$jurnal->old) {
            if($jurnal->save()) {
            
	        
                $akun_jajan = AkunJajan::where('siswa_id', $jurnal->siswa_id)->first();
                JurnalJajan::create([
	            'siswa_id' => $jurnal->siswa_id,
	            'nilai' => $jurnal->nilai,
	            'keterangan' => $jurnal->keterangan,
	            'jenis' => 'MASUK',
	            'kelompok' => $jurnal->kelompok,
	            'sisa_saldo' => $akun_jajan->saldo + intval($jurnal->nilai),
	            'user_id' => Auth::user()->id,
	        ]);
                $akun_jajan->saldo = $akun_jajan->saldo + intval($jurnal->nilai);
                $akun_jajan->save();
            }
            
        } else {
            $jurnal->old = false;
            $jurnal->save();
        }
        
        return response()->json(['status' => true, 'pesan' => 'Status sudah diganti..!', 'data' => $akun_jajan], 200);
        
    }
    
    protected function rekapJajan() {
        
        $rekap_rina = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->where('user_id', 7)->sum('nilai');
        $rekap_uli = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->where('user_id', 8)->sum('nilai');
        $rekap_lukman = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->where('user_id', 5)->sum('nilai');
        $rekap_hana = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->where('user_id', 6)->sum('nilai');
        $rekap_total_banat = JurnalJajan::where('jenis', 'KELUAR')->where('kelompok','P')->whereDate('created_at', Carbon::today())->sum('nilai');
        $rekap_total_banin = JurnalJajan::where('jenis', 'KELUAR')->where('kelompok','L')->whereDate('created_at', Carbon::today())->sum('nilai');
        $rekap_total = JurnalJajan::where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $today = Carbon::today()->format('d F Y');
        return view('data.jajan.rekap_uang_jajan', compact('rekap_rina', 'rekap_uli', 'rekap_lukman','rekap_hana','rekap_total_banat','rekap_total_banin','today','rekap_total'));
    }
    
    protected function cetakPanggilan() {
        $judul = "PANGGILAN";
        $data_penambahan = JurnalMasuk::where('jenis', 'MASUK')->where('status', 0)->get();
       

        $pdf = \PDF::loadView('data.jajan.form_panggilan', compact('data_penambahan'))->setPaper('a4', 'landscape');
        return $pdf->stream('download.pdf');
    }
}
 