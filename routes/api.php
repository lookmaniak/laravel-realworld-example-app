<?php

use App\Http\Controllers\Api\Articles\ArticleController;
use App\Http\Controllers\Api\Articles\CommentsController;
use App\Http\Controllers\Api\Articles\FavoritesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TagsController;
use App\Http\Controllers\Api\UserController;
//use App\Http\Controllers\Api\MadrasahController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DataRombelController;
use App\Http\Controllers\MadrasahController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemPembayaranController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TahunPelajaranController;
use App\Http\Controllers\AkunJajanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\JurnalJajanController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\TahunAnggaranController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\JurnalInOutController;
use App\Http\Controllers\EmisController;
use App\Http\Controllers\PengeluaranController;
use App\Models\Tagihan;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')->group(function () {
    Route::name('users.')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('user', [UserController::class, 'show'])->name('current');
            Route::put('user', [UserController::class, 'update'])->name('update');
            Route::get('user/profile', [UserController::class, 'showProfile'])->name('profile');
            Route::put('user/profile', [UserController::class, 'updateProfile'])->name('update.profile');
            Route::get('user/accounts', [UserController::class, 'getAccounts'])->name('get.accounts');
            Route::post('user/accounts', [UserController::class, 'saveUserAccount']);
            Route::delete('user/accounts/{username}', [UserController::class, 'destroyAccount']);
        });

        Route::post('users/login', [AuthController::class, 'login'])->name('login');
        Route::post('users/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('users', [AuthController::class, 'register'])->name('register');
    });


    Route::name('oraganizations.')->group(function(){
        Route::middleware('auth:api')->group(function() {
            Route::get('organizations', [MadrasahController::class, 'index'])->name('list');
            Route::post('organizations', [MadrasahController::class, 'create']);
            Route::put('organizations', [MadrasahController::class, 'update']);
        });
    });


    Route::name('data.')->group(function(){
        Route::middleware('auth:api')->group(function() {
            Route::get('provinces', [DataController::class, 'getProvinces'])->name('provinces');
            Route::get('cities/{province_id}', [DataController::class, 'getCities'])->name('cities');
            Route::get('districts/{city_id}', [DataController::class, 'getDistricts'])->name('districs');
            Route::get('villages/{district_id}', [DataController::class, 'getVillages'])->name('villages');
        });
    });

    Route::name('profiles.')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::post('profiles/{username}/follow', [ProfileController::class, 'follow'])->name('follow');
            Route::delete('profiles/{username}/follow', [ProfileController::class, 'unfollow'])->name('unfollow');
        });

        Route::get('profiles/{username}', [ProfileController::class, 'show'])->name('get');
    });

    Route::name('articles.')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('articles/feed', [ArticleController::class, 'feed'])->name('feed');
            Route::post('articles', [ArticleController::class, 'create'])->name('create');
            Route::put('articles/{slug}', [ArticleController::class, 'update'])->name('update');
            Route::delete('articles/{slug}', [ArticleController::class, 'delete'])->name('delete');
        });

        Route::get('articles', [ArticleController::class, 'list'])->name('list');
        Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('get');

        Route::name('comments.')->group(function () {
            Route::middleware('auth:api')->group(function () {
                Route::post('articles/{slug}/comments', [CommentsController::class, 'create'])->name('create');
                Route::delete('articles/{slug}/comments/{id}', [CommentsController::class, 'delete'])->name('delete');
            });

            Route::get('articles/{slug}/comments', [CommentsController::class, 'list'])->name('get');
        });

        Route::name('favorites.')->group(function () {
            Route::middleware('auth:api')->group(function () {
                Route::post('articles/{slug}/favorite', [FavoritesController::class, 'add'])->name('add');
                Route::delete('articles/{slug}/favorite', [FavoritesController::class, 'remove'])->name('remove');
            });
        });
    });

    Route::name('tags.')->group(function () {
        Route::get('tags', [TagsController::class, 'list'])->name('list');
    });
});
 
/*

Route::get('/f', function(){
    return view('layouts.full_height');
});
Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dev/cancel-pembayaran', [PembayaranController::class, 'cancelPembayaran']);


Route::group(['prefix' => 'emis'], function(){
   Route::get('/', [EmisController::class, 'dataSiswa'])->name('data_siswa'); 
});

Route::get('/json-upload', function(){
    return view('data.rdm.jsonviewer');
})->name('upload_json_data');

Route::post('/json-upload', [HomeController::class, 'jsonViewer']);

//Auth::routes(['register' => false]);


*/


