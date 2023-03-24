<?php

namespace App\Http\Controllers;

use App\Models\Madrasah;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Penghasilan;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use App\Models\TahunPelajaran;
use App\Models\ProfilSiswa;
use App\Models\Rombel;
use Illuminate\Http\Request;
use Storage;
use File;

class SiswaController extends Controller
{
    protected function index(Request $request) {
        $k = $request->keyword;
        //$siswa = \Auth::user()->siswa;
        $madrasah = \Auth::user()->madrasah;
        

        $siswas = Siswa::whereIn('madrasah_id', $madrasah->pluck('id'))
                    ->where('status', true)
                    ->where('is_deleted', false)
                    ->where(function($q) use ($k) {
                        return $q->where('nama', 'LIKE', '%' . $k . '%')
                                ->orWhere('nisn', $k )
                                ->orWhere('nis', $k );
                    })
                    ->with('rombel_saat_ini');

        if(!empty($request->sort)) {
            foreach( $request->sort as $key => $value) {
                $siswas = $siswas->orderBy($key, $value);
            }
        }

        $siswas = $siswas->paginate($request->per_page);

        return response()->json($siswas, 200);
    }

    protected function create(Request $request) {
        $request->validate([
            'madrasah_id' => 'integer | required',
            'nama' => 'max:225 | required',
            'tahun_masuk' => 'integer | required',
            'jenis_pendaftaran' => 'required',
            'jenis_kelamin' => 'required | in:L,P',
        ]);

        $data = Siswa::create($request->all());

        return response()->json(['siswa' => $data], 200);
    }

    protected function update(Request $request) {
        $request->validate([
            'madrasah_id' => 'integer | required',
            'nama' => 'max:225 | required',
            'tahun_masuk' => 'integer | required',
            'jenis_pendaftaran' => 'required',
            'jenis_kelamin' => 'required | in:L,P',
        ], $request->all());

        $siswa = Siswa::find($request->id);
        $madrasah = \Auth::user()->madrasah;
        $found = false;

        foreach ($madrasah as $value) {
            if($value->id == $siswa->madrasah_id) {
                $found = true;
                break;
            }
        }

        if($found) {
            $siswa->update([
                'madrasah_id' => $request->madrasah_id,
                'nama' => $request->nama,
                'tahun_masuk' => $request->tahun_masuk,
                'nisn' => $request->nisn,
                'jenis_kelamin' => $request->jenis_kelamin,
                'jenis_pendaftaran' => $request->jenis_pendaftaran,
            ]);
    
            return response()->json(['siswa' => $siswa], 200);
        } else {
            return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        }
    }

    protected function delete(Request $request) {

        $siswa = Siswa::find($request->id);
        $madrasah = \Auth::user()->madrasah;
        $found = false;

        foreach ($madrasah as $value) {
            if($value->id == $siswa->madrasah_id) {
                $found = true;
                break;
            }
        }

        if($found) {
            $siswa->is_deleted = true;
            $siswa->deleted_by = \Auth::user()->id;
            $siswa->deleted_at = date("Y-m-d H:i:s");
            $siswa->save();
            
            return response()->json(['siswa' => $siswa], 200);
    
        } else {
            return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        }
    }

    protected function tagihan(){
        $judul = 'Rekap Tagihan Siswa';
        $data = Siswa::where('status', true)->with('tagihan', function ($query) {
                                                    return $query->where('status', '=', 0);
                                                })->with('data_rombel')->get();
        
       // return dd($data);
        return view('data.siswa.tagihan', compact('data', 'judul'));
    }
    
    protected function profil(Request $request) {
        $siswa = Siswa::find($request->id);
        $judul = 'PROFIL SISWA';
        $fa = 'id-card';
        
        return view('data.siswa.profil_siswa', compact('siswa', 'judul', 'fa'));
    }
    
    protected function ppdb(Request $request) {
        if(isset($request->tp)) {
            $data_siswa = Siswa::where('tahun_masuk', $request->tp)->where('jenis_pendaftaran','BARU')->get();
            $data_tp = TahunPelajaran::all();
            $judul = 'DAFTAR PESERTA PPDB';
            $fa = 'user-plus';
            $tp_active = TahunPelajaran::where('tahun_mulai', $request->tp)->first();
            
            return view('data.ppdb.daftar_siswa', compact('data_siswa', 'judul', 'fa', 'data_tp', 'tp_active'));
        } else {
            $tp_active = TahunPelajaran::where('ppdb', true)->first();
            $data_siswa = Siswa::where('tahun_masuk', $tp_active->tahun_mulai)->where('jenis_pendaftaran','BARU')->get();
            $data_tp = TahunPelajaran::all();
            $judul = 'DAFTAR PESERTA PPDB';
            $fa = 'user-plus';
            
            return view('data.ppdb.daftar_siswa', compact('data_siswa', 'tp_active', 'judul', 'fa', 'data_tp', 'tp_active'));
        }
    }
    
