<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RequisitionReportExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $report = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        foreach ($this->data as $data) {
            $row = [
                $data->item->name,
                $data->total_quantity,
            ];

            array_push($this->report, $row);
        }

        return collect($this->report);
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function headings(): array
    {
        return ["Item Name", "Total Quantity"];
    }
}
