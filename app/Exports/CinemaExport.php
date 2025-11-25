<?php

namespace App\Exports;

use App\Models\Cinema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CinemaExport implements FromCollection, WithHeadings, WithMapping
{
    private $key = 0;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Cinema::all();
    }

    //Menentukan header
    public function headings(): array
    {
        return ['No', 'Nama Bioskop', 'Lokasi'];
    }

    //Menentukan isi data
    public function map($cinemas): array
    {
        return [
            ++$this->key,
            $cinemas->name,
            $cinemas->location
        ];
    }
}
