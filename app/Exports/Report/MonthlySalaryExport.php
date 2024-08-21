<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlySalaryExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $report = [];

    /**
     * @var array
     */
    protected $totalEarnings = [];

    /**
     * @var array
     */
    protected $totalDeductions = [];

    /**
     * @var array
     */
    protected $totalCashEarnings = [];

    /**
     * Create a new controller instance
     *
     * MonthlyAttendanceExport constructor.
     * @param $salaryReport
     */
    public function __construct($salaryReport)
    {
        $this->data = $salaryReport;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            "SALARY FOR THE MONTH OF: ", "", "",
            "{$this->data['month']}",
            "NUMBER OF WORKING DAYS", "", "",
            "{$this->data['workingDays']}",
            "PREPARATION DATE: ",
            "{$this->data['preparation_date']}"
        ];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $this->appendSubHeadings();
        $earningsName       = $this->getAllEarningsName();
        $deductionsName     = $this->getAllDeductionsName();
        $cashEarningsName   = $this->getAllCashEarningsName();

        $row = [];
        foreach ($this->data["salary"] as $salary) {
            $row = [
                $salary->user->fingerprint_no,
                $salary->user->name,
                $salary->user->currentPromotion->designation->title,
                (int) $salary->salary,
                (int) $salary->basic,
            ];

            # Earnings
            $earnings = $salary->earnings;
            foreach ($earningsName as $value) {
                $allEarnings = collect($earnings)->pluck("name")->toArray();

                if(!in_array($value, $allEarnings)) array_push($row, "0.00");

                foreach ($earnings as $earning) {
                    $earning->name === $value ? array_push($row, strval( $earning->amount )) : "0.00";
                }
            }

            # Deductions
            $deductions = $salary->deductions;
            foreach ($deductionsName as $value) {
                $allDeductions = collect($deductions)->pluck("name")->toArray();

                if(!in_array($value, $allDeductions)) array_push($row, "0.00");

                foreach ($deductions as $deduction) {
                    $deduction->name === $value ? array_push($row, strval( $deduction->amount )) : "0.00";
                }
            }

            # Cash Earnings
            $cashEarnings = $salary->cash_earnings;
            foreach ($cashEarningsName as $value) {

                $allCashEarnings = collect($cashEarnings)->pluck("name")->toArray();

                if(!in_array($value, $allCashEarnings)) array_push($row, "0.00");

                foreach ($cashEarnings as $cashEarning) {
                    $cashEarning->name === $value ? array_push($row, strval( $cashEarning->amount )) : "0.00";
                }
            }
            array_push($row,
                strval( $salary->overtime_amount ),
                strval( $salary->total_holiday_amount ),
                strval( $salary->leave_unpaid_amount ),
                strval( $salary->taxable_amount ),
                strval( $salary->loan_amount ),
                strval( $salary->payable_amount ),
            );

            array_push($this->report, $row);
        }

        return collect($this->report);
    }

    /**
     * Append the sub headings to the excel sheet
     *
     * return @void
     */
    protected function appendSubHeadings(): void
    {
        $earnings       = $this->getAllEarningsName();
        $deductions     = $this->getAllDeductionsName();
        $cashEarnings   = $this->getAllCashEarningsName();

        $row = ["ID", "Employee Name", "Designation", "Gross", "Basic"];
        foreach ($earnings as $earning) array_push($row, $earning);
        foreach ($deductions as $deduction) array_push($row, $deduction);
        foreach ($cashEarnings as $cashEarning) array_push($row, $cashEarning);
        array_push($row, "Overtime", "Holiday", "Unpaid Leave", "Taxable", "Loan", "Net Payable");

        array_push($this->report, $row);
    }

    /**
     * @return array
     */
    protected function getAllEarningsName(): array
    {
        $earnings = $this->data["salary"]->pluck("earnings");
        $allEarnings = [];
        foreach ($earnings as $earning) {
            foreach ($earning as $name) {
                !in_array($name->name, $allEarnings) ? array_push($allEarnings, $name->name) : null;
            }
        }
        $this->totalEarnings = count($allEarnings);

        return $allEarnings;
    }

    /**
     * @return array
     */
    protected function getAllDeductionsName(): array
    {
        $deductions = $this->data["salary"]->pluck("deductions");
        $allDeductions = [];
        foreach ($deductions as $deduction) {
            foreach ($deduction as $name) {
                !in_array($name->name, $allDeductions) ? array_push($allDeductions, $name->name) : null;
            }
        }
        $this->totalDeductions = count($allDeductions);

        return $allDeductions;
    }

    /**
     * @return array
     */
    protected function getAllCashEarningsName(): array
    {
        $cashEarnings = $this->data["salary"]->pluck("cash_earnings");
        $allCashEarnings = [];

        foreach ($cashEarnings as $cashEarning) {
            foreach ($cashEarning as $name) {
                !in_array($name->name, $allCashEarnings) ? array_push($allCashEarnings, $name->name) : null;
            }
        }
        $this->totalCashEarnings = count($allCashEarnings);

        return $allCashEarnings;
    }
}
