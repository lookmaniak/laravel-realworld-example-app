<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    protected function tambah() {
        $judul = 'TAMBAH CATATAN';
        $fa = 'sticky-note';

        return view('data.note.form', compact('judul', 'fa'));
    }

    protected function simpan(Request $request) {
        $request->validate([
            'judul' => 'max:225',
            'target' => 'integer',
            'pesan' => 'max:1000',
        ]);

        Note::create([
            'judul' =>$request->judul,
            'pesan' => $request->pesan,
            'user_id'=> Auth::user()->id,
            'target' => $request->target,
            ]);

        return redirect()->back()->with('success', 'Catatan sudah disimpan!');
    }

    protected function daftar() {
        $data = Note::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
        $fa = 'sticky-note';
        
        $judul = 'DAFTAR CATATAN';
        return view('data.note.daftar', compact('data', 'judul', 'fa'));
    }

    protected function edit(Request $request)     {
        $data = Note::find($request->id);
        $judul = 'EDIT CATATAN';

        return view('data.note.form_edit', compact('data', 'judul'));
    }

    protected function update(Request $request) {
        $request->validate([
           'judul' => 'max:225',
           'pesan' => 'max:1000',
           'target' => 'integer',
        ], $request->all());

        $data = Note::find($request->id);
        $data->update([
            'judul' =>$request->judul,
            'pesan' => $request->pesan,
            'status' => 0,
            'target' => $request->target,
        ]);

        return redirect()->back()->with('success', 'Catatan berhasil diperbarui!');
    }

    protected function hapus(Request $request) {
        $data = Note::find($request->id);
        $data->delete();
        return redirect(route('daftar_note'))->with('success', 'Catatan berhasil dihapus!');
    }
    
    protected function close(Request $request) {
        $data = Note::find($request->id);
        $data->status = 1;
        $data->closed_by = Auth::user()->id;
        $data->save();
        return redirect()->back()->with('success', 'Catatan berhasil diclose!');
    }

    protected function pinpost(Request $request) {
        $data = Note::find($request->id);
        $data->pin = 1;
        $data->save();
        return redirect()->back()->with('success', 'Catatan berhasil dipin!');
    }

    protected function unpinpost(Request $request) {
        $data = Note::find($request->id);
        $data->pin = 0;
        $data->save();
        return redirect()->back()->with('success', 'Catatan berhasil diunpin!');
    }
}
