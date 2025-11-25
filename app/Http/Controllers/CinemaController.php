<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use Yajra\DataTables\Facades\DataTables;


class CinemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cinemas = Cinema::all();
        //Cinema::all : mengambil semua data pada model cinema (tabel cinema)
        //Mengirim data dari controller ke blade menggunakan : compact()
        //Isis compact sama dengan nama variabel
        return view('admin.cinema.index', compact('cinemas'));
    }

    public function datatables()
    {
        $cinemas = Cinema::query();
        return DataTables::of($cinemas)->addIndexColumn()
        ->addColumn('btnActions', function($cinema) {
            $btnEdit = '<a href="'. route('admin.cinemas.edit', $cinema['id']) .'" class="btn btn-secondary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('admin.cinemas.delete', $cinema['id']) .'" method="POST"> '.
            @csrf_field() .
            @method_field('DELETE') . '
            <button class="btn btn-danger">Hapus</button>
        </form>';
        return '<div class="d-flex">' . $btnEdit . $btnDelete . '</div>';

        })
        ->rawColumns(['btnActions'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cinema.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'Nama Bioskop harus diisi',
            'location.required' => 'Lokasi Bioskop harus diisi',
            'location.min' => 'Lokasi Bioskop diisi minimal 10 karakter',
        ]);

        $createData = Cinema::create([
            'name' => $request->name,
            'location' => $request->location,
        ]);
        if($createData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil tambah data bioskop!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cinema $cinema)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //edit($id) : id diambil dari route {id}
        $cinema = Cinema::find($id);
        return view('admin.cinema.edit', compact('cinema'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'Nama Bioskop harus diisi',
            'location.required' => 'Lokasi Bioskop harus diisi',
            'location.min' => 'Lokasi Bioskop diisi minimal 10 karakter',
        ]);

        $updateData = Cinema::where('id', $id)->update([
            'name' => $request->name,
            'location' => $request->location,
        ]);

        if($updateData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil mengubah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // count : menghitung jumlah data yang terkait
        $schedules = Schedule::where('cinema_id', $id)->count();

        if ($schedules > 0) {
            return redirect()->route('admin.cinemas.index')->with('error', 'Tidak dapat menghapus data bioskop! Data tertaut dengan jadwal tayang');
        }

        Cinema::where('id', $id)->delete();
        return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil menghapus data!');
    }

    public function trash()
    {
        $cinemas = Cinema::onlyTrashed()->get();
        return view('admin.cinema.trash', compact('cinemas'));
    }

    public function restore($id)
    {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema->restore();
        return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil mengembalikan data!');
    }

    public function deletePermanent($id)
    {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema->forceDelete();
        return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil menghapus data secara permanen!');
    }



    public function exportExcel(
        //nama file yang akan diunduh
        $namaFile = 'data-bioskop.xlsx'
    )
    {
        //proses download
        return Excel::download(new CinemaExport, $namaFile);
    }

    public function cinemaList()
    {
        $cinemas = Cinema::all();
        return view('schedule.cinemas', compact('cinemas'));
    }

    public function cinemaSchedules($cinema_id)
    {
        // whereHas('namarelasi', function($q) {...}) : argumen 1 (nama relasi) wajib, argumen 2 (func untuk filter pada relasi) optional
        // whereHas('namarelasi')->Movie::whereHas('schedules') mengambil data film hanya yang memiliki relasi (memiliki data) schedules
        // whereHas('namarelasi', function($q) {...}) -> schedule::whereHas('movie' function($q) {$q->where('actived', 1)}) mengambil data schedule hanya yang memiliki relasi (memiliki data) movie dan nilai actived = 1,
        $schedules = Schedule::where('cinema_id', $cinema_id)->with('movie')->whereHas('movie', function($q) {
            $q->where('actived', 1);
        })->get();
        return view('schedule.cinema-schedules', compact('schedules'));
    }
}
