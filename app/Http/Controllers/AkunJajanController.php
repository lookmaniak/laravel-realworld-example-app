<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkunJajan;
use App\Models\JurnalJajan;
use App\Models\JurnalMasuk;
use App\Models\Siswa;
use Carbon\Carbon;
use Auth;

class AkunJajanController extends Controller
{
    protected function daftar() {
        $judul = 'DAFTAR AKUN TABUNGAN';
        $fa = 'list';
        $data = AkunJajan::all();
        $data_siswa = Siswa::where('status', true)->get();

        return view('data.jajan.daftar', compact('judul', 'data', 'data_siswa', 'fa'));
    }

    protected function tambah(Request $request) {
        $judul = 'TOPUP SALDO TABUNGAN';
        $fa = 'money';
        $data_siswa = Siswa::where('id', $request->id)->first();

        return view('data.jajan.form', compact('judul', 'data_siswa', 'fa'));
    }

    protected function simpan(Request $request) {
        $request->validate([
            'siswa_id' => 'required | unique:akun_jajans',
            'saldo' => 'integer | required',
            'batas_harian' => 'integer | required',
            'boleh_lebih' => 'integer | required',
        ]);
        $siswa = Siswa::find($request->siswa_id);
        $success = AkunJajan::create([
            'siswa_id' => $request->siswa_id,
            'saldo' => 0,
            'batas_harian' => $request->batas_harian,
            'boleh_lebih' => $request->boleh_lebih,
        ]);
        if($success) {
            JurnalMasuk::create([
                'siswa_id' => $request->siswa_id,
                'nilai' => $request->saldo,
                'jenis' => 'MASUK',
                'kelompok' => $siswa->jenis_kelamin,
                'sisa_saldo' => $success->saldo - intval($request->nilai),
                'user_id' => Auth::user()->id,
            ]);
        }

        return redirect(route('daftar_akun'))->with('success', 'Akun tabungan berhasil dibuat!');
    }

    protected function edit(Request $request)     {
        $data = AkunJajan::find($request->id);
        $judul = 'EDIT AKUN TABUNGAN';
        $fa = 'user';
        $data_siswa = Siswa::where('status', true)->get();

        return view('data.jajan.form_edit', compact('data', 'judul', 'data_siswa', 'fa'));
    }

    protected function update(Request $request) {
        $request->validate([
            'batas_harian' => 'integer | required',
        ]);

        $data = AkunJajan::find($request->id);
        $data->update([
            'batas_harian' => $request->batas_harian,
            'boleh_lebih' => $request->boleh_lebih,
        ]);

        return redirect(route('daftar_akun'))->with('success', 'Akun tabungan berhasil diperbarui!');
    }

    protected function hapus(Request $request) {
        $data = AkunJajan::find($request->id);
        $x = $data->delete();
        if($x) {
            return redirect(route('daftar_akun'))->with('success', 'Akun tabungan berhasil dihapus!');
        } else {
            return redirect(route('daftar_akun'))->with('error', 'Terjadi kesalahan saat akan menghapus akun tabungan!');            
        }

    }

    protected function detail(Request $request) {
        $akun = AkunJajan::where('siswa_id', $request->siswa_id)->first();

        if(!$akun) {
            return response()->json(['status' => false, 'pesan' => 'Akun jajan tidak ditemukan!']);
        }

        $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $pengambilan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->first();
        $penambahan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->first();
        $token_jurnal = sha1(time());
        $data = [
            'id' => $akun->id,
            'siswa_id' => $akun->siswa->id,
            'nama' => $akun->siswa->nama,
            'boleh_lebih' => $akun->boleh_lebih ? true : false,
            'pengambilan_hari_ini' => intval($pengambilan_hari_ini),
            'saldo' => $akun->saldo,
            'batas_harian' => $akun->batas_harian,
            'pengambilan_terakhir' => $pengambilan_terakhir,
            'penambahan_terakhir' => $penambahan_terakhir,
            'token_jurnal' => $token_jurnal,
            'rombel' => $akun->siswa->rombel_saat_ini->rombel,
        ];
        $data = (object)$data;

        $akun->token_jurnal = $token_jurnal;
        $akun->save();
        
        return view('data.jajan.detail_khusus', compact('data'));
        //return response()->json($data);
    }

