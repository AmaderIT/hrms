<?php

namespace App\Exports\Report;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class BonusReportExport implements FromCollection, WithHeadings, WithEvents
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
    public function __construct($bonusData)
    {
        $this->data = json_decode($bonusData);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headingText = "BONUS NAME: " . $this->data->bonus_name;

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
                foreach ($department->bonuses as $bonus) {

                    $houseRent = $bonus->house_rent;
                    $medicalAllowance = $bonus->medical_allowance;
                    $conveyance = $bonus->conveyance;

                    $data = [
                        $index,
                        ($userOfficeIds[$bonus->user_id]??'-'),
                        ($userNames[$bonus->user_id]??'-'),
                        ($designations[$bonus->designation_id]??'-'),
                        $bonus->basic, $houseRent, $medicalAllowance, $conveyance, $bonus->gross,
                        number_format($bonus->amount, 2), number_format($bonus->tax, 2),
                        number_format($bonus->net_payable_amount, 2), $bonus->payment_mode
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
                    array_sum($department->payable_amount),
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
            $this->data->total->payable_amount,
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

                for ($i = 'E'; $i <= 'M'; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($i)->setWidth(20);
                }

                $event->sheet->getStyle('A1:M1')
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getStyle('A2:M2')
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getDelegate()->getStyle('A1:M1')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('388EEF');

                $event->sheet->getDelegate()->getStyle('A2:M2')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E7EBF1');

                $event->sheet->getDelegate()->getStyle('A1:M1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->getColor()
                    ->setARGB('white');

                # Style for Department Rows
                foreach ($this->departmentRows as $departmentRow) {
                    $rowNum = ($departmentRow + 2);

                    $event->sheet->getDelegate()->getStyle('A' . $rowNum . ':M' . $rowNum)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('ffeac3');

                    $event->sheet->getDelegate()->getStyle('A' . $rowNum . ':M' . $rowNum)
                        ->getAlignment()->setVertical('Center');

                    $event->sheet->getDelegate()->getRowDimension($rowNum)->setRowHeight(20);
                }

                # Style for Total Rows
                $event->sheet->getDelegate()->getStyle('A' . $this->rowNumber . ':M' . $this->rowNumber)
                    ->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A' . ($this->rowNumber + 1) . ':M' . ($this->rowNumber + 1))
                    ->getFont()->setBold(true);

                $event->sheet->getDelegate()->getStyle('A' . $this->rowNumber . ':M' . $this->rowNumber)
                    ->getAlignment()->setVertical('Center');

                $event->sheet->getDelegate()->getStyle('A' . ($this->rowNumber + 1) . ':M' . ($this->rowNumber + 1))
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
            "Total Payable (Tk.)", "Income Tax (Tk.)", "Net Payable (Tk.)",
            "Payment Mode",
        ];

        return $header;
    }
}
