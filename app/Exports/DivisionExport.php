<?php

namespace App\Exports;

use App\Models\Division;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class DivisionExport implements FromCollection
{
    /**
    * @return Collection
    */
    public function collection()
    {
        return Division::select('name')->get();
    }
}
