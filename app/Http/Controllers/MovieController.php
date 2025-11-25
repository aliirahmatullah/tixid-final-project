<?php

namespace App\Http\Controllers;

use App\Models\movie;
use App\Models\schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovieExport;
use Yajra\DataTables\Facades\DataTables;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movies = Movie::all();
        return view('admin.movie.index', compact('movies'));
    }

    public function datatables()
    {
        // jika data yang diambil tidak ada relasi pake query() jika ada relasi pake with()
        $movies = Movie::query();
        return DataTables::of($movies)->addIndexColumn()
        ->addColumn('imgPoster', function($movie){
            $imgUrl = asset('storage/' . $movie['poster']);
            return '<img src="'. $imgUrl .'" width="120px">';
        })
        ->addColumn('activeBadge', function($movie) {
            if ($movie->actived == 1) {
                return '<span class="badge bg-success">Aktif</span>';
            } else {
                return '<span class="badge bg-danger">Non-Aktif</span>';
            }
        })
        ->addColumn('btnActions', function($movie) {
            $btnDetail ='<button class="btn btn-primary me-2 " onclick=\'showmodal('. json_encode($movie) .')\'>Detail</button>';
            $btnEdit = '<a href="'. route('admin.movies.edit', $movie['id']) .'" class="btn btn-secondary me-2">Edit</a>';
            $btnDelete ='<form action="'. route('admin.movies.delete', $movie['id']) .'" method="POST">'.
                            csrf_field() .
                            method_field("DELETE") . '
                            <button type="submit" class="btn btn-danger me-2">Hapus</button>
                        </form>';

            if ($movie['actived'] == 1) {
                $btnNonAktif = '<form action="'. route('admin.movies.deactive', $movie['id']) .'" method="POST">'.
                            csrf_field() .
                            method_field("PATCH") . '
                            <button type="submit" class="btn btn-danger me-2">Non-Aktif</button>
                               </form>';
            } else {
                $btnNonAktif = '';
            }

            return '<div class="d-flex">' . $btnDetail . $btnEdit . $btnDelete . $btnNonAktif . '</div>';
        })
        //daftarkan nama dari addColumn untuk dipanggil di js datatablesnya
        ->rawColumns(['imgPoster', 'activeBadge', 'btnActions'])
        //ubah query jadi json agar bisa dibaca JS datatablesnya
        ->make(true);
    }


    public function home() {

        // where() -> untuk mencari data. format yang digunakan where('field', 'operator', 'value')
        // get -> mengambil semua data hasil filter
        // first() -> mengambil data pertama hasil filter
        // paginate() -> membagi data menjadi beberapa halaman
        // orderBy() -> untuk mengurutkan data. format orderBy('field', 'type')
        // type ASC -> mengurutkan dari A-Z atau 0-9 atau terlama ke terbaru
        // type DESC -> mengurutkan dari Z-A atau 9-0 atau terbaru ke terlama
        // limit() -> mengambil data dengan jumlah tertentu format limit(angka)

        $movies = Movie::where('actived', 1)->orderBy('created_at', 'desc')->limit(5)->get();
        return view('home', compact('movies'));
    }

    public function homeMovies(Request $request) {
        //pengambilan data dari input form search

        $nameMovie = $request->search_movie;

        if ($nameMovie != "") {
            //LIKE : mencari data yang mirip/ mengandung teks yang dicari
            // % depan : mencari kata belakang, % belakang : mencari kata depan, % depan dan belakang : mencari kata di tengah
            $movies = Movie::where('title', 'LIKE', '%' . $nameMovie . '%')->where('actived', 1)->orderBy('created_at', 'desc')->get();
        } else {
            $movies = Movie::where('actived', 1)->orderBy('created_at', 'desc')->get();
        }
        return view ('movies', compact('movies'));
    }

    public function movieSchedule($movie_id, Request $request) {
        //mengambil ? bisa dnegan Request $request
        $sortirHarga = $request->sortirHarga;
        if ($sortirHarga) {
            //with(['namarelasi] => function($q) {...}]) melakukan filter di relasi
            $movie = Movie::where('id', $movie_id)->with(['schedules' => function($q) use ($sortirHarga) {
                $q->orderBy('price', $sortirHarga);
            }, 'schedules.cinema'])->first();
        } else {
            $movie = Movie::where('id', $movie_id)->with(['schedules', 'schedules.cinema'])->first();
        }

        $sortirAlfabet = $request->sortirAlfabet;
        if ($sortirAlfabet) {
            //karna alfabet dari name di cinemaa, cinema di 'schedule.cinema' (cinema relasi ke dua) jadi gunakan collection untuk urutkannya
            //$movie->schedules : mengambil dari movie diatas bagian data schedulesnya
            $movie->schedules = $movie->schedules->sortBy(function ($schedule) {
                //sortBy L mengurutkan collection (hasil pengambilan data) secara ASC
                //diurutkan berdasarkan data di return (data name dari cinema, cinema dari relasi schedule)
                return $schedule->cinema->name;
            })->values(); // ambil ulang data hasil sortir
        } elseif ($sortirAlfabet == 'DESC') {
            //sortByDesc : mengurutkan collectin hasil pengambilan data secara DESC
            $movie->schedules = $movie->schedules->sortByDesc(function ($schedule) {
                return $schedule->cinema->name;
            })->values();
        }
        return view('schedule.detail-film', compact('movie'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.movie.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required',
            //mimes : memastikan ekstensi file (bentuk file yang boleh diupload)
            'poster' => 'required|mimes:jpg, jpeg, png, svg, webp',
            'description' => 'required|min:10'
        ], [
            'title.required' => 'Judul film harus diisi',
            'duration.required' => 'Durasi film harus diisi',
            'genre.required' => 'Genre film harus diisi',
            'director.required' => 'Sutradara harus diisi',
            'age_rating.required' => 'Usia minimal harus diisi',
            'poster.required' => 'Poster harus diisi',
            'poster.mimes' => 'Poster harus berbentuk JPG/JPEG/PNG/SVG/WEBP',
            'description.required' => 'Sinopsis harus diisi',
            'description.min' => 'Sinopsis diisi minimal 10 karakter',
        ]);

        //ambil file dari input
        $poster = $request->file('poster');
        //buat nama file yg akan disimpan di folder public/storage
        //nama dibuat baru dan unik untuk menghindari duplikat file : <acak> - poster.jpg contoh nama barunya
        //getClientoriginalExtension() : mengambil ekstensi file yang diupload
        $namaFile = uniqid() . "-poster." . $poster->getClientOriginalExtension();

        //Simpan file ke folder public/store : storeAS("namafolder", namafile, "visibility")
        //Visibility : public, private (disesuaikan file boleh ditampilkan atau tidak)
        $path = $poster->storeAs("poster", $namaFile, "public");
        $createData = Movie::create([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            'poster' => $path, // yg disimpan di db bukan file nya, hanya lokasi file dari storeAs()->$path
            'description' => $request->description,
            'actived' => 1

        ]);

        if ($createData) {
            return redirect()->route('admin.movies.index')->with('success', 'Berhasil membuat data');
        } else {
            return redirect()->back()->with('error', 'Gagal! Menambahkan data');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movie = Movie::find($id);
        return view('admin.movie.edit', compact('movie'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required',
            //mimes : memastikan ekstensi file (bentuk file yang boleh diupload)
            'poster' => 'sometimes|mimes:jpg, jpeg, png, svg, webp',
            'description' => 'required|min:10'
        ], [
            'title.required' => 'Judul film harus diisi',
            'duration.required' => 'Durasi film harus diisi',
            'genre.required' => 'Genre film harus diisi',
            'director.required' => 'Sutradara harus diisi',
            'age_rating.required' => 'Usia minimal harus diisi',
            'poster.mimes' => 'Poster harus berbentuk JPG/JPEG/PNG/SVG/WEBP',
            'description.required' => 'Sinopsis harus diisi',
            'description.min' => 'Sinopsis diisi minimal 10 karakter',
        ]);
        //ambil data sebelumnya
        $movie = Movie::find($id);
        //Cek jika ada poster baru
        if ($request->file('poster')) {
            //ambil lokasi poster lama
            $posterSebelumnya = storage_path('app/public/' . $movie['poster']);
            //cek jika file ada di oflder storage : file_exists()
            if (file_exists($posterSebelumnya)) {
                //hapus file lama : unlink()
                unlink($posterSebelumnya);
            }

        //ambil file dari input
        $poster = $request->file('poster');
        //buat nama file yg akan disimpan di folder public/storage
        //nama dibuat baru dan unik untuk menghindari duplikat file : <acak> - poster.jpg contoh nama barunya
        //getClientoriginalExtension() : mengambil ekstensi file yang diupload
        $namaFile = uniqid() . "-poster." . $poster->getClientOriginalExtension();

        //Simpan file ke folder public/store : storeAS("namafolder", namafile, "visibility")
        //Visibility : public, private (disesuaikan file boleh ditampilkan atau tidak)
        $path = $poster->storeAs("poster", $namaFile, "public");


        }

        $createData = Movie::where('id', $id)->update([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            'poster' => $path ?? $movie['poster'], // ?? ternary : (if, jika ada ambil) ?? else, gunakan yang sebelumnya
            'description' => $request->description,
            'actived' => 1

        ]);

        if ($createData) {
            return redirect()->route('admin.movies.index')->with('success', 'Berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'Gagal! Mengubah data');
        }
    }

    //Status aktif dan tidak aktif
    public function deactive($id) {
        $movie = Movie::find($id);
        $movie->actived = 0;
        $movie->save();
        return redirect()->route('admin.movies.index')->with('success', 'Berhasil mengubah status');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $schedules = Schedule::where('movie_id', $id)->count();
        if ($schedules > 0) {
            return redirect()->route('admin.movies.index')->with('error', 'Tidak dapat menghapus data film! Data tertaut dengan jadwal tayang.');
        }
        $movie = Movie::find($id);
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Berhasil menghapus data.');
    }

    public function trash()
    {
        $movies = Movie::onlyTrashed()->get();
        return view('admin.movie.trash', compact('movies'));
    }

    public function restore($id)
    {
        $movie = Movie::onlyTrashed()->find($id);
        $movie->restore();

        return redirect()->route('admin.movies.index')->with('success', 'Berhasil mengembalikan data.');
    }

    public function deletePermanent($id)
    {
        $movie = Movie::onlyTrashed()->find($id);

        if ($movie->poster) {
            $posterSebelumnya = storage_path('app/public/' . $movie->poster);
            if (file_exists($posterSebelumnya)) {
                unlink($posterSebelumnya);
            }
        }
        $movie->forceDelete();
        return redirect()->route('admin.movies.index')->with('success', 'Data berhasil dihapus secara permanen.');
    }




    public function exportExcel()
    {
        //nama file yang akan diunduh
        $fileName = 'data-film.xlsx';
        //proses download
        return Excel::download(new MovieExport, $fileName);
    }

    // chart
    public function chart()
    {
        $movieActive = Movie::where('actived', 1)->count();
        $movieNonActive = Movie::where('actived', 0)->count();

        // yg diperlukan jumlah data, gunakan count untuk menghitungnya
        $data = [$movieActive, $movieNonActive];
        return response()->json([
            'data' => $data
        ]);
    }
}
