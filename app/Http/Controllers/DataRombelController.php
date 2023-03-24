<?php

namespace App\Http\Controllers;

use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\DataRombel;
use App\Models\Madrasah;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class DataRombelController extends Controller
{
    protected function insert(Request $request) {
        if($request->siswa_id == null) return response()
        ->json(['code' => 402, 'message' => 'Anda belum memilih Siswa!', 'errors' => (object)[]], 200);

        
        $request->validate([
            'rombel_id' => 'integer | required',
            'tahun_pelajaran_id' => 'integer | required',
        ]);

        $data = Array();
        $total = 0;
        foreach($request->siswa_id as $id) {
            $res = DataRombel::updateOrCreate(
                    [ 
                        'rombel_id' => $request->rombel_id, 
                        'siswa_id' => $id, 
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id, 
                        'is_deleted' => false
                    ],
                    [
                        'rombel_id' => $request->rombel_id, 
                        'siswa_id' => $id, 
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id, 
                        'is_deleted' => false
                    ]
                );

            $data[] = $res;
            if($res->wasRecentlyCreated){
               $total += 1;
            }
            
        }

        $d['rombel_id'] = $request->rombel_id;
        $d['tahun_pelajaran_id'] = $request->tahun_pelajaran_id;

        $data = DataRombel::where('rombel_id', $d['rombel_id'])
                    ->where('tahun_pelajaran_id', $d['tahun_pelajaran_id'])
                    ->with('siswa', function($q){
                        return $q->with('rombel_saat_ini');
                    })
                    ->where('is_deleted', false)
                    ->get();

        return response()->json(['siswas' => $data->pluck('siswa') ], 200);
    }


    protected function remove(Request $request) {
        $tp = \Auth::user()->tahun_pelajaran;

        $data_rombel = DataRombel::whereIn('tahun_pelajaran_id', $tp->pluck('id'))
                        ->where('id', $request->data_rombel_id)->first();
        if($data_rombel) {
            $data_rombel->is_deleted = true;
            $data_rombel->deleted_by = \Auth::user()->id;
            $data_rombel->deleted_at = date("Y-m-d H:i:s");
            $data_rombel->save();
            
            return response()->json(['rombel' => $data_rombel], 200);
    
        } else {
            return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        }
    }

    protected function tambah() {
        $judul = 'Tambah Data Rombel';
        $data_madrasah = Madrasah::all();
        $data_tahun_pelajaran = TahunPelajaran::all();

        return view('data.data_rombel.form', compact('judul', 'data_tahun_pelajaran', 'data_madrasah'));
    }

    protected function simpan(Request $request) {
        if($request->siswa_id == null) return redirect()->back()->with('error', 'Anda belum memilih siswa..!');
        
        $request->validate([
            'rombel_id' => 'integer | required',
            'tahun_pelajaran_id' => 'integer | required',
        ]);

        $data = Array();
        $total = 0;
        foreach($request->siswa_id as $id) {
            $res = DataRombel::updateOrCreate(
                    ['rombel_id' => $request->rombel_id, 'siswa_id' => $id, 'tahun_pelajaran_id' => $request->tahun_pelajaran_id],
                    ['rombel_id' => $request->rombel_id, 'siswa_id' => $id, 'tahun_pelajaran_id' => $request->tahun_pelajaran_id]
                );
            if($res->wasRecentlyCreated){
               $total += 1;
            }
            
        }
        
        return redirect(route('atur_penempatan'))->with('success', $total.' data rombel berhasil disimpan!');
    }

    protected function daftar() {
        $data = Rombel::all();
        $judul = 'Daftar Data Rombel';

        return view('data.data_rombel.daftar', compact('data', 'judul'));
    }

    protected function edit(Request $request)     {
        $data = DataRombel::find($request->id);
        $judul = 'Edit Data Rombel';
        $data_madrasah = Madrasah::all();
        $data_tahun_pelajaran = TahunPelajaran::all();

        return view('data.rombel.', compact('data', 'judul', 'data_tahun_pelajaran', 'data_madrasah'));
    }

    protected function update(Request $request) {
        $request->validate([
            'kode' => 'max:12 | required',
            'madrasah_id' => 'integer | required',
            'kelas' => 'integer | required',
            'tahun_pelajaran_id' => 'required',
        ], $request->all());

        $data = DataRombel::find($request->id);
        $data->update([
            'kode' => $request->kode,
            'madrasah_id' => $request->madrasah_id,
            'kelas' => $request->kelas,
            'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
        ]);

        return redirect(route('daftar_data_rombel'))->with('success', 'Data rombel brehasil diperbarui!');
    }

    protected function hapus(Request $request) {
        $data = DataRombel::where('rombel_id', $request->id)->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)->delete();
        
        return response()->json(['status' => true, 'message' => 'Data rombel telah dihapus'], 200);
    }
    
    protected function aturPenempatan() {
        $judul = 'ATUR PENEMPATAN SISWA';
        $data_tahun_pelajaran = TahunPelajaran::all();
        $rombel = Rombel::all();
        $tp_active = TahunPelajaran::where('status', true)->first();
        $madrasah = Madrasah::all();
        
        $data_rombel = Rombel::whereHas('data_rombel', function($q) use ($tp_active) {
                    return $q->where('tahun_pelajaran_id', $tp_active->id);
                })->get();

        return view('data.rombel.atur_penempatan', compact('data_tahun_pelajaran', 'tp_active', 'data_rombel', 'rombel', 'madrasah', 'judul'));
    }
    

    protected function tambah_siswa(Request $request) {
        $judul = 'EDIT DAFTAR SISWA';
        $rombel = Rombel::where('id', $request->id)->with('data_rombel')->first();
        $data_siswa = Siswa::where('status', 1)->where('madrasah_id', $rombel->madrasah_id)->whereDoesntHave('data_rombel')->get();
        $tp = TahunPelajaran::find($request->tahun_pelajaran_id);
        return view('data.rombel.daftar_siswa', compact('data_siswa', 'rombel', 'judul', 'tp'));
    }
    
    public function dataRombelPerTp(Request $request) {
        $data = DataRombel::select('rombel_id')->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
        ->groupBy('rombel_id')->whereHas('rombel', function($q) use ($request){
            $q->where('madrasah_id', $request->madrasah_id);
        })->with('rombel')->get();
        
        $options = '<option value="0">Semua Rombel</option>';
        foreach($data as $r) {
            $options.= '<option value="'.$r->rombel_id.'">'.$r->rombel->kode.'</option>';
        }
        
        return response()->json(['success' => true, 'results' => $data, 'options' => $options], 200);
    }
}