Route::middleware('auth:api')->group(function() {
    Route::get('/siswa-menunggak', [TagihanController::class, 'siswaMenunggak']);
    Route::post('/siswa-menunggak', [TagihanController::class, 'siswaMenunggak']);

    Route::group(['prefix' => 'data'], function () {
        Route::get('jenjangs', [DataController::class, 'getJenjang']);
    });

    Route::group(['prefix' => 'madrasah'], function() {
        Route::get('/', [MadrasahController::class, 'index']);
        Route::post('/', [MadrasahController::class, 'create']);
        Route::put('/{id}', [MadrasahController::class, 'update'])->name('update_madrasah');
        Route::delete('/{id}', [MadrasahController::class, 'delete'])->name('hapus_madrasah');
        //Route::get('tambah', [MadrasahController::class, 'tambah'])->name('tambah_madrasah');
        //Route::get('/{id}/edit', [MadrasahController::class, 'edit'])->name('edit_madrasah');
    });

    Route::group(['prefix' => 'tahun-pelajaran'], function() {
        Route::get('/', [TahunPelajaranController::class, 'index'])->name('daftar_tahun_pelajaran');
        Route::post('/', [TahunPelajaranController::class, 'create']);
        Route::put('/{id}', [TahunPelajaranController::class, 'update']);
        Route::delete('/{id}', [TahunPelajaranController::class, 'delete']);
        
        Route::put('/aktif/{id}', [TahunPelajaranController::class, 'setActive']);
        Route::put('/aktif-ppdb/{id}', [TahunPelajaranController::class, 'setActivePPDB']);
    });

    Route::group(['prefix' => 'siswa'], function() {
        
        Route::get('/', [SiswaController::class, 'index']);
        Route::post('/', [SiswaController::class, 'create']);
        Route::put('/{id}', [SiswaController::class, 'update']);
        Route::delete('/{id}', [SiswaController::class, 'delete']);
    /*
        Route::post('/terapkan-nis', [SiswaController::class, 'setNis'])->name('set_nis');
        Route::get('/pilih-madrasah', [SiswaController::class, 'pilihMadrasah'])->name('pilih_madrasah_siswa');
        Route::get('daftar/{id}', [SiswaController::class, 'daftarSiswaPerMadrasah'])->name('daftar_siswa_per_madrasah');
        Route::get('/list/{madrasah_id}', [SiswaController::class, 'list'])->name('list_siswa');
        Route::get('/list/{madrasah_id}/{tahun_pelajaran_id}/{rombel_id}/{hide_nonaktif}', [SiswaController::class, 'list'])->name('list_siswa_based_rombel');
        Route::get('tambah', [SiswaController::class, 'tambah'])->name('tambah_siswa');
        Route::get('/ppdb', [SiswaController::class, 'ppdb'])->name('ppdb');
        Route::get('/ppdb/tambah-calon-siswa', [SiswaController::class, 'tambahCalonSiswa'])->name('tambah_calon_siswa');
        Route::post('/ppdb/simpan', [SiswaController::class, 'simpanPPDB'])->name('simpan_siswa_baru');
        Route::post('terapkan-status', [SiswaController::class, 'terapkanStatus'])->name('terapkan_status');
        Route::post('simpan', [SiswaController::class, 'simpan'])->name('simpan_siswa');
        Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit_siswa');
        Route::get('/profil/{id}/edit', [SiswaController::class, 'editProfil'])->name('edit_profil');
        Route::post('/{id}/update', [SiswaController::class, 'update'])->name('update_siswa');
        Route::post('/profil/{id}/update', [SiswaController::class, 'updateProfil'])->name('update_profil_siswa');
        Route::get('/{id}/hapus', [SiswaController::class, 'hapus'])->name('hapus_siswa');
        Route::get('/list-per-rombel/{rombel_id}', [SiswaController::class, 'listPerRombel'])->name('list_siswa_per_rombel');
        Route::post('/upload-foto', [SiswaController::class, 'uploadFoto'])->name('upload_foto_siswa');
        Route::get('/{id}/profil', [SiswaController::class, 'profil'])->name('profil_siswa');
        */
    });

    Route::group(['prefix' => 'rombel'], function() {
        Route::get('/{madrasah_id}/{tahun_pelajaran_id}', [RombelController::class, 'index']);
        Route::get('/list/{rombel_id}/{tahun_pelajaran_id}', [RombelController::class, 'show']);
        Route::get('/{madrasah_id}', [RombelController::class, 'list']);
        Route::post('/', [RombelController::class, 'create']);
        Route::put('/{id}', [RombelController::class, 'update']);
        Route::delete('/{id}', [RombelController::class, 'delete']);
        
        Route::post('/data/insert', [DataRombelController::class, 'insert']);
        Route::delete('/data/remove/{data_rombel_id}', [DataRombelController::class, 'remove']);
        /*
        Route::get('/', [RombelController::class, 'daftar'])->name('daftar_rombel');
        Route::get('/{id}/detail', [RombelController::class, 'detail'])->name('detail_rombel');
        Route::get('/tambah', [RombelController::class, 'tambah'])->name('tambah_rombel');
        Route::get('/{id}/edit-data-siswa', [DataRombelController::class, 'tambah_siswa'])->name('tambah_siswa_ke_rombel');
        Route::post('/simpan-ke-rombel', [RombelController::class, 'simpan_siswa'])->name('simpan_siswa_ke_rombel');
        Route::post('/hapus-siswa', [RombelController::class, 'hapus_siswa'])->name('hapus_siswa_dari_rombel');
        Route::get('/{id}/edit', [RombelController::class, 'edit'])->name('edit_rombel');
        Route::post('/{id}/update', [RombelController::class, 'update'])->name('update_rombel');
        Route::get('/{id}/hapus', [RombelController::class, 'hapus'])->name('hapus_rombel');
        */
    });

    Route::group(['prefix' => 'item-pembayaran'], function() {
        Route::get('/{madrasah_id}', [ItemPembayaranController::class, 'index'])->name('item_pembayaran_index');
        Route::put('/{id}', [ItemPembayaranController::class, 'update']);
        Route::delete('/{id}', [ItemPembayaranController::class, 'delete']);
        Route::post('/', [ItemPembayaranController::class, 'create']);
        /*
        Route::get('/', [ItemPembayaranController::class, 'daftar'])->name('daftar_item_pembayaran');
        Route::get('tambah', [ItemPembayaranController::class, 'tambah'])->name('tambah_item_pembayaran');
        Route::post('simpan', [ItemPembayaranController::class, 'simpan'])->name('simpan_item_pembayaran');
        Route::get('/{id}/edit', [ItemPembayaranController::class, 'edit'])->name('edit_item_pembayaran');
        Route::post('/{id}/edit', [ItemPembayaranController::class, 'update']);
        Route::get('/{id}/hapus', [ItemPembayaranController::class, 'hapus'])->name('hapus_item_pembayaran');
        Route::get('/detail/{id}', [ItemPembayaranController::class, 'detailItemPembayaran'])->name('detail_item_pembayaran');
        */
    });

    Route::group(['prefix' => 'tagihan'], function() {
        Route::get('/siswa-no-tagihan/{madrasah_id}/{tahun_pelajaran_id}/{item_pembayaran_id}', [TagihanController::class, 'indexSiswaHasTagihan']);
      
        /*
        Route::get('/', [ItemPembayaranController::class, 'daftar'])->name('daftar_item_pembayaran');
        Route::get('tambah', [ItemPembayaranController::class, 'tambah'])->name('tambah_item_pembayaran');
        Route::post('simpan', [ItemPembayaranController::class, 'simpan'])->name('simpan_item_pembayaran');
        Route::get('/{id}/edit', [ItemPembayaranController::class, 'edit'])->name('edit_item_pembayaran');
        Route::post('/{id}/edit', [ItemPembayaranController::class, 'update']);
        Route::get('/{id}/hapus', [ItemPembayaranController::class, 'hapus'])->name('hapus_item_pembayaran');
        Route::get('/detail/{id}', [ItemPembayaranController::class, 'detailItemPembayaran'])->name('detail_item_pembayaran');
        */
    });
});


