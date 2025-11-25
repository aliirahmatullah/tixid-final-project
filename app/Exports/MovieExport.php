<?php

namespace App\Exports;

use App\Models\movie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
//class laravel untuk memanipulasi datetime
use Carbon\Carbon;

class MovieExport implements FromCollection, WithHeadings, WithMapping
{
    //Membuat properti untuk no urutan data
    private $key = 0;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //Memanggil data yang akan dimunculkan di excel
        return movie::all();
    }

    //Menentukan header data (th)
    public function headings(): array
    {
        return ['No', 'Judul Film', 'Durasi', 'Genre', 'Sutradara', 'Usia Minimal', 'Poster', 'Sinopsis'];
    }

    //Menentukan isi data (td)
    public function map($movie): array
    {
        return [
            //Menambahkan sebanyak 1 setiap data dari $key = 0 diatas
            ++$this->key,
            $movie->title,
            //format : 01 jam 30 menit, data asal : 01.00.00
            //parse() : mengambil data tanggal/jam yang akan di manipulasi
            Carbon::parse($movie->duration)->format("H") . " Jam " . Carbon::parse($movie->duration)->format("i") . " Menit",
            $movie->genre,
            $movie->director,
            //format : usia + : 17+
            $movie->age_rating . "+",
            // asset() : link buat liat gambar
            asset('storage/' . $movie->poster),
            $movie->description

        ];
    }
}
