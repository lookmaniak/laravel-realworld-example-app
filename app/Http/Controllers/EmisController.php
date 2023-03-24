<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmisController extends Controller
{
    protected function dataSiswa() {
        return view('data.emis.data_siswa');
    }
}