/*





Route::group(['prefix' => 'dashboard', 'middleware' => 'auth:api'], function() {
    

    Route::get('/', [HomeController::class, 'redirector'])->name('redirector');
    Route::get('/data/kabupaten/{id}', [App\Http\Controllers\DataController::class, 'kabupaten'])->name('data_kabupaten');
    Route::get('/data/kecamatan/{id}', [App\Http\Controllers\DataController::class, 'kecamatan'])->name('data_kecamatan');
    Route::get('/data/desa/{id}', [App\Http\Controllers\DataController::class, 'desa'])->name('data_desa');
    Route::post('/profil/upload-foto', [UserController::class, 'simpanFoto'])->name('upload_profil_foto');
    Route::get('/profil/edit', [UserController::class, 'editProfil'])->name('edit_profil_diri');
    
    
    Route::group(['prefix' => 't', 'middleware' => 'teller_access'], function(){
        Route::get('/', [JurnalJajanController::class, 'ringkasan'])->name('dashboard_teller');
        Route::get('/cek-saldo', [AkunJajanController::class, 'cekSaldo'])->name('cek_saldo');
        Route::get('/lihat-jurnal/{id}', [JurnalJajanController::class, 'lihatJurnal'])->name('lihat_jurnal');
        Route::get('/tarik-tunai', [JurnalJajanController::class, 'daftarPengambilan'])->name('daftar_pengambilan');
        Route::get('/ganti-pin', [AkunJajanController::class, 'gantiPin'])->name('ganti_pin');
        Route::post('/simpan-pin-baru', [AkunJajanController::class, 'simpanPinBaru'])->name('simpan_pin_baru');
        Route::get('/reset-token-jurnal/{id}', [JurnalJajanController::class, 'resetTokenJurnal'])->name('reset_token_jurnal');
        Route::post('/detail', [AkunJajanController::class, 'detail']);
        Route::get('/cari-by-id/{siswa_id}', [AkunJajanController::class, 'cariById']);
        Route::post('/pengambilan/simpan', [JurnalJajanController::class, 'simpanPengambilan'])->name('simpan_pengambilan');
    });

    Route::group(['prefix' => 'a'], function(){
        Route::get('/', [HomeController::class, 'index'])->name('dashboard_admin');
        
        Route::group(['prefix' => 'note'], function(){
           Route::get('/', [NoteController::class, 'daftar'])->name('daftar_note'); 
           Route::get('tambah', [NoteController::class, 'tambah'])->name('tambah_note'); 
           Route::post('simpan', [NoteController::class, 'simpan'])->name('simpan_note'); 
           Route::get('/{id}/edit', [NoteController::class, 'edit'])->name('edit_note'); 
           Route::post('/{id}/update', [NoteController::class, 'update'])->name('update_note'); 
           Route::get('/{id}/hapus', [NoteController::class, 'hapus'])->name('hapus_note'); 
           Route::get('/{id}/close', [NoteController::class, 'close'])->name('close_note'); 
           Route::get('/{id}/pin', [NoteController::class, 'pinpost'])->name('pin_note'); 
           Route::get('/{id}/unpin', [NoteController::class, 'unpinpost'])->name('unpin_note'); 
        });
        
        Route::group(['prefix' => 'profil'], function(){
            Route::get('password/ganti', [UserController::class, 'gantiPassword'])->name('ganti_password'); 
            Route::post('password/update', [UserController::class, 'updatePassword'])->name('update_password');
            Route::post('update', [UserController::class, 'updateProfilDiri'])->name('update_profil_diri'); 
            Route::get('/', [UserController::class, 'lihatProfil'])->name('lihat_profil');
        });
        
        Route::group(['prefix' => 'f', 'middleware' => 'finance_access'], function() {
            
            Route::group(['prefix' => 'tahun-pelajaran'], function() {
                Route::get('/{madrasah_id}', [TahunPelajaranController::class, 'daftarPerMadrasah'])->name('daftar_tp_per_madrasah');
            });
            
            Route::group(['prefix' => 'pengeluaran'], function(){
                Route::get('/', [PengeluaranController::class, 'daftarPengeluaran'])->name('daftar_pengeluaran'); 
            });
            
            Route::group(['prefix' => 'siswa'], function() {
                Route::get('/{id}/ganti-status/{status}', [SiswaController::class, 'gantiStatus'])->name('ganti_status');
                
                Route::post('/terapkan-nis', [SiswaController::class, 'setNis'])->name('set_nis');
                Route::get('tagihan', [SiswaController::class, 'tagihan'])->name('tagihan_siswa');
                Route::get('daftar/{id}', [SiswaController::class, 'daftarSiswaPerMadrasah'])->name('daftar_siswa_per_madrasah');
                Route::get('/per-madrasah/{madrasah_id}', [SiswaController::class, 'daftar'])->name('daftar_siswa');
                Route::get('/pilih-madrasah', [SiswaController::class, 'pilihMadrasah'])->name('pilih_madrasah_siswa');
                Route::get('/list/{madrasah_id}', [SiswaController::class, 'list'])->name('list_siswa');
                Route::get('/list/{madrasah_id}/{tahun_pelajaran_id}/{rombel_id}/{hide_nonaktif}', [SiswaController::class, 'list'])->name('list_siswa_based_rombel');
                Route::get('/{id}/profil', [SiswaController::class, 'profil'])->name('profil_siswa');
            });
            
            Route::get('/ringkasan-penerimaan', [HomeController::class, 'ringkasanPenerimaan'])->name('ringkasan_penerimaan');
        
            Route::group(['prefix' => 'item-pembayaran'], function() {
                Route::get('/', [ItemPembayaranController::class, 'daftar'])->name('daftar_item_pembayaran');
                Route::get('tambah', [ItemPembayaranController::class, 'tambah'])->name('tambah_item_pembayaran');
                Route::post('simpan', [ItemPembayaranController::class, 'simpan'])->name('simpan_item_pembayaran');
                Route::get('/{id}/edit', [ItemPembayaranController::class, 'edit'])->name('edit_item_pembayaran');
                Route::post('/{id}/edit', [ItemPembayaranController::class, 'update']);
                Route::get('/{id}/hapus', [ItemPembayaranController::class, 'hapus'])->name('hapus_item_pembayaran');
                Route::get('/{madrasah_id}', [ItemPembayaranController::class, 'daftarPerMadrasah'])->name('daftar_item_pembayaran_per_madrasah');
                Route::get('/detail/{id}', [ItemPembayaranController::class, 'detailItemPembayaran'])->name('detail_item_pembayaran');
            });
            
            Route::group(['prefix' => 'tagihan'], function() {
                Route::get('/', [TagihanController::class, 'daftar'])->name('daftar_tagihan');
                Route::get('/detail/{id}/{tahun_pelajaran_id}', [TagihanController::class, 'detail'])->name('detail_tagihan');
                Route::get('tambah', [TagihanController::class, 'tambah'])->name('tambah_tagihan');
                Route::post('simpan', [TagihanController::class, 'simpan'])->name('simpan_tagihan');
                Route::get('/{id}/edit', [TagihanController::class, 'edit'])->name('edit_tagihan');
                Route::post('/{id}/update', [TagihanController::class, 'update'])->name('update_tagihan');
                Route::get('/{id}/hapus', [TagihanController::class, 'hapus'])->name('hapus_tagihan');
                Route::get('/tagihan-per-siswa/{id}', [TagihanController::class, 'lihatTagihanPerSiswa'])->name('lihat_tagihan_per_siswa');
                Route::get('/history-per-siswa/{id}', [TagihanController::class, 'lihatHistoryPerSiswa'])->name('lihat_history_per_siswa');
                Route::get('/{id}', [TagihanController::class, 'lihatTagihanPerId'])->name('lihat_tagihan_per_id');
                Route::get('/{id}/{tahun_pelajaran_id}/{madrasah_id}', [TagihanController::class, 'siswaTanpaTagihan'])->name('siswa_tanpa_tagihan');
                Route::post('/hapus-data', [TagihanController::class, 'hapusData'])->name('hapus_data_tagihan');
            });
            
            Route::group(['prefix' => 'jajan'], function() {
                Route::get('/', [AkunJajanController::class, 'daftar'])->name('daftar_akun');
                Route::get('tambah', [AkunJajanController::class, 'tambah'])->name('tambah_akun');
                Route::post('simpan', [AkunJajanController::class, 'simpan'])->name('simpan_akun');
                Route::get('/{id}/edit', [AkunJajanController::class, 'edit'])->name('edit_akun');
                Route::post('/{id}/update', [AkunJajanController::class, 'update'])->name('update_akun');
                Route::get('/{id}/hapus', [AkunJajanController::class, 'hapus'])->name('hapus_akun');
                Route::get('detail/{siswa_id}', [AkunJajanController::class, 'detailKhusus'])->name('detail');
            
                Route::get('/cari', [AkunJajanController::class, 'cari'])->name('cari_akun');
                Route::get('/cari-by-id/{siswa_id}', [AkunJajanController::class, 'cariById'])->name('cari_akun_by_id');
                Route::get('/jurnal/{id}', [JurnalJajanController::class, 'jurnalJajan'])->name('jurnal_jajan');
                Route::get('/pengambilan/khusus', [JurnalJajanController::class, 'pengambilanKhusus'])->name('pengambilan_saldo_khusus');
                Route::post('/pengambilan/khusus/simpan', [JurnalJajanController::class, 'simpanPengambilanKhusus'])->name('simpan_pengambilan_saldo_khusus');
                Route::get('/penambahan', [JurnalJajanController::class, 'penambahanSaldo'])->name('penambahan_saldo');
                Route::post('/penambahan/simpan', [JurnalJajanController::class, 'simpanPenambahan'])->name('simpan_penambahan');
                Route::post('/penambahan/set-sc', [JurnalJajanController::class, 'gantiStatus'])->name('set_sc');
                Route::get('/rekap', [JurnalJajanController::class, 'rekapJajan'])->name('rekap_jajan');
                Route::get('/cetak-panggilan', [JurnalJajanController::class, 'cetakPanggilan'])->name('cetak_panggilan');
                
            });
    
            Route::group(['prefix' => 'pembayaran'], function() {
                Route::get('/', [PembayaranController::class, 'tambah'])->name('tambah_pembayaran');
                Route::post('/proses-pembayaran', [PembayaranController::class, 'prosesPembayaran'])->name('proses_pembayaran');
                Route::post('simpan', [PembayaranController::class, 'simpan'])->name('simpan_pembayaran');
                Route::post('hapus', [PembayaranController::class, 'cancelPembayaran']);
                Route::get('/daftar', [PembayaranController::class, 'daftar'])->name('daftar_pembayaran');
                Route::get('/{id}/edit', [PembayaranController::class, 'edit'])->name('edit_pembayaran');
                Route::post('/{id}/update', [PembayaranController::class, 'update'])->name('update_pembayaran');
                Route::get('/{id}/hapus', [PembayaranController::class, 'hapus'])->name('hapus_pembayaran');
                Route::post('/detail', [PembayaranController::class, 'detail'])->name('detail_pembayaran');
                Route::get('/kwitansi/{id}', [PembayaranController::class, 'cetakKwitansi'])->name('cetak_kwitansi');
                Route::get('/kwitansi-kosong', [PembayaranController::class, 'cetakKwitansiKosong'])->name('kwitansi_kosong');
                Route::get('/rekap-penerimaan/{from}/{to}', [PembayaranController::class, 'rekapPenerimaan'])->name('rekap_penerimaan');
            });
            
            
        });
        
        Route::group(['prefix' => 'tu', 'middleware' => 'staff_access'], function(){
            Route::group(['prefix' => 'madrasah'], function() {
                Route::get('/', [MadrasahController::class, 'daftar'])->name('daftar_madrasah');
                Route::get('tambah', [MadrasahController::class, 'tambah'])->name('tambah_madrasah');
                Route::post('simpan', [MadrasahController::class, 'simpan'])->name('simpan_madrasah');
                Route::get('/{id}/edit', [MadrasahController::class, 'edit'])->name('edit_madrasah');
                Route::post('/{id}/update', [MadrasahController::class, 'update'])->name('update_madrasah');
                Route::get('/{id}/hapus', [MadrasahController::class, 'hapus'])->name('hapus_madrasah');
            });
            Route::group(['prefix' => 'data-rombel'], function() {
                Route::get('/', [DataRombelController::class, 'daftar'])->name('daftar_data_rombel');
                Route::get('tambah', [DataRombelController::class, 'tambah'])->name('tambah_data_rombel');
                Route::post('simpan', [DataRombelController::class, 'simpan'])->name('simpan_data_rombel');
                Route::get('/{id}/edit', [DataRombelController::class, 'edit'])->name('edit_data_rombel');
                Route::post('/{id}/update', [DataRombelController::class, 'update'])->name('update_data_rombel');
                Route::get('/{id}/{tahun_pelajaran_id}/hapus', [DataRombelController::class, 'hapus'])->name('hapus_data_rombel');
                Route::get('/atur-penempatan', [DataRombelController::class, 'aturPenempatan'])->name('atur_penempatan');
                Route::get('/tp/{tahun_pelajaran_id}/{madrasah_id}', [DataRombelController::class, 'dataRombelPerTp'])->name('list_rombel_per_tp');
            });
            Route::group(['prefix' => 'tahun-pelajaran'], function() {
                Route::get('/', [TahunPelajaranController::class, 'daftar'])->name('daftar_tahun_pelajaran');
                Route::get('tambah', [TahunPelajaranController::class, 'tambah'])->name('tambah_tahun_pelajaran');
                Route::post('simpan', [TahunPelajaranController::class, 'simpan'])->name('simpan_tahun_pelajaran');
                Route::get('/{id}/edit', [TahunPelajaranController::class, 'edit'])->name('edit_tahun_pelajaran');
                Route::post('/{id}/update', [TahunPelajaranController::class, 'update'])->name('update_tahun_pelajaran');
                Route::get('/{id}/hapus', [TahunPelajaranController::class, 'hapus'])->name('hapus_tahun_pelajaran');
                Route::get('/{madrasah_id}', [TahunPelajaranController::class, 'daftarPerMadrasah'])->name('daftar_tahun_pelajaran_per_madrasah');
                Route::get('/{id}/active', [TahunPelajaranController::class, 'setActive'])->name('set_active');
                Route::get('/{id}/ppdb-active', [TahunPelajaranController::class, 'setActivePPDB'])->name('set_active_ppdb');
            });
            Route::group(['prefix' => 'siswa'], function() {
                Route::get('/per-madrasah/{madrasah_id}', [SiswaController::class, 'daftar'])->name('daftar_siswa');
                
                Route::post('/terapkan-nis', [SiswaController::class, 'setNis'])->name('set_nis');
                Route::get('/pilih-madrasah', [SiswaController::class, 'pilihMadrasah'])->name('pilih_madrasah_siswa');
                Route::get('daftar/{id}', [SiswaController::class, 'daftarSiswaPerMadrasah'])->name('daftar_siswa_per_madrasah');
                Route::get('/list/{madrasah_id}', [SiswaController::class, 'list'])->name('list_siswa');
                Route::get('/list/{madrasah_id}/{tahun_pelajaran_id}/{rombel_id}/{hide_nonaktif}', [SiswaController::class, 'list'])->name('list_siswa_based_rombel');
                Route::get('tambah', [SiswaController::class, 'tambah'])->name('tambah_siswa');
                Route::get('/ppdb', [SiswaController::class, 'ppdb'])->name('ppdb');
                Route::get('/ppdb/tambah-calon-siswa', [SiswaController::class, 'tambahCalonSiswa'])->name('tambah_calon_siswa');
                Route::post('/ppdb/simpan', [SiswaController::class, 'simpanPPDB'])->name('simpan_siswa_baru');
                Route::post('terapkan-status', [SiswaController::class, 'terapkanStatus'])->name('terapkan_status');
                Route::post('simpan', [SiswaController::class, 'simpan'])->name('simpan_siswa');
                Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit_siswa');
                Route::get('/profil/{id}/edit', [SiswaController::class, 'editProfil'])->name('edit_profil');
                Route::post('/{id}/update', [SiswaController::class, 'update'])->name('update_siswa');
                Route::post('/profil/{id}/update', [SiswaController::class, 'updateProfil'])->name('update_profil_siswa');
                Route::get('/{id}/hapus', [SiswaController::class, 'hapus'])->name('hapus_siswa');
                Route::get('/list-per-rombel/{rombel_id}', [SiswaController::class, 'listPerRombel'])->name('list_siswa_per_rombel');
                Route::post('/upload-foto', [SiswaController::class, 'uploadFoto'])->name('upload_foto_siswa');
                Route::get('/{id}/profil', [SiswaController::class, 'profil'])->name('profil_siswa');
            });
            Route::group(['prefix' => 'rombel'], function() {
                Route::get('/', [RombelController::class, 'daftar'])->name('daftar_rombel');
                Route::get('/madrasah/{id}', [RombelController::class, 'daftarPerMadrasah'])->name('daftar_rombel_per_madrasah');
                Route::get('/{id}/detail', [RombelController::class, 'detail'])->name('detail_rombel');
                Route::get('/tambah', [RombelController::class, 'tambah'])->name('tambah_rombel');
                Route::get('/{id}/edit-data-siswa', [DataRombelController::class, 'tambah_siswa'])->name('tambah_siswa_ke_rombel');
                Route::post('/simpan-ke-rombel', [RombelController::class, 'simpan_siswa'])->name('simpan_siswa_ke_rombel');
                Route::post('/hapus-siswa', [RombelController::class, 'hapus_siswa'])->name('hapus_siswa_dari_rombel');
                Route::post('simpan', [RombelController::class, 'simpan'])->name('simpan_rombel');
                Route::get('/{id}/edit', [RombelController::class, 'edit'])->name('edit_rombel');
                Route::post('/{id}/update', [RombelController::class, 'update'])->name('update_rombel');
                Route::get('/{id}/hapus', [RombelController::class, 'hapus'])->name('hapus_rombel');
                
            });
        });
        
    });
    
    Route::group(['prefix' => 'dev', 'middleware' => 'developer_access'], function(){
        Route::group(['prefix' => 'bank'], function(){
            Route::get('/', [BankController::class, 'daftar'])->name('daftar_bank');
            Route::post('/simpan', [BankController::class, 'simpan'])->name('simpan_bank');
            Route::get('/{id}/edit', [BankController::class, 'edit'])->name('edit_bank');
            Route::post('/{id}/update', [BankController::class, 'update'])->name('update_bank');
            Route::get('/{id}/hapus', [BankController::class, 'hapus'])->name('hapus_bank');
        }); 
        
        Route::group(['prefix' => 'akun'], function(){
            Route::get('/', [AkunController::class, 'daftar'])->name('daftar_kode_akun');
            Route::post('/simpan', [AkunController::class, 'simpan'])->name('simpan_kode_akun');
            Route::get('/{id}/edit', [AkunController::class, 'edit'])->name('edit_kode_akun');
            Route::post('/{id}/update', [AkunController::class, 'update'])->name('update_kode_akun');
            Route::get('/{id}/hapus', [AkunController::class, 'hapus'])->name('hapus_kode_akun');
            Route::get('/kode-baru/{id}', [AkunController::class, 'get_kode'])->name('get_kode_baru');
        }); 
        
        Route::group(['prefix' => 'tahun-anggaran'], function(){
            Route::get('/', [TahunAnggaranController::class, 'daftar'])->name('daftar_tahun_anggaran');
            Route::post('/simpan', [TahunAnggaranController::class, 'simpan'])->name('simpan_tahun_anggaran');
            Route::get('/{id}/edit', [TahunAnggaranController::class, 'edit'])->name('edit_tahun_anggaran');
            Route::post('/{id}/update', [TahunAnggaranController::class, 'update'])->name('update_tahun_anggaran');
            Route::get('/{id}/hapus', [TahunAnggaranController::class, 'hapus'])->name('hapus_tahun_anggaran');
        }); 
        
        Route::group(['prefix' => 'kegiatan'], function(){
            Route::get('/', [KegiatanController::class, 'daftar'])->name('daftar_kegiatan');
            Route::post('/simpan', [KegiatanController::class, 'simpan'])->name('simpan_kegiatan');
            Route::get('/{id}/edit', [KegiatanController::class, 'edit'])->name('edit_kegiatan');
            Route::post('/{id}/update', [KegiatanController::class, 'update'])->name('update_kegiatan');
            Route::get('/{id}/hapus', [KegiatanController::class, 'hapus'])->name('hapus_kegiatan');
        }); 
        
        Route::group(['prefix' => 'jurnal-in-out'], function(){
            Route::get('/', [JurnalInOUtController::class, 'daftar'])->name('daftar_jurnal_in_out');
            Route::post('/simpan', [JurnalInOUtController::class, 'simpan'])->name('simpan_jurnal_in_out');
            Route::get('/{id}/edit', [JurnalInOUtController::class, 'edit'])->name('edit_jurnal_in_out');
            Route::post('/{id}/update', [JurnalInOUtController::class, 'update'])->name('update_jurnal_in_out');
            Route::get('/{id}/hapus', [JurnalInOUtController::class, 'hapus'])->name('hapus_jurnal_in_out');
        }); 
        
        Route::group(['prefix' => 'ppdb'], function(){
            Route::get('/', [JurnalInOUtController::class, 'daftar'])->name('daftar_jurnal_in_out');
            Route::post('/simpan', [JurnalInOUtController::class, 'simpan'])->name('simpan_jurnal_in_out');
            Route::get('/{id}/edit', [JurnalInOUtController::class, 'edit'])->name('edit_jurnal_in_out');
            Route::post('/{id}/update', [JurnalInOUtController::class, 'update'])->name('update_jurnal_in_out');
            Route::get('/{id}/hapus', [JurnalInOUtController::class, 'hapus'])->name('hapus_jurnal_in_out');
            Route::get('/neraca', [JurnalInOUtController::class, 'neraca'])->name('laporan_neraca');
            Route::get('/leger', [JurnalInOUtController::class, 'leger'])->name('leger');
            Route::post('/buku-besar', [JurnalInOUtController::class, 'jurnalBukuBesar'])->name('tampil_buku_besar');
        }); 
        
        Route::group(['prefix' => 'pengguna'], function(){
            Route::get('/', [UserController::class, 'daftar'])->name('daftar_pengguna'); 
            Route::get('tambah', [UserController::class, 'tambah'])->name('tambah_pengguna'); 
            Route::post('simpan', [UserController::class, 'simpan'])->name('simpan_pengguna'); 
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit_pengguna'); 
            Route::post('/{id}/update', [UserController::class, 'update'])->name('update_pengguna'); 
            Route::get('/{id}/hapus', [UserController::class, 'hapus'])->name('hapus_pengguna'); 
        });
    });

    Route::group(['prefix' => 'c', 'middleware' => 'client_access'], function(){
        Route::get('/', function(){
            return 'coming_soon';
        })->name('dashboard_client');

    });

    
});
*/