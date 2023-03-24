<?php

namespace App\Http\Controllers;

use App\Models\DataRombel;
use App\Models\Rombel;
use App\Models\Madrasah;
use App\Models\TahunPelajaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RombelController extends Controller
{
    protected function index(Request $request) {

        $tahun_p = TahunPelajaran::where('user_id', \Auth::user()->id)->get();
        $mad = Madrasah::where('user_id', \Auth::user()->id)->get();

        $tp = $request->tahun_pelajaran_id == null ? 
            TahunPelajaran::where('status', true)->first() : 
            TahunPelajaran::find($request->tahun_pelajaran_id);
        
        $data = Rombel::where('madrasah_id', $request->madrasah_id)
                ->where('is_deleted', false)
                ->orderBy('kelas', 'asc')
                ->orderBy('kode', 'asc')->get();
        return response()->json(['rombels' => $data ], 200);
    }

    protected function show(Request $request) {
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


    protected function create(Request $request) {

        $request->validate([
            'kode' => 'max:12 | required',
            'madrasah_id' => 'integer | required',
            'kelas' => 'integer | required',
        ]);

        $madrasah = \Auth::user()->madrasah->where('id', $request->madrasah_id)->first();

        $rombel = Rombel::create([
            'kode' => $request->kode,
            'madrasah_id' => $madrasah->id,
            'kelas' => $request->kelas,
        ]);

        return response()->json(['rombel' => $rombel], 200);
    }


    protected function update(Request $request) {
        
        $madrasah = \Auth::user()->madrasah->pluck('id');


        if (!in_array($request->madrasah_id, $madrasah->toArray())) return response()->json(['code' => 404, 'message' => 'Resource not found!', 'errors' => (object)[]], 200);

        $request->validate([
            'kode' => 'max:12 | required',
            'madrasah_id' => 'integer | required',
            'kelas' => 'integer | required',
        ], $request->all());

        $data = Rombel::find($request->id);

        $data->update([
            'kode' => $request->kode,
            'madrasah_id' => $request->madrasah_id,
            'kelas' => $request->kelas,
        ]);

        return response()->json(['rombel' => $data], 200);
    }

    protected function delete(Request $request) {

        $madrasah = \Auth::user()->madrasah->pluck('id');
        $rombel = Rombel::find($request->id);

        if (!in_array($rombel->madrasah_id, $madrasah->toArray())) return response()->json(['code' => 404, 'errors' => (object)[], 'message' => 'Data not found!'], 404);

        $rombel->is_deleted = true;
        $rombel->deleted_by = \Auth::user()->id;
        $rombel->deleted_at = date("Y-m-d H:i:s");
        $rombel->save();

        return response()->json(['rombel' => $rombel], 200);
    }

    protected function list(Request $r) {
        $madrasah = Madrasah::where('user_id', \Auth::user()->id)->get();
        
        $madrasah = $madrasah->where('id', $r->madrasah_id)->first();

        if(!$madrasah) return response()->json([
            'code' => 404, 
            'errors' => (object)[], 
            'message' => 'Data not found!'], 
        404);

        $rombels = Rombel::where('madrasah_id', $madrasah->id)
                    ->where('is_deleted', false)
                    ->orderBy('kelas', 'asc')
                    ->orderBy('kode', 'asc')->get();
        return response()->json(['rombels' => $rombels], 200);
    }


    protected function daftar(Request $request) {
        $tp = TahunPelajaran::all();
        
        foreach($tp as $t) {
            $data_rombel = Rombel::whereHas('data_rombel', function($q) use($t){
                return $q->where('tahun_pelajaran_id', $t->id);
            })->get();
            $total = count($data_rombel);
            
            $dt = [
                'tahun_pelajaran' => $t,
                'total_rombel' => $total,
                'data_rombel' => [
                    'TK' => $data_rombel->where('kelas', 0)->count(),
                    'I' => $data_rombel->where('kelas', 1)->count(),
                    'II' => $data_rombel->where('kelas', 2)->count(),
                    'III' => $data_rombel->where('kelas', 3)->count(),
                    'IV' => $data_rombel->where('kelas', 4)->count(),
                    'V' => $data_rombel->where('kelas', 5)->count(),
                    'VI' => $data_rombel->where('kelas', 6)->count(),
                    'VII' => $data_rombel->where('kelas', 7)->count(),
                    'VIII' => $data_rombel->where('kelas', 8)->count(),
                    'IX' => $data_rombel->where('kelas', 9)->count(),
                    'X' => $data_rombel->where('kelas', 10)->count(),
                    'XI' => $data_rombel->where('kelas', 11)->count(),
                    'XII' => $data_rombel->where('kelas', 12)->count(),
                ],
            ];
            $data[] = (object)$dt;
        }
        
        $data_madrasah = Madrasah::all();
        $judul = 'DAFTAR ROMBEL';

        return view('data.rombel.daftar', compact('data', 'data_madrasah', 'judul'));
    }
    
    protected function detail(Request $request) {
        $tp = $request->id == null ? TahunPelajaran::where('status', true)->first() : TahunPelajaran::find($request->id);
        
        $data = Rombel::whereHas('data_rombel', function($q) use ($tp) {
                    return $q->where('tahun_pelajaran_id', $tp->id);
                })->get();
        $judul = 'Daftar Data Rombel';
        
        //return dd($data);

        return view('data.rombel.detail', compact('data', 'judul', 'tp'));
    }

    protected function tambah() {
        $judul = 'PENGATURAN ROMBEL';
        $data_madrasah = Madrasah::all();
        $data_tahun_pelajaran = TahunPelajaran::all();
        $data = Rombel::all();

        return view('data.rombel.form', compact('judul', 'data', 'data_tahun_pelajaran', 'data_madrasah'));
    }

    protected function simpan(Request $request) {
        $request->validate([
            'kode' => 'max:12 | required',
            'madrasah_id' => 'integer | required',
            'kelas' => 'integer | required',
        ]);

        Rombel::create($request->all());

        return redirect(route('tambah_rombel'))->with('success', 'Data rombel berhasil disimpan!');
    }

    protected function edit(Request $request)     {
        $data = Rombel::find($request->id);
        $judul = 'Edit Data Rombel';
        $data_madrasah = Madrasah::all();
        $data_tahun_pelajaran = TahunPelajaran::all();

        return view('data.rombel.form_edit', compact('data', 'judul', 'data_tahun_pelajaran', 'data_madrasah'));
    }

    

    protected function hapus(Request $request) {
        $data = Rombel::find($request->id);
        $data->delete();
        return redirect(route('daftar_rombel'))->with('success', 'Data rombel brehasil dihapus!');
    }


    protected function simpan_siswa(Request $request) {
        if(!$request->siswa_id || !$request->rombel_id) {
            return response()->json(['status' => false, 'pesan' => 'Error: Data belum lengkap!']);
        }
        
        $tp = TahunPelajaran::where('status', true)->first();


        $data = DataRombel::create([
            'rombel_id' => $request->rombel_id,
            'siswa_id' => $request->siswa_id,
            'tahun_pelajaran_id' => $tp->id,
        ]);

        return response()->json(['status' => true, 'pesan' => $data]);

    }

    protected function hapus_siswa(Request $request) {
        $data_rombel = DataRombel::find($request->data_rombel_id);
        $data_rombel->delete();

        return response()->json(['status' => true, 'pesan' => $data_rombel]);
    }
    
    
}
