<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\movie;
use App\Models\schedule;
use Illuminate\Http\Request;
use App\Exports\ScheduleExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;



class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $cinemas = Cinema::all();
        $movies = movie::all();

        //menampilkan nama di blade, namun di db angka
        //with: mengambil detail data pada relasi, diambil dari nama fungsi relasi model
        $schedules = schedule::with('cinema', 'movie')->get();
        return view('staff.schedule.index', compact('cinemas', 'movies', 'schedules'));
    }

    public function datatables()
    {
        $schedules = schedule::with('cinema', 'movie')->get();
        return DataTables::of($schedules)->addIndexColumn()
        ->addColumn('cinema', function($schedule) {
            return $schedule->cinema->name;
        })
        ->addColumn('movie', function($schedule) {
            return $schedule->movie->title;
        })
        ->addColumn('price', function($schedule) {
            return 'Rp.' .  number_format($schedule->price, 0, ',', '.') ;
        })
        ->addColumn('hours', function($schedule) {
            $hours = $schedule->hours;
            $list = '<ul class = list-unstyled>';
            foreach ($hours as $hour) {
                $list .= '<li>' . $hour . '</li>';
            }
            $list .= '</ul>';
            return $list;
        })
        ->addColumn('btnActions', function($schedule) {
            $btnEdit = '<a href="'. route('staff.schedules.edit', $schedule['id']) .'" class="btn btn-secondary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('staff.schedules.delete', $schedule['id']) .'" method="POST"> '.
            @csrf_field() .
            @method_field('DELETE') . '
            <button class="btn btn-danger">Hapus</button>
        </form>';
        return '<div class="d-flex">' . $btnEdit . $btnDelete . '</div>';

        })
        ->rawColumns(['hours','btnActions'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cinema_id' => 'required',
            'movie_id' => 'required',
            'price' => 'required',
            //karna hours array, yang divalidasi isi arraynya (tanda .) dam divalidasi semua isi item array (tanda *)
            'hours.*' => 'required|date_format:H:i',
        ], [
            'cinema_id.required' => 'Lokasi Bioskop harus dipilih',
            'movie_id.required' => 'Film harus dipilih',
            'price.required' => 'Harga wajib diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'hours.*.required' => 'Jam tayang wajib diisi minimal 1 data',
            'hours.*.date_format' => 'Format jam tayang harus diisi dengan jam : menit',
        ]);

        //pengecekan apakah ada bioskop dan film yang dipilih sekarang ada di db, jika ada ambil jam tayang nya
        $hours = schedule::where('cinema_id', $request->cinema_id)->where('movie_id', $request->movie_id)->value('hours');
        // ambil data jam atau buat array kosong
        $hoursBefore = $hours ?? [];
        // gabungkan array sebelumnya dengan array baru
        $mergeHours = array_merge($hoursBefore, $request->hours);
        // jika ada duplikat ambil salah satu saja
        // gunakan data ini untuk disimpan di db
        $newHours = array_unique($mergeHours);

        // updateOrCreate: jika data ada maka update, jika tidak ada maka create
        $createData = schedule::updateOrCreate([
            // array pertama, acuan pencarian data
            'cinema_id' => $request->cinema_id,
            'movie_id' => $request->movie_id,
        ], [
            // array kedua, data yang akan diupdate
            'price' => $request->price,
            'hours' => $newHours,
        ]);

        if ($createData) {
            return redirect()->route('staff.schedules.index')->with('success', 'Berhasil menambahkan data!');
        } else {
            return redirect()->route('staff.schedules.index')->with('error', 'Gagal menambahkan data! Coba Lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(schedule $schedule, $id)
    {
        $schedule = Schedule::where('id', $id)->with(['cinema', 'movie'])->first();
        return view ('staff.schedule.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, schedule $schedule, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'hours.*' => 'required|date_format:H:i',
            ], [
            'price.required' => 'Harga harus diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'hours.*.required' => 'Jam tayang wajib diisi',
            'hours.*.date_format' => 'Format jam tayang harus diisi dengan jam : menit',
        ]);

        $updateData = schedule::where('id', $id)->update([
            'price' => $request->price,
            'hours' => $request->hours,
        ]);

        if($updateData) {
            return redirect()->route('staff.schedules.index')->with('success', 'Berhasil mengubah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(schedule $schedule, $id)
    {
        Schedule::where('id', $id)->delete();
        return redirect()->route('staff.schedules.index')->with('success', 'Berhasil menghapus data!');
    }

    public function trash() {
        $scheduleTrash = Schedule::with(['cinema', 'movie'])->onlyTrashed()->get();
        return view('staff.schedule.trash', compact('scheduleTrash'));
    }

    public function restore($id) {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->restore();
        return redirect()->route('staff.schedules.index')->with('success', 'Berhasil mengembalikan data!');
    }

    public function deletePermanent($id) {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->forceDelete();
        return redirect()->back()->with('success', 'Berhasil menghapus data secara permanen!');
    }

    public function exportExcel() {
        $fileName = 'data-jadwal.xlsx';
        return Excel::download(new ScheduleExport, $fileName);
    }

}