    protected function tambahCalonSiswa() {
        $judul = 'FORM PENDAFTARAN PESERTA DIDIK BARU';
        $fa = 'tasks';
        $data_provinsi = Provinsi::all();
        $data_penghasilan = Penghasilan::all();
        $data_pekerjaan = Pekerjaan::all();
        $data_pendidikan = Pendidikan::all();
        $data_tahun_pelajaran = TahunPelajaran::all();
        $data_madrasah = Madrasah::all();
        
        return view('data.ppdb.form_pendaftaran', compact(
            'judul', 'fa', 'data_provinsi', 'data_penghasilan', 
            'data_pekerjaan', 'data_pendidikan', 'data_tahun_pelajaran', 'data_madrasah'));
        
    }
    
    protected function editProfil(Request $request) {
        $judul = 'FORM EDIT PROFIL';
        $fa = 'tasks';
        $data = Siswa::findOrFail($request->id);
        if(!$data->profil) {
            $data->profil = new ProfilSiswa();
        }
        
        //return dd($data);
        $data_provinsi = Provinsi::all();
        $data_kabupaten = Kabupaten::where('provinsi_id', $data->profil->provinsi_id)->get();
        $data_kecamatan = Kecamatan::where('kabupaten_id', $data->profil->kabupaten_id)->get();
        $data_desa = Desa::where('kecamatan_id', $data->profil->kecamatan_id)->get();
        $data_penghasilan = Penghasilan::all();
        $data_pekerjaan = Pekerjaan::all();
        $data_pendidikan = Pendidikan::all();
        $data_tahun_pelajaran = TahunPelajaran::all();
        $data_madrasah = Madrasah::all();
        
        return view('data.siswa.form_edit_profil', compact(
            'judul', 'fa', 'data_provinsi', 'data_penghasilan', 
            'data_pekerjaan', 'data_pendidikan', 'data_tahun_pelajaran', 'data_madrasah', 'data', 'data_kabupaten', 'data_kecamatan', 'data_desa'));
    }
    
    protected function uploadFoto(Request $request) {
        
        $validatedData = $request->validate([
         'foto_profil' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
         'webcam' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',

        ]);
        
        $folder = 'uploads/images/siswa/profile/';
        $file = $request->foto_profil ?? $request->webcam;
        $fileName   = time() . $file->getClientOriginalName();
        $path   = $folder.$fileName;
        
        try {
            Storage::disk('public')->put($path, File::get($file));
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan file ke folder'], 200);
        }
        
        $profil = Siswa::find($request->id);
        $old = $profil->foto;
        $profil->foto = $path;
        
        try {
            $profil->save();
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan path ke db'], 200);
        }

        Storage::disk('public')->delete($old);
        return response()->json(['status' => true, 'pesan' => $path, 'profil' => $profil], 200);
    }
    
    protected function simpanPPDB(Request $request) {
        //return dd($request->tanggal_lahir);
        $request->validate([
                'nama_lengkap' => 'required',
                'jenis_kelamin' => 'in:L,P',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required',
                'provinsi_id' => 'integer | required',
                'kecamatan_id' => 'integer | required',
                'kabupaten_id' => 'integer | required',
                'desa_id' => 'integer | required',
                'status_ibu' => 'in:1,2,3 | required',
                'status_ayah' => 'in:1,2,3 | required',
                'tanggal_lahir' => 'required',
                'pendidikan_ayah' => 'integer',
                'pendidikan_ibu' => 'integer',
                'penghasilan_id' => 'integer',
                'agama' => 'required',
                'madrasah_id' => 'integer | required',
                'verifikator' => 'string | max:225 | required',
            ], $request->all());
        
        $tp_active = TahunPelajaran::where('ppdb', true)->first();
        $siswa = Siswa::firstOrNew([
                'madrasah_id' => $request->madrasah_id,
                'golongan' => 'M',
                'nama' => strtoupper($request->nama_lengkap),
                'tahun_masuk' => $tp_active->tahun_mulai,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nisn' => $request->nisn,
                'status' => true,
                'skip_tagihan' => false,
            ]);
        $siswa->save();
        
        if($siswa) {
            $profile = ProfilSiswa::create(array_merge($request->except([
                    'madrasah_id',
                    'tahun_masuk',
                    'jenis_kelamin',
                    'nisn',
                    'nama_lengkap'
                ]), ['siswa_id' => $siswa->id, 'verifikator' => $request->verifikator ?? \Auth::user()->name ]));
                
            if(!$profile) {
                return redirect()->back()->with('error', 'Terjadi kesalahan saat akan menyimpan data Profile!');
            }
        } else {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat akan menyimpan data Siswa!');
        }
        
        return redirect(route('ppdb'))->with('success', 'Data siswa telah disimpan!');
    }
    
