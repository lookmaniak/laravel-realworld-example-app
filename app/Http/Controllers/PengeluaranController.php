<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Bank;

class PengeluaranController extends Controller
{
    protected function daftarPengeluaran() {
        
        $pengeluaran = Pengeluaran::paginate(20);
        $banks = Bank::all();
        $judul = 'JURNAL PENGELUARAN';
        
        return view('data.pengeluaran.daftar_pengeluaran', compact('pengeluaran', 'judul', 'banks'));
        
    }
}
