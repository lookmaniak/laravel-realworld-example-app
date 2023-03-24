<?php

namespace App\Http\Controllers;

use App\Models\Madrasah;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class TahunPelajaranController extends Controller
{
    protected function index() {
        $data = \Auth::user()->tahun_pelajaran;

        return response()->json(['tahun_pelajarans' => $data], 200);
    }

    protected function create(Request $request) {
        $request->validate([
            'kode' => 'max:32 | required',
            'tahun_mulai' => 'string | size:4 | required',
        ]);

        $data = TahunPelajaran::create([
            'kode' => $request->kode,
            'tahun_mulai' => $request->tahun_mulai,
            'user_id' => \Auth::user()->id,
        ]);

        return response()->json(['tahun_pelajaran' => $data], 200);
    }

    protected function update(Request $request) {
        $request->validate([
            'kode' => 'max:32 | required',
            'tahun_mulai' => 'string | size:4 | required',
        ], $request->all());

        $data = \Auth::user()->tahun_pelajaran->where('id', $request->id)->first();
        $data->update([
            'kode' => $request->kode,
            'tahun_mulai' => $request->tahun_mulai,
        ]);

        return response()->json(['tahun_pelajaran' => $data], 200);
    }

    protected function delete(Request $request) {
        $data = \Auth::user()->tahun_pelajaran->where('id', $request->id)->first();
        
        if(empty($data)) return response()->json([ 'code' => 402, 'message' => 'Data not found!'], 402);
        
        $data->is_deleted = true;
        $data->deleted_by = \Auth::user()->id;
        $data->deleted_at = date("Y-m-d H:i:s");
        $data->save();
        
        return response()->json(['tahun_pelajaran' => $data], 200);
    }
    
    protected function setActive(Request $request) {
        $user = \Auth::user();
        $data = TahunPelajaran::where('user_id', $user->id)->update(['status' => 0]);
        
        if(empty($data)) return response()->json([ 'code' => 402, 'message' => 'Records not found!'], 402);
        
        $val = $user->tahun_pelajaran->where('id', $request->id)->first();

        $val->status = true;
        $val->save();
        
        return response()->json(['tahun_pelajaran' => $val], 200);
    }
    
    protected function setActivePPDB(Request $request) {
        $user = \Auth::user();
        $data = TahunPelajaran::where('user_id', $user->id)->update(['ppdb' => 0]);
        
        if(empty($data)) return response()->json([ 'code' => 402, 'message' => 'Records not found!'], 402);
        
        $val = $user->tahun_pelajaran->where('id', $request->id)->first();

        $val->ppdb = true;
        $val->save();
        
        return response()->json(['tahun_pelajaran' => $val], 200);
    }
}
