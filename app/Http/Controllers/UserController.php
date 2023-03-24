<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use \Auth;
use Illuminate\Support\Facades\Hash;
use Storage;
use File;

class UserController extends Controller
{
    //
    protected function daftar() {
        $data = User::paginate(20);
        $judul = 'DAFTAR PENGGUNA';
        $fa = 'users';

        return view('data.pengguna.daftar', compact('fa', 'judul', 'data'));
    }
    
    protected function edit(Request $request) {
        $data = User::find($request->id);
        $fa = 'fa fa-user-edit';
        $judul = 'Edit Pengguna';
        
        return view('data.pengguna.form_edit', compact('fa', 'judul', 'data'));
    }
    
    protected function update(Request $request) {
        $res = User::find($request->id);
        
        $res->name = $request->name;
        $res->save();
        
        return redirect()->back()->with('success', 'Berhasil diperbarui');
        
    }

    protected function tambah() {
        $judul = 'TAMBAH PENGGUNA BARU';
        $fa = 'user-plus';

        return view('data.pengguna.form', compact('fa', 'judul'));
    }

    protected function simpan(Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'level' => ['required', 'in:0,1,2,3', 'int', 'digits:1'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'level' => $request->level,
            'password' => Hash::make($request->password),
        ]);

        return redirect(route('daftar_pengguna'))->with('success', 'Pengguna baru telah ditambahkan!');
    }
    
    protected function hapus(Request $request) {
        if($request->id == 1) {
            return redirect()->back()->with('error', 'Untuk keamanan, akun yang anda pilih tidak dapat dihapus!');
        }
        
        $user = User::find($request->id);
        if($user->delete() > 0) {
            return response()->json(['status' => true] , 200);
        } else {
            return response()->json(['status' => false] , 200);
        }
        
        return redirect(route('daftar_pengguna'))->with('success', 'Akun pengguna berhasil dihapus');
    }
    
    protected function updatePassword(Request $request){
        $request->validate([
            'password' => 'required | string',
            'password_baru' => 'required | string | min:6 | required_with:konfirmasi_password_baru | same:konfirmasi_password_baru',
            'konfirmasi_password_baru' => 'required | min:6 ',
        ]);
        
        if(!Hash::check($request->password, Auth::user()->password)) return redirect()->back()->with('error', 'Password lama yang anda masukan salah!');
        
        Auth::user()->password = Hash::make($request->password_baru);
        Auth::user()->save();
        
        return redirect()->back()->with('success', 'Password berhasil diganti!');
    }
    
    protected function gantiPassword() {
        return view('data.user.ganti_password');
    }
    
    
    protected function lihatProfil() {
        if(!isset(Auth::user()->profil)) {
            Auth::user()->profil = new Profile();
        }
        return view('data.user.profil');
    }
    
    protected function editProfil() {
        $user = Auth::user();
        if(!isset($user->profil)) {
            $user->profil = new Profile();
        }
        
        $judul = 'EDIT PROFIL';
        $fa = 'user-edit';
        
        return view('data.user.edit_profil', compact('judul', 'fa', 'user'));
    }
    
    protected function updateProfilDiri(Request $request) {
        $request->validate([
                'name' => 'required | max:255',
                'email' => 'email | required',
            ], $request->all());
        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        $profil = Profile::where('user_id', $user->id)->firstOrNew();
        $profil->user_id = $user->id;
        $profil->tanggal_lahir = $request->tanggal_lahir;
	 	$profil->alamat = $request->alamat;
	 	$profil->no_kontak = $request->no_kontak;
	 	$profil->jabatan = $request->jabatan;
	 	$profil->tanggal_masuk = $request->tanggal_masuk;
	 	$profil->tempat_lahir = $request->tempat_lahir;
	 	$profil->pendidikan_terakhir = $request->pendidikan_terakhir;
	  	$profil->jurusan = $request->jurusan;
	  	$profil->keahlian = $request->keahlian;
	 	$profil->hobi = $request->hobi;
	 	$profil->save();
	 	
	 	return redirect(route('lihat_profil'))->with('success', 'Profil telah diperbarui!');
    }
    
    protected function simpanFoto(Request $request)
    {
        
        $validatedData = $request->validate([
         'foto_profil' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',

        ]);
        
        $folder = 'uploads/images/profile/';
        $file = $request->foto_profil;
        $fileName   = time() . $file->getClientOriginalName();
        $path   = $folder.$fileName;
        
        try {
            Storage::disk('public')->put($path, File::get($file));
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan file ke folder'], 200);
        }
        
        $profil = Profile::where('user_id', Auth::user()->id)->first();
        $old = $profil->foto;
        $profil->foto = $path;
        
        
        try {
            $profil->save();
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan path ke db'], 200);
        }

        Storage::disk('public')->delete($old);
        return response()->json(['status' => true, 'pesan' => '/'.$path], 200);

    }
}
