<?php

namespace App\Http\Controllers;

use App\Models\ItemPembayaran;
use App\Models\Madrasah;
use Illuminate\Http\Request;


class ItemPembayaranController extends Controller
{

    protected function index(Request $request){
        $madrasahs = \Auth::user()->madrasah;
        $madrasah = $madrasahs->where('id', $request->madrasah_id)->first();
        
        if(!$madrasah) return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        
        $data = $madrasah->item_pembayaran->where('is_deleted', false);
        return response()->json(['item_pembayarans' => $data], 200);
    }

    protected function create(Request $request) {
        $request->validate([
            'madrasah_id' => 'integer | required',
            'keterangan' => 'max:225 | required',
        ]);

        $madrasahs = \Auth::user()->madrasah;
        $madrasah = $madrasahs->where('id', $request->madrasah_id)->first();
        
        if(!$madrasah) return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        
        $data = ItemPembayaran::create($request->all());

        return response()->json(['item_pembayaran' => $data], 200);
    }

    protected function delete(Request $request) {
        $madrasahs = \Auth::user()->madrasah;
        $item_pembayaran = ItemPembayaran::where('id', $request->id)->first();
        $madrasah = $madrasahs->where('id', $item_pembayaran->madrasah_id)->first();
        
        if(!$madrasah) return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        
        $item_pembayaran->is_deleted = true;
        $item_pembayaran->deleted_by = \Auth::user()->id;
        $item_pembayaran->deleted_at = date("Y-m-d H:i:s");
        $item_pembayaran->save();
            

        return response()->json(['item_pembayaran' => $item_pembayaran], 200);
    }

    protected function update(Request $request) {
        $request->validate([
            'madrasah_id' => 'integer | required',
            'keterangan' => 'max:225 | required',
        ], $request->all());

        $madrasahs = \Auth::user()->madrasah;
        $item_pembayarans = $madrasahs->where('id', $request->madrasah_id)->first()->item_pembayaran;
        $item_pembayaran = $item_pembayarans->where('id', $request->id)->first();
        
        
        if(!$item_pembayaran) return response()->json(['code' => 402, 'errors' => (object)[], 'message' => 'Data not found!'], 200);
        
        $item_pembayaran->update([
            'madrasah_id' => $request->madrasah_id,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['item_pembayaran' => $item_pembayaran], 200);
    }

    protected function daftar(Request $request) {
        $data = ItemPembayaran::all();
        $data_madrasah = Madrasah::all();
        
        $judul = 'DAFTAR ITEM PEMBAYARAN';
        return view('data.item_pembayaran.daftar', compact('data', 'judul', 'data_madrasah'));
    }

    protected function edit(Request $request)     {
        $data = ItemPembayaran::find($request->id);
        $judul = 'EDIT ITEM PEMBAYARAN';
        $data_madrasah = Madrasah::all();

        return view('data.item_pembayaran.form_edit', compact('data', 'judul', 'data_madrasah'));
    }

    protected function hapus(Request $request) {
        $data = ItemPembayaran::find($request->id);
        $data->delete();
        return redirect(route('daftar_item_pembayaran'))->with('success', 'Data Item Pembayaran berhasil dihapus!');
    }

    protected function daftarPerMadrasah(Request $request){
        $data = ItemPembayaran::where('madrasah_id', $request->madrasah_id)->get();
        return response()->json($data);
    }
    
    protected function detailItemPembayaran(Request $request) {
        $data = ItemPembayaran::where('id', $request->id)->first();
        return response()->json($data);
    }
}