    protected function detailKhusus(Request $request) {
        $akun = AkunJajan::where('siswa_id', $request->siswa_id)->first();

        if(!$akun) {
            return response()->json(['status' => false, 'pesan' => 'Akun jajan tidak ditemukan!']);
        }

        $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $pengambilan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->first();
        $penambahan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->first();
        $token_jurnal = sha1(time());
        $data = [
            'id' => $akun->id,
            'siswa_id' => $akun->siswa->id,
            'nama' => $akun->siswa->nama,
            'boleh_lebih' => $akun->boleh_lebih ? true : false,
            'pengambilan_hari_ini' => intval($pengambilan_hari_ini),
            'saldo' => $akun->saldo,
            'batas_harian' => $akun->batas_harian,
            'pengambilan_terakhir' => $pengambilan_terakhir,
            'penambahan_terakhir' => $penambahan_terakhir,
            'token_jurnal' => $token_jurnal,
            'rombel' => $akun->siswa->rombel_saat_ini->rombel,
        ];
        $data = (object)$data;

        $akun->token_jurnal = $token_jurnal;
        $akun->save();

        return view('data.jajan.detail_khusus', compact('data'));
    }

    protected function cari(Request $request) {
        $judul = 'DAFTAR AKUN TABUNGAN';
        $data = AkunJajan::whereHas('siswa', function($query) use ($request) {
            $query->where('nama','like', '%'.$request->keyword.'%');
        })->paginate(20);
        $data_siswa = Siswa::where('status', true)->get();

        return view('data.jajan.daftar', compact('judul', 'data', 'data_siswa'));
    }

    protected function cekSaldo(Request $request) {
        $judul = "CEK SALDO";
        $data_siswa = Siswa::has('akun_jajan')->get();

        return view('data.jajan.public.form_cek_saldo', compact('judul', 'data_siswa'));
    }

    protected function cariById(Request $request) {
        $akun = AkunJajan::where('siswa_id', $request->siswa_id)->first();

        if(!$akun) {
            return response()->json(['status' => false, 'pesan' => 'Akun jajan tidak ditemukan!']);
        }

        $pengambilan_hari_ini = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->whereDate('created_at', Carbon::today())->sum('nilai');
        $pengambilan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'KELUAR')->orderBy('id', 'DESC')->first();
        $penambahan_terakhir = JurnalJajan::where('siswa_id', $request->siswa_id)->where('jenis', 'MASUK')->orderBy('id', 'DESC')->first();
        $token_jurnal = sha1(time());
        $data = [
            'id' => $akun->id,
            'siswa_id' => $akun->siswa->id,
            'nama' => $akun->siswa->nama,
            'boleh_lebih' => $akun->boleh_lebih ? true : false,
            'pengambilan_hari_ini' => number_format($pengambilan_hari_ini),
            'saldo' => number_format($akun->saldo),
            'batas_harian' => $akun->batas_harian,
            'pengambilan_terakhir' => number_format($pengambilan_terakhir->nilai),
            'penambahan_terakhir' => $penambahan_terakhir,
            'rombel' => $akun->siswa->rombel_saat_ini->rombel ?? new \App\Models\Rombel(),
        ];
        $data = (object)$data;

        return response()->json(['data' => $data], 200);
    }

    protected function simpanPinBaru(Request $request) {
        
        $data = AkunJajan::where('siswa_id', $request->siswa_id)->first();
        if(!isset($request->pin) || trim($request->pin) === '') {
            return response()->json(['status' => false, 'pesan' => 'Pin harus diisi!']);
        }
        if(!isset($request->pin_baru) || trim($request->pin_baru) === '') {
            return response()->json(['status' => false, 'pesan' => 'Pin baru harus diisi!']);
        }
        if(strlen($request->pin_baru) !== 4 || !is_numeric($request->pin_baru)) {
            return response()->json(['status' => false, 'pesan' => 'Pin harus 4 digit angka!']);
        }
        if(!isset($request->pin_baru_konfirmasi) || trim($request->pin_baru_konfirmasi) === '') {
            return response()->json(['status' => false, 'pesan' => 'Pin konfirmasi harus diisi!']);
        }
        if(strlen($request->pin_baru_konfirmasi) !== 4 || !is_numeric($request->pin_baru_konfirmasi)) {
            return response()->json(['status' => false, 'pesan' => 'Pin harus 4 digit angka!']);
        }
        if($request->pin_baru !== $request->pin_baru_konfirmasi) {
            return response()->json(['status' => false, 'pesan' => 'Pin baru dan pin baru konfirmasi harus sama!']);
        }
        if(!$data) {
            return response()->json(['status' => false, 'pesan' => 'Akun tidak ditemukan!']);
        }
        if($request->pin !== $data->pin) {
            return response()->json(['status' => false, 'pesan' => 'Pin lama yang kamu masukan salah']);
        }

        $data->pin = $request->pin_baru;
        $data->save();

        return response()->json(['status' => true, 'pesan' => 'PIN kamu berhasil diganti...!']);
    }

    protected function gantiPin() {
        $judul = 'GANTI PIN';
        $data_siswa = Siswa::has('akun_jajan')->get();

        return view('data.jajan.form_ganti_pin', compact('judul', 'data_siswa'));
    }
}
