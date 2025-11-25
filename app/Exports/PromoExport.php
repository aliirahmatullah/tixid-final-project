<?php

namespace App\Exports;

use App\Models\promo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
//class laravel untuk memanipulasi datetime
use Carbon\Carbon;

class PromoExport implements FromCollection, WithHeadings, WithMapping
{

    private $key = 0;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return promo::all();
    }

    //Membuat Headings
    public function headings(): array
    {
        return ['No', 'Kode Promo', 'Total Potongan'];
    }

    //Membuat Isi
    public function map($promo): array
    {

        if ($promo->type == 'percent') {
            $promo->discount = $promo->discount . '%';
        } elseif ($promo->type == 'rupiah') {
            $promo->discount = 'Rp. ' . number_format($promo->discount, 0, ',', '.');
        }
        return [
            ++$this->key,
            $promo->promo_code,
            $promo->discount,
        ];
    }
}