    protected function updateProfil(Request $request) {
        
        $request->validate([
                'nama_lengkap' => 'required',
                'jenis_kelamin' => 'in:L,P',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required',
                'provinsi_id' => 'integer | required',
                'kecamatan_id' => 'integer | required',
                'kabupaten_id' => 'integer | required',
                'desa_id' => 'integer | required',
                'status_ibu' => 'in:1,2,3 | required',
                'status_ayah' => 'in:1,2,3 | required',
                'tanggal_lahir' => 'required',
                'pendidikan_ayah' => 'integer',
                'pendidikan_ibu' => 'integer',
                'penghasilan_id' => 'integer',
                'agama' => 'required',
            ], $request->all());
        
        $siswa = Siswa::where('id', $request->id)->update([
                'nama' => strtoupper($request->nama_lengkap),
                'jenis_kelamin' => $request->jenis_kelamin,
                'nisn' => $request->nisn,
            ]);
            
        $old = isset($siswa->profil) ? $siswa->profil->foto : '';
        
        if($siswa) {
            
            if(isset($request->foto_profil)) {
                $validatedData = $request->validate([
                 'foto_profil' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        
                ]);
                
                $folder = 'uploads/images/siswa/profile/';
                $file = $request->foto_profil;
                $fileName   = time() . $file->getClientOriginalName();
                $path   = $folder.$fileName;
                
                try {
                    Storage::disk('public')->put($path, File::get($file));
                } catch (Exception $ex) {
                    return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan file ke folder'], 200);
                }
                
                try {
                    $request['foto'] = $path;
                    $profile = ProfilSiswa::where('siswa_id', $request->id)->firstOrNew()->update($request->except([
                        'madrasah_id',
                        'tahun_masuk',
                        'foto_profil',
                        'jenis_kelamin',
                        'nisn',
                        'nama_lengkap',
                        '_token'
                    ]));
                    
                    if(isset($siswa->profil)) {
                        Storage::disk('public')->delete($old);
                    }
                    
                if(!$profile) {
                    return redirect()->back()->with('error', 'Terjadi kesalahan saat akan mengupdate data Profile!');
                }
                } catch (Exception $ex) {
                    return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan path ke db'], 200);
                }
            } else {
               
                try {
                   
                    $profile = ProfilSiswa::where('siswa_id', $request->id)->firstOrNew()->update($request->except([
                        'madrasah_id',
                        'tahun_masuk',
                        'foto_profil',
                        'jenis_kelamin',
                        'nisn',
                        'nama_lengkap',
                        '_token'
                    ]));
                    
                if(!$profile) {
                    return redirect()->back()->with('error', 'Terjadi kesalahan saat akan mengupdate data Profile!');
                }
                } catch (Exception $ex) {
                    return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan path ke db'], 200);
                }
            }
        } else {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat akan mengupdate data Siswa!');
        }
        
        return redirect()->back()->with('success', 'Data siswa telah diupdate!');
    }
    
    protected function tambah() {
        $judul = 'Tambah Siswa';
        $data_madrasah = Madrasah::all();

        return view('data.siswa.form', compact('judul', 'data_madrasah'));
    }

    protected function simpan(Request $request) {
        $request->validate([
            'madrasah_id' => 'integer | required',
            'nama' => 'max:225 | required',
            'tahun_masuk' => 'integer | required',
            'jenis_pendaftaran' => 'required',
            'jenis_kelamin' => 'required | in:L,P',
        ]);

        Siswa::create($request->all());

        return redirect()->back()->with('success', 'Data siswa berhasil disimpan!');
    }

    
    
