<?php

namespace App\Exports\Report;

use App\Models\Requisition;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RequisitionExport implements FromCollection, WithHeadings
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
        foreach ($this->data as $item) {
            $row = [
                $item->id,
                $item->appliedBy->fingerprint_no . ' - ' . $item->appliedBy->name,
                $item->department->name,
                $item->applied_date,
                $this->getPriority($item->priority),
                $this->getStatus($item->status),
                optional($item->approvedBy)->fingerprint_no . ' - ' . optional($item->approvedBy)->name,
                $item->remarks,
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
        return [
            "Order No.",
            "Applied by",
            "Department",
            "Apply Date",
            "Priority",
            "Status",
            "Approved by",
            "Remarks",
        ];
    }

    /**
     * @param $priority
     * @return mixed
     */
    protected function getPriority($priority)
    {
        $priorities = [
            Requisition::PRIORITY_TODAY         => "Today",
            Requisition::PRIORITY_WITHIN_3_DAYS => "Within 3 days",
            Requisition::PRIORITY_WITHIN_7_DAYS => "Within 7 days",
            Requisition::PRIORITY_WITHIN_10_DAYS=> "Within 10 days"
        ];

        return $priorities[$priority];
    }

    /**
     * @param $status
     * @return mixed
     */
    protected function getStatus($status)
    {
        $statuses = [
            Requisition::STATUS_NEW         => "New",
            Requisition::STATUS_IN_PROGRESS => "In progress",
            Requisition::STATUS_DELIVERED   => "Delivered",
            Requisition::STATUS_REJECTED    => "Rejected",
            Requisition::STATUS_RECEIVED    => "Received",
        ];

        return $statuses[$status];
    }
}
