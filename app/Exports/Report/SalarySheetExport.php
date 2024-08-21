<?php

namespace App\Exports\Report;

use App\Models\Department;
use App\Models\Salary;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class SalarySheetExport implements FromCollection, WithHeadings, WithEvents
{
    protected $data = [];
    protected $month = [];
    protected $year = [];
    protected $rowNumber = [];
    protected $hasCommission = false;

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
        return ["", "", "", "", "", "", "", "", "", "Salary Sheet"];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $tempRow = 3;

        foreach ($this->data as $departmentId) {
            $total = [
                "basic"                     => 0,
                "house_rent"                => 0,
                "medical_allowance"         => 0,
                "conveyance"                => 0,
                "gross"                     => 0,
                "holiday_amount"            => 0,
                "overtime_amount"           => 0,
                "parcel_charge"             => 0,
                "delivery_bonus"            => 0,
                "distance_bonus"            => 0,
                "payable_amount"            => 0,
                "advance"            => 0,
                "casual_leave"              => 0,
                "earn_leave"                => 0,
                "loan"               => 0,
                "absent_salary_deduction"   => 0,
                "late_salary_deduction"     => 0,
                "payable_tax_amount"        => 0,
                "net_payable_amount"        => 0,
            ];

            $salaries = Salary::with("user.employeeStatusJoining", "designation")
                ->where("salary_department_id", $departmentId)
                ->where("month", $this->month)
                ->where("year", $this->year)
                ->get();

            # Check whether has Commission
            foreach ($salaries as $salary) {
                if($salary->parcel_charge > 0) {
                    $this->hasCommission = true;
                    break;
                }
            }

            if($salaries->count() > 0) {
                $departmentName = !empty($salaries[0]->name) ? $salaries[0]->name : '';
                array_push($this->report, [""]);
                array_push($this->report, [""]);
                array_push($this->rowNumber, $tempRow);
                array_push($this->report, [$departmentName]);
                array_push($this->report, $this->commissionColumns());
                $tempRow += 4;
            }

            foreach ($salaries as $key => $salary) {
                $total["basic"] += $salary->basic;
                $total["house_rent"] += collect($salary->earnings)->where("name", "House Rent")->first()->amount;
                $total["medical_allowance"] += collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
                $total["conveyance"] += collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
                $total["gross"] += $salary->gross;
                $total["holiday_amount"] += $salary->holiday_amount;
                $total["overtime_amount"] += $salary->overtime_amount;

                if($this->hasCommission) {
                    $total["parcel_charge"] += $salary->parcel_charge;
                    $total["delivery_bonus"] += $salary->delivery_bonus;
                    $total["distance_bonus"] += $salary->distance_bonus;
                }

                $total["payable_amount"] += $salary->payable_amount;
                $total["advance"] += $salary->advance;
                $total["casual_leave"] += $salary->casual_leave;
                $total["earn_leave"] += $salary->earn_leave;
                $total["loan"] += $salary->loan;
                $total["absent_salary_deduction"] += $salary->absent_salary_deduction;
                $total["late_salary_deduction"] += $salary->late_salary_deduction;
                $total["payable_tax_amount"] += $salary->payable_tax_amount;
                $total["net_payable_amount"] += $salary->net_payable_amount;

                $tempRow++;
                $houseRent = collect($salary->earnings)->where("name", "House Rent")->first()->amount;
                $medicalAllowance = collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
                $conveyance = collect($salary->earnings)->where("name", "Conveyance")->first()->amount;

                if($this->hasCommission) {
                    $data = [
                        $key + 1, $salary->user->fingerprint_no, $salary->user->name, $salary->designation->title,
                        date('M d, Y', strtotime($salary->user->employeeStatusJoining->action_date)),
                        $salary->basic, $houseRent, $medicalAllowance, $conveyance, $salary->gross, number_format($salary->regular_duty, 2),
                        number_format($salary->weekend_holiday_duty, 2), number_format($salary->official_holiday_duty, 2),
                        number_format($salary->late, 2), number_format($salary->leave_days, 2),
                        number_format($salary->absent_days, 2), number_format($salary->overtime_hours, 2),
                        number_format($salary->weekend_holiday_days, 2), number_format($salary->official_holiday_days, 2),
                        number_format($salary->relax_day_days, 2),
                        number_format($salary->holiday_amount, 2), number_format($salary->overtime_amount, 2),
                        number_format($salary->parcel_charge, 2), number_format($salary->delivery_bonus, 2), number_format($salary->distance_bonus, 2),
                        number_format($salary->payable_amount, 2), number_format($salary->advance, 2),
                        number_format($salary->causal_leave, 2), number_format($salary->earn_leave, 2),
                        number_format($salary->loan, 2), number_format($salary->absent_salary_deduction),
                        number_format($salary->late_salary_deduction), number_format($salary->payable_tax_amount, 2),
                        number_format($salary->net_payable_amount, 2), /*number_format($salary->attendance_hours, 2),*/ $salary->payment_mode, $salary->remarks,
                    ];
                } else {
                    $data = [
                        $key + 1, $salary->user->fingerprint_no, $salary->user->name, $salary->designation->title,
                        date('M d, Y', strtotime($salary->user->employeeStatusJoining->action_date)),
                        $salary->basic, $houseRent, $medicalAllowance, $conveyance, $salary->gross, number_format($salary->regular_duty, 2),
                        number_format($salary->weekend_holiday_duty, 2), number_format($salary->official_holiday_duty, 2),
                        number_format($salary->late, 2), number_format($salary->leave_days, 2),
                        number_format($salary->absent_days, 2), number_format($salary->overtime_hours, 2),
                        number_format($salary->weekend_holiday_days, 2), number_format($salary->official_holiday_days, 2),
                        number_format($salary->relax_day_days, 2),
                        number_format($salary->holiday_amount, 2), number_format($salary->overtime_amount, 2),
                        number_format($salary->payable_amount, 2), number_format($salary->advance, 2),
                        number_format($salary->causal_leave, 2), number_format($salary->earn_leave, 2),
                        number_format($salary->loan, 2), number_format($salary->absent_salary_deduction),
                        number_format($salary->late_salary_deduction), number_format($salary->payable_tax_amount, 2),
                        number_format($salary->net_payable_amount, 2), /*number_format($salary->attendance_hours, 2),*/ $salary->payment_mode, $salary->remarks,
                    ];
                }

                array_push($this->report, $data);
            }

            if($this->hasCommission) {
                $summaries = [
                    "", "", "", "", "Total",
                    \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]),
                    "", "", "", "", "", "", "", "", "", "",
                    \App\Http\Controllers\SalaryController::currencyFormat($total["holiday_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["overtime_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["parcel_charge"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["delivery_bonus"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["distance_bonus"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["advance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["casual_leave"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["earn_leave"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["loan"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["absent_salary_deduction"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["late_salary_deduction"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]),
                ];
            } else {
                $summaries = [
                    "", "", "", "", "Total",
                    \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]),
                    "", "", "", "", "", "", "", "", "", "",
                    \App\Http\Controllers\SalaryController::currencyFormat($total["holiday_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["overtime_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["advance"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["casual_leave"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["earn_leave"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["loan"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["absent_salary_deduction"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["late_salary_deduction"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]),
                    \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]),
                ];
            }

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
            AfterSheet::class    => function(AfterSheet $event) {
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
    protected function commissionColumns()
    {
        if($this->hasCommission) {
            $header = [
                "Sl. No", "Office ID", "Name", "Designation", "Joining Date", "Basic", "House Rent", "Medical Allowance", "Conveyance", "Gross",
                "Regular Duty(Days)", "Weekend Holiday Duty (Days)", "Official Holiday Duty (Days)", "Late (Days)", "Leave Days", "Absent (Days)", "Overtime Hours",
                "Weekend Holiday Days", "Official Holiday Days", "Relax Day Days", "Holiday Pay (Tk)", "Overtime (Tk)", "Parcel Charge", "Delivery Bonus", "Distance Bonus",
                "Total Payable", "Advance", "Casual Leave (Days)", "Earn Leave (Days)", "Loan", "Absent (Tk)", "Late (Tk)", "Income Tax", "Net Payable", /*"Hours / Attendance",*/ "Payment Mode", "Remarks",
            ];
        } else {
            $header = [
                "Sl. No", "Office ID", "Name", "Designation", "Joining Date", "Basic", "House Rent", "Medical Allowance", "Conveyance", "Gross",
                "Regular Duty(Days)", "Weekend Holiday Duty (Days)", "Official Holiday Duty (Days)", "Late (Days)", "Leave Days", "Absent (Days)", "Overtime Hours",
                "Weekend Holiday Days", "Official Holiday Days", "Relax Day Days", "Holiday Pay (Tk)", "Overtime (Tk)",
                "Total Payable", "Advance", "Casual Leave (Days)", "Earn Leave (Days)", "Loan", "Absent (Tk)", "Late (Tk)", "Income Tax", "Net Payable", /*"Hours / Attendance",*/ "Payment Mode", "Remarks",
            ];
        }

        return $header;
    }
}
