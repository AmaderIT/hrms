<?php

namespace App\Exports\Report;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class SalaryReportExport implements FromCollection, WithHeadings, WithEvents
{
    protected $data = null;
    protected $rowNumber = 1;
    protected $departmentRows = [];

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
    public function __construct($SalaryData)
    {
        $this->data = json_decode($SalaryData);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headingText = "SALARY FOR THE MONTH OF:" . strtoupper(date('F', mktime(0, 0, 0, (int)$this->data->month))) . ', ' . $this->data->year;

        return ["", "", "", "", "", $headingText, "", "", "", "", "", "", "", "", "", ""];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $index = 1;

        array_push($this->report, $this->headings());
        array_push($this->report, $this->headers());

        $userNames = User::pluck('name', 'id')->toArray();
        $userOfficeIds = User::pluck('fingerprint_no', 'id')->toArray();
        $designations = Designation::pluck('title', 'id')->toArray();

        foreach ($this->data->departments as $department) {
            if ($this->data->is_employee) {
                foreach ($department->salaries as $salary) {

                    $houseRent = collect($salary->earnings)->where("name", "House Rent")->first()->amount;
                    $medicalAllowance = collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
                    $conveyance = collect($salary->earnings)->where("name", "Conveyance")->first()->amount;

                    $data = [
                        $index,
                        ($userOfficeIds[$salary->user_id]??'-'),
                        ($userNames[$salary->user_id]??'-'),
                        ($designations[$salary->designation_id]??'-'),
                        $salary->basic, $houseRent, $medicalAllowance, $conveyance, $salary->gross,
                        number_format($salary->holiday_amount, 2), number_format($salary->overtime_amount, 2),
                        number_format($salary->payable_amount, 2), number_format($salary->advance, 2),
                        number_format($salary->loan, 2), number_format($salary->payable_tax_amount, 2),
                        number_format($salary->net_payable_amount, 2), $salary->payment_mode
                    ];

                    array_push($this->report, $data);
                    $index++;
                }
            }

            if ($this->data->is_department) {
                $data = [
                    $index, '###', $department->name, '-',
                    array_sum($department->basic),
                    array_sum($department->house_rent),
                    array_sum($department->medical_allowance),
                    array_sum($department->conveyance),
                    array_sum($department->gross),
                    array_sum($department->holiday_amount),
                    array_sum($department->overtime_amount),
                    array_sum($department->payable_amount),
                    array_sum($department->advance_amount),
                    array_sum($department->loan_amount),
                    array_sum($department->payable_tax_amount),
                    array_sum($department->net_payable_amount),
                ];

                array_push($this->report, $data);
                $this->departmentRows[] = $index;
                $index++;
            }
        }

        $data = [
            '', '', '', 'Total',
            $this->data->total->basic,
            $this->data->total->house_rent,
            $this->data->total->medical_allowance,
            $this->data->total->conveyance,
            $this->data->total->gross,
            $this->data->total->holiday_amount,
            $this->data->total->overtime_amount,
            $this->data->total->payable_amount,
            $this->data->total->advance_amount,
            $this->data->total->loan_amount,
            $this->data->total->payable_tax_amount,
            $this->data->total->net_payable_amount
        ];
        array_push($this->report, $data);
        $index++;

        $inWords = [
            "", "", "", "", "IN WORDS",
            \App\Http\Controllers\SalaryController::getBangladeshCurrency($this->data->total->net_payable_amount)
        ];
        array_push($this->report, $inWords);
        $index++;

        $this->rowNumber = $index;
        return collect($this->report);
    }

    /**
     * Write code on Method
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(25);
                $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(25);
                $event->sheet->getDelegate()->getRowDimension($this->rowNumber)->setRowHeight(30);
                $event->sheet->getDelegate()->getRowDimension($this->rowNumber + 1)->setRowHeight(25);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('b')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(40);

                for ($i = 'E'; $i <= 'Q'; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($i)->setWidth(20);
                }

                $event->sheet->getStyle('A1:Q1')
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getStyle('A2:Q2')
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getDelegate()->getStyle('A1:Q1')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('388EEF');

                $event->sheet->getDelegate()->getStyle('A2:Q2')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E7EBF1');

                $event->sheet->getDelegate()->getStyle('A1:Q1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->getColor()
                    ->setARGB('white');

                # Style for Department Rows
                foreach ($this->departmentRows as $departmentRow) {
                    $rowNum = ($departmentRow + 2);

                    $event->sheet->getDelegate()->getStyle('A' . $rowNum . ':Q' . $rowNum)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('ffeac3');

                    $event->sheet->getDelegate()->getStyle('A' . $rowNum . ':Q' . $rowNum)
                        ->getAlignment()->setVertical('Center');

                    $event->sheet->getDelegate()->getRowDimension($rowNum)->setRowHeight(20);
                }

                # Style for Total Rows
                $event->sheet->getDelegate()->getStyle('A' . $this->rowNumber . ':Q' . $this->rowNumber)
                    ->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A' . ($this->rowNumber + 1) . ':Q' . ($this->rowNumber + 1))
                    ->getFont()->setBold(true);

                $event->sheet->getDelegate()->getStyle('A' . $this->rowNumber . ':Q' . $this->rowNumber)
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getDelegate()->getStyle('A' . ($this->rowNumber + 1) . ':Q' . ($this->rowNumber + 1))
                    ->getAlignment()->setVertical('Center');
            },
        ];
    }

    /**
     * @return array
     */
    protected function headers()
    {
        $header = [
            "Sl. No.", "Office ID", "Name", "Designation", "Basic (Tk.)", "House Rent (Tk.)", "Medical Allowance (Tk.)", "Conveyance (Tk.)", "Gross Salary (Tk.)",
            "Holiday Pay (Tk.)", "Over Time (Tk.)", "Total Payable (Tk.)", "Advance (Tk.)", "Loan (Tk.)", "Income Tax (Tk.)", "Net Payable (Tk.)",
            "Payment Mode",
        ];

        return $header;
    }
}
