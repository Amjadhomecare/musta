<?php

namespace App\Exports;

use App\Models\MaidsDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MaidsNoFilter implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return MaidsDB::doesntHave('maidsFilter')
                     ->select('name', 'maid_type', 'nationality', 'maid_status')
                     ->where('maid_status', 'approved')
                     ->where('maid_type', 'HC')
                     ->whereNull('maid_booked')
                     ->get(); 
    }


    public function headings(): array
    {
        return ['Name', 'Maid Type', 'Nationality', 'Maid Status'];
    }
}
