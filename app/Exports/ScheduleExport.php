<?php

namespace App\Exports;

use App\Models\schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScheduleExport implements FromCollection, WithHeadings, WithMapping
{

    private $key = 0;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return schedule::all();
    }

    public function headings(): array
    {
        return ['No', 'Bioskop', 'Film', 'Harga', 'Jam Tayang'];
    }

    public function map($schedule): array
    {
        return [
            ++$this->key,
            $schedule->cinema->name,
            $schedule->movie->title,
            $schedule->price,
           $schedule->hours,
        ];
    }
}
