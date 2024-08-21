<?php

namespace App\Exports\Report;

use App\Http\Controllers\SalaryController;
use App\Models\Salary;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LoanReportExport implements FromCollection, WithHeadings, WithColumnFormatting, WithEvents
{
    protected $departmentIds = [];
    protected $month = [];
    protected $year = [];

    /**
     * @var array
     */
    protected $report = [];

    /**
     * Create a new controller instance
     *
     * SalarySheetExport constructor.
     * @param $departmentIds
     * @param $month
     * @param $year
     */
    public function __construct($departmentIds, $month, $year)
    {
        $this->departmentIds = $departmentIds;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ["", "", "", "Loan and Advance Adjustment Report(BYSL Global Technology Group)"];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $reports = [
            [""],
            ["SL. No.", "ID. No", "Employee Name", "Loan", "Advance Salary", "Month", "Year", "Department"],
        ];


        $salaries = Salary::with("user.currentBank")
            ->whereIn("salary_department_id", $this->departmentIds)
            ->where([["month", $this->month], ["year", $this->year]])
            ->get();

        $total_amount_loan = 0;
        $total_amount_advance = 0;

        foreach ($salaries as $key => $salary) {
            $acc = !empty($salary->user->currentBank->account_no) ? $salary->user->currentBank->account_no : "";
            $amount_loan = SalaryController::currencyFormat($salary->loan, 'BHD') ?? ""; #for 3 digit comma separator
            $amount_advance = SalaryController::currencyFormat($salary->advance, 'BHD') ?? ""; #for 3 digit comma separator

            if ($amount_loan > 0 || $amount_advance > 0) {
                array_push($reports, [
                    $key + 1,
                    $salary->user->fingerprint_no,
                    $salary->user->currentBank->account_name ?? "",
                    $amount_loan,
                    $amount_advance,
                    $salary->month,
                    $salary->year,
                    $salary->department->name ?? '',
                ]);
                $total_amount_loan += $salary->loan;
                $total_amount_advance += $salary->advance;
            }
        }

        $total_amount_loan = SalaryController::currencyFormat($total_amount_loan, 'BHD') ?? "";
        $total_amount_advance = SalaryController::currencyFormat($total_amount_advance, 'BHD') ?? "";

        array_push($reports, [
            "", "", "TOTAL", $total_amount_loan, $total_amount_advance
        ]);

        return collect($reports);
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
            //'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(22);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(50);

                $event->sheet->getDelegate()->getStyle('A1:I1')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('538DD5');

                $event->sheet->getDelegate()->getStyle('A1:I1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->getColor()
                    ->setARGB('white');


            },
        ];
    }
}
