<?php

namespace App\Exports\Report;

use App\Models\UserBonus;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class BonusSheetExport implements FromCollection, WithHeadings, WithEvents
{
    protected $data = [];
    protected $month = [];
    protected $year = [];
    protected $rowNumber = [];

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
        $this->data = $departmentIds;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ["", "", "", "", "", "Bonus Sheet"];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $tempRow = 3;

        foreach ($this->data as $departmentId) {
            $total = [
                "basic" => 0,
                "house_rent" => 0,
                "medical_allowance" => 0,
                "conveyance" => 0,
                "gross" => 0,
                "payable_amount" => 0,
                "payable_tax_amount" => 0,
                "net_payable_amount" => 0,
            ];

            $bonuses = UserBonus::with("user.employeeStatusJoining", "designation")
                ->where("bonus_department_id", $departmentId)
                ->where("month", $this->month)
                ->where("year", $this->year)
                ->get();

            if ($bonuses->count() > 0) {
                $departmentName = !empty($bonuses[0]->department->name) ? $bonuses[0]->department->name : '';
                array_push($this->report, [""]);
                array_push($this->rowNumber, $tempRow);
                array_push($this->report, [$departmentName]);
                array_push($this->report, $this->columnNames());
                $tempRow += 3;
            }

            foreach ($bonuses as $key => $bonus) {
                $total["basic"] += $bonus->basic;
                $total["house_rent"] += $bonus->house_rent;
                $total["medical_allowance"] += $bonus->medical_allowance;
                $total["conveyance"] += $bonus->conveyance;
                $total["gross"] += $bonus->gross;
                $total["payable_amount"] += $bonus->amount;
                $total["payable_tax_amount"] += $bonus->tax;
                $total["net_payable_amount"] += $bonus->net_payable_amount;

                $tempRow++;
                $houseRent = $bonus->house_rent;
                $medicalAllowance = $bonus->medical_allowance;
                $conveyance = $bonus->conveyance;

                $data = [
                    $key + 1, $bonus->user->fingerprint_no, $bonus->user->name, $bonus->designation->title,
                    date('M d, Y', strtotime($bonus->user->employeeStatusJoining->action_date)),
                    $bonus->basic, $houseRent, $medicalAllowance, $conveyance, $bonus->gross,
                    number_format($bonus->amount, 2),
                    number_format($bonus->tax, 2),
                    number_format($bonus->net_payable_amount, 2),
                    $bonus->payment_mode, $bonus->remarks,
                ];

                array_push($this->report, $data);
            }

            $summaries = [
                "", "", "", "", "Total",
                \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]),
                \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]),
            ];

            array_push($this->report, $summaries);

            $inWords = [
                "", "", "", "", "IN WORDS",
                \App\Http\Controllers\SalaryController::getBangladeshCurrency($total["net_payable_amount"])
            ];
            array_push($this->report, $inWords);
            $tempRow += 2;
        }

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
                foreach ($this->rowNumber as $row) {
                    $event->sheet->getDelegate()->getStyle("A{$row}:AC{$row}")
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('A9A9A9');
                }
            },
        ];
    }

    /**
     * @return array
     */
    protected function columnNames()
    {
        return [
            "Sl. No", "Office ID", "Name", "Designation", "Joining Date", "Basic", "House Rent", "Medical Allowance", "Conveyance", "Gross",
            "Total Payable", "Income Tax", "Net Payable", "Payment Mode", "Remarks",
        ];
    }
}
