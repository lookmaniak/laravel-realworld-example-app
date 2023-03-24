<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use App\Models\JurnalPembayaran;
use App\Models\JurnalMasuk;
use App\Models\Tagihan;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function jsonViewer(Request $request) {
        $data = json_decode(file_get_contents($request->file('json_data')));
        $body = $data->results;
        $header = $data->results[0] ?? [];
        
        return view('data.rdm.jsonviewer', compact('header', 'body'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $judul = 'DASHBOARD';
        $fa = 'home';
        $today = Carbon::today('Asia/Jakarta');
        $penerimaan_pembayaran = JurnalPembayaran::where('jenis_pembayaran','=', null)->whereDate('created_at', $today)->sum('nilai_pembayaran');
        $penerimaan_tabungan = JurnalMasuk::where('jenis','MASUK')->whereDate('created_at', $today)->sum('nilai');
        $penerimaan_lainnya = JurnalPembayaran::where('jenis_pembayaran','lain-lain')->whereDate('created_at', $today)->sum('nilai_pembayaran');
        $total_tunggakan = Tagihan::where('status', 0)->sum('sisa');
        $notes = Note::where('target', Auth::user()->user_group)->orWhere('target', 99)->orderBy('status', 'ASC')->orderBy('updated_at', 'DESC')->paginate(8);

        
        return view('home', compact('fa', 'judul', 'today','notes', 'penerimaan_pembayaran', 'penerimaan_tabungan', 'penerimaan_lainnya', 'total_tunggakan'));
    }
    
    public function ringkasanPenerimaan() {
        if(Auth::user()->level == 0 || Auth::user()->level == 4) {
             $data = JurnalPembayaran::groupBy('labels')
            ->selectRaw('DATE_FORMAT(created_at,"%d-%m") as labels, SUM(nilai_pembayaran) as vals')
            ->whereRaw('created_at >= DATE(NOW()) - INTERVAL 30 DAY')
            ->where('jenis_pembayaran','=', null)
            ->orderBy('created_at')->get();
            
            return response()->json(['status' => true, 'data' => $data], 200);
        }
       
        return response()->json(['status' => false, 'data' => $data], 200);
    }

    public function redirector() {
        switch (Auth::user()->level) {
            case 0:
                return redirect(route('dashboard_admin'));
                break;
            case 1:
                return redirect(route('dashboard_teller'));
                break;
            case 2:
                return redirect(route('dashboard_client'));
                break;
            case 3:
                return redirect(route('dashboard_admin'));
                break;
            case 4:
                return redirect(route('dashboard_admin'));
                break;
            default:
                return redirect(route('dashboard_client'));
                break;
            
        }
    }

}