    protected function pilihMadrasah(Request $request) {
        $judul = 'DAFTAR SISWA';
        $data = Madrasah::all();
        
        return view('data.siswa.parkir', compact('data', 'judul'));
    }
    
    protected function list(Request $request) {
        $q = Siswa::where('madrasah_id', $request->madrasah_id)->whereHas('data_rombel', function($q) use ($request){
          
                $operand_tpid = $request->tahun_pelajaran_id == 0 ? '>' : '=';
                $operand_rbid = $request->rombel_id == 0 ? '>' : '=';
              
                return $q->where('tahun_pelajaran_id', $operand_tpid , $request->tahun_pelajaran_id)->where('rombel_id', $operand_rbid, $request->rombel_id); 
          })->with('madrasah')->with('rombel_saat_ini');
          
        if($request->hide_nonaktif) {
            $q = $q->where('status_siswa', true);
        }
      
        $recordsTotal = $q->get()->count();
        
        $data_new = Array();
        $draw = $request->draw;
        $columns = $request->columns;
        
       /*column[0] => array:5 [
          "data" => "id"
          "name" => null
          "searchable" => "false"
          "orderable" => "false"
          "search" => array:2 [
            "value" => null
            "regex" => "false"
          ]
        */
        $order = $request->order;
        /*
        order[0] => array:1 [
            0 => array:2 [
              "column" => "1"
              "dir" => "asc"
            ]
          ]
        */
        $start = $request->start;
        $length = $request->length < 0 ? $recordsTotal : $request->length;
        $search = $request->search;
        /*
        search => array:2 [
            "value" => null
            "regex" => "false"
          ]
        */
        
      //return dd($request->order);
      
      
      
        $recordsFiltered = $recordsTotal;
      
        if($search['value'] != null) {
            $param = ['col' => $columns, 'value' => $search['value']];
            
            $q = $q->where(function($q) use ($param){
                    foreach($param['col'] as $col) {
                        if($col['searchable'] == 'true') {
                            $q = $q->orWhere($col['name'], 'LIKE', '%' . $param['value'] . '%');
                        }
                    }
                });
          
        }
      
      $q->skip(intval($start))->take(intval($length));
      
      if(count($order) > 0) {
          foreach($order as $ord) {
              $q = $q->orderBy($columns[intval($ord['column'])]['name'], $ord['dir']);
          }
      }
      
      $data = $q->get();
      $recordsFiltered = $search['value'] != null ? $data->count() : $recordsTotal;
      return response()->json(['data' => $data, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered], 200);
      
    }
    
    protected function listPerRombel(Request $request) {
        if($request->rombel_id == 0) {
            $tp = TahunPelajaran::where('status', true)->first();
            $param = ['rombel_id' =>  $request->rombel_id, 'tp' => $tp->id];
            
            $data = Siswa::whereDoesntHave('data_rombel')->where('status', true)->get();
        } else {
            $tp = TahunPelajaran::where('status', true)->first();
            $param = ['rombel_id' =>  $request->rombel_id, 'tp' => $tp->id];
            
            $data = Siswa::whereHas('data_rombel', function($q) use ($param) {
                    return $q->where('rombel_id', $param['rombel_id'])->where('tahun_pelajaran_id', $param['tp']);
                })->with('rombel_saat_ini')->get();
        }
        
        
        return response()->json(['success' => true, 'data' => $data], 200);
    }

    protected function edit(Request $request)     {
        $data = Siswa::find($request->id);
        $judul = 'Edit Siswa';
        $data_madrasah = Madrasah::all();

        return view('data.siswa.form_edit', compact('data', 'judul', 'data_madrasah'));
    }

    protected function update_deprecated(Request $request) {

        $request->validate([
            'madrasah_id' => 'integer | required',
            'nama' => 'max:225 | required',
            'tahun_masuk' => 'integer | required',
            'jenis_pendaftaran' => 'required',
            'jenis_kelamin' => 'required | in:L,P',
        ], $request->all());

        $data = Siswa::find($request->id);
        
        $data->update([
            'madrasah_id' => $request->madrasah_id,
            'nama' => $request->nama,
            'tahun_masuk' => $request->tahun_masuk,
            'nis' => $request->nis,
            'nisn' => $request->nisn,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jenis_pendaftaran' => $request->jenis_pendaftaran,
        ]);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui!');
    }

    protected function hapus(Request $request) {
        
        $data = Siswa::find($request->id);
        
        if(count($data->tagihan) > 0) {
            return response()->json(['status' => false, 'pesan' => 'Siswa memiliki tagihan aktif, koordinasi terlebih dahulu dengan bagian keuangan!']);
        }
        
        $profil = ProfilSiswa::where('siswa_id', $data->id)->first();
        
        if(!$data) {
            return response()->json(['status' => false, 'pesan' => 'Data siswa tidak ditemukan!']);
        }
        
        if(!$data->delete()) {
            return response()->json(['status' => false, 'pesan' => 'Gagal saat menghapus siswa!']);
        }
        
        if(!$profil) {
            return response()->json(['status' => false, 'pesan' => 'Profil tidak ditemukan!']);
        }
        
        if(!$profil->delete()) {
            return response()->json(['status' => false, 'pesan' => 'Gagal saat menghapus profil!']);
        }
        
        return response()->json(['status' => true, 'pesan' => 'Data siswa telah dihapus!']);
    }
    
    protected function daftarSiswaPerMadrasah(Request $request) {
        $data = Siswa::where('madrasah_id', $request->id)->where('status', true)->get();
        return view('data.tagihan.tabel_siswa', compact('data'));
    }

    protected function daftarSiswaFromParkir(Request $request) {
        $data = Siswa::where('madrasah_id', $request->id)->where('status', true)->get();
        return view('data.siswa.daftar', compact('data'));
    }
    
    protected function gantiStatus(Request $request) {
        $siswa  = Siswa::find($request->id);
        if($siswa) {
            $siswa->status = $request->status;
            $siswa->save();
            $pesan = $siswa->status == 1? 'diaktifkan' : 'dinonaktifkan';
            return redirect(route('daftar_siswa'))->with('success', 'Data siswa berhasil '.$pesan.'!');
        } else {
            return redirect(route('daftar_siswa'))->with('error', 'Data siswa tidak ditemukan!');
        }
    }

    protected function terapkanStatus(Request $request) {
        
        if($request->id == null) return response()->json(['success' => false, 'message' => 'Anda belum memilih siswa!']);
        
        switch ($request->aksi) {
            case 'set-alumni':
                $val = ['alumni' => true, 'status_siswa' => false ];
                break;
            case 'unset-alumni':
                $val = ['alumni' => false ];
                break;
            case 'siswa-non-aktif':
                $val = ['status_siswa' => false ];
                break;
            case 'siswa-aktif':
                $val = ['status_siswa' => true ];
                break;
            case 'non-aktif':
                $val = ['status' => false ];
                break;
            case 'aktif':
                $val = ['status' => true ];
                break;
            case 'set-m':
                $val = ['golongan' => 'M' ];
                break;
            case 'set-pm':
                $val = ['golongan' => 'PM' ];
                break;
            case 'set-pp':
                $val = ['golongan' => 'PP' ];
                break;
            case 'stop-tagihan':
                $val = ['skip_tagihan' => true ];
                break;
            case 'lanjut-tagihan':
                $val = ['skip_tagihan' => false ];
                break;
            case 'on-aktif-tagihan':
                $val = ['skip_tagihan' => false, 'status' => true ];
                break;
            case 'off-aktif-tagihan':
                $val = ['skip_tagihan' => true, 'status' => false ];
                break;
            default:
                $val = null;
                break;
        }
        
       // return dd($request->aksi);
        
        Siswa::whereIn('id', $request->id)->update($val);
        
        return response()->json(['success' => true, 'message' => 'Status berhasil diterapkan']);
    }
    
    
    public function getSiswaById(Request $request) {
        $siswa = Siswa::with('profil')->where('id', $request->id)->first();
        
        return response()->json(['success' => true, 'result' => $siswa], 200);
    }
    
    public function getTagihanBySiswaId(Request $request) {
        $tagihan = Tagihan::with('tahun_pelajaran')->with('pembayaran')->with('item_pembayaran')->where('siswa_id', $request->id)->get();
        
        return response()->json(['success' => true, 'result' => $tagihan], 200);
    }
    
    public function setNis(Request $request) {
        $res = Array();
        
        foreach($request->data as $data) {
            $success = Siswa::where('id', $data['id'])->update(['nis' => $data['nis']]);
            
            $res[] = ['id' => $data['id'], 'status' => $success ? true : false];
        }
        
        return response()->json(['status' => 'true', 'data' => $res, 'pesan' => 'Berhasil'], 200);
    }
}
