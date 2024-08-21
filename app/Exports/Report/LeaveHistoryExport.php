<?php

namespace App\Exports\Report;

use App\Models\LeaveRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LeaveHistoryExport implements FromCollection, WithHeadings, WithColumnFormatting , WithEvents
{
    protected $employeeLeaveHistoryDeptWise = [];
    protected $departmentInformations = [];
    protected $monthAndYear = [];
    protected $rangeNumber = [];

    /**
     * @var array
     */
    protected $report = [];

    /**
     * Create a new controller instance
     *
     * SalarySheetExport constructor.
     * @param array $employeeLeaveHistoryDeptWise
     * @param array $departmentInformations
     * @param $monthAndYear
     */
    public function __construct($employeeLeaveHistoryDeptWise, $departmentInformations, $monthAndYear)
    {
        $this->employeeLeaveHistoryDeptWise = $employeeLeaveHistoryDeptWise;
        $this->departmentInformations = $departmentInformations;
        $this->monthAndYear = $monthAndYear;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ["Employee Name", "Leave Type", "Request Duration", "Applied Date","No. of day","Authorized By","Approved By","Reasons","Status"];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $cellNumber = 1;
        $tempDeptSum = 0;
        foreach ($this->employeeLeaveHistoryDeptWise as $deptID => $items) {
            $divisionDeptName = [
                $this->departmentInformations[$deptID]['division_name'].', '.$this->departmentInformations[$deptID]['department_name']
            ];

            if($cellNumber === 1){
                $this->rangeNumber[2] = count($items);
                $cellNumber = 2;
                $tempDeptSum = $tempDeptSum + count($items)+3;
                $this->rangeNumber[$tempDeptSum] = count($items);
            }else{
                $tempDeptSum = $tempDeptSum + count($items)+1;
                $this->rangeNumber[$tempDeptSum] = count($items);
            }
            //echo $deptID.'-'.$tempDeptSum.'-'.$cellNumber.'-'.count($items)."<br>";
            array_push($this->report, $divisionDeptName);
            foreach($items as $item){
                $row = [
                    $item['employee_name'] .' - '.$item['fingerprint_no'],
                    $item['leave_type'],
                    $item['request_duration'],
                    $item['applied_date'],
                    $item['number_of_days'],
                    $item['authorized_by'],
                    $item['approved_by'],
                    $item['purpose'],
                    $this->getStatus($item['status'])
                ];
                $cellNumber++;
                array_push($this->report, $row);
            }
        }
        return collect($this->report);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(45);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(90);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(10);

                $event->sheet->getDelegate()->getStyle('A1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('B1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('C1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('D1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('E1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('F1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('G1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('H1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('I1')->getFont()->setBold(true);
                //dd($this->rangeNumber);
                foreach ($this->rangeNumber as $keyRange=>$valueRange){
                    $event->sheet->mergeCells('A'.$keyRange.':I'.$keyRange)
                        ->getStyle('A'.$keyRange.':I'.$keyRange)
                        ->getAlignment()
                        ->setHorizontal('center');
                    $this->createStyleAfterMergeCell($event, 'A'.$keyRange.':I'.$keyRange, 12);
                }
            },

        ];


    }

    /**
     * @param $status
     * @return mixed
     */
    protected function getStatus($status)
    {
        $statuses = [
            LeaveRequest::STATUS_APPROVED         => "Approved",
            LeaveRequest::STATUS_REJECTED => "Cancelled"
        ];
        return $statuses[$status];
    }

    /**
     * @param $event
     * @param $cell
     * @param $size
     */
    private function createStyleAfterMergeCell($event, $cell, $size) {
        /** @var AfterSheet $event */
        $event->sheet->getDelegate()
            ->getStyle($cell)
            ->getFont()
            ->setBold(true)
            ->setSize($size)
            ->getColor()
            ->setARGB('538DD5');
    }
}
