<?php

namespace App\Library;

use App\Models\BankUser;
use App\Models\Earning;
use App\Models\HolidayAllowance;
use App\Models\LateDeduction;
use App\Models\LeaveType;
use App\Models\LeaveUnpaid;
use App\Models\Loan;
use App\Models\Overtime;
use App\Models\PayGradeEarning;
use App\Models\Profile;
use App\Models\Promotion;
use App\Models\Salary;
use App\Models\Tax;
use App\Models\TaxRule;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\UserLoan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

trait GenerateSalary
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function generate(Request $request)
    {
        try {
            $datePicker = explode("-", $request->input("datepicker"));
            $month = $datePicker[0];
            $year = $datePicker[1];

            $employees = $this->filterEmployees($request, $month, $year);
            foreach ($employees as $user)
            {
                $currentPromotion   = $user->currentPromotion->load("payGrade");
                $grossSalary        = $currentPromotion->salary;
                $basedOn            = $currentPromotion->payGrade->based_on;
                $percentageOfBasic  = $currentPromotion->payGrade->percentage_of_basic;
                $basicSalary        = $grossSalary * ($percentageOfBasic / 100);
                $employeeEarnings   = $user->employeeEarnings();
                $employeeDeductions = $user->employeeDeductions();

                $getEmployeeTotalEarnings   = $this->getEmployeeTotalEarnings($user, $grossSalary, $basicSalary, $employeeEarnings, $basedOn);
                $getEmployeeTotalDeductions = $this->getEmployeeTotalDeductions($user, $grossSalary, $basicSalary, $employeeDeductions, $basedOn);
                $taxableAmount              = $this->employeeTaxableAmountForCurrentMonth($user, $currentPromotion);
                $payableAmount              = ($basicSalary + $getEmployeeTotalEarnings) - ($getEmployeeTotalDeductions + $taxableAmount);

                # Loan Amount
                $employeeLoan = $user->load("activeLoan");
                if(!is_null($employeeLoan->activeLoan))
                {
                    # Check eligible loan amount
                    $installmentAmountOfLoan = $employeeLoan->activeLoan->installment_amount;
                    $employeeLoans = $user->load("userLoans")->userLoans;

                    if($employeeLoans->count() == 0) {
                        $pendingLoan = $employeeLoan->activeLoan->loan_amount;

                        $payLoan = array(
                            "user_id"       => $user->id,
                            "loan_id"       => $employeeLoan->activeLoan->id,
                            "amount_paid"   => $installmentAmountOfLoan,
                            "month"         => $request->input("datepicker")
                        );

                        UserLoan::create($payLoan);
                    } else {
                        $lastLoanPaid = explode("-", $employeeLoans->last()->month);
                        $lastLoanPaidMonth = (int) $lastLoanPaid[0];
                        $lastLoanPaidYear = (int) $lastLoanPaid[1];

                        $month = (int) $month;
                        $year = (int) $year;

                        # Check whether employee paid any custom amount for this current month
                        if($month == $lastLoanPaidMonth && $year == $lastLoanPaidYear) {
                            $installmentAmountOfLoan = $employeeLoans->last()->amount_paid;
                        } else {
                            $pendingLoan = $employeeLoan->activeLoan->loan_amount - $employeeLoans->sum("amount_paid");

                            # Pay Loan Amount
                            if ($pendingLoan < $installmentAmountOfLoan) {
                                $installmentAmountOfLoan = $pendingLoan;
                            }

                            $payLoan = array(
                                "user_id"       => $user->id,
                                "loan_id"       => $employeeLoan->activeLoan->id,
                                "amount_paid"   => $installmentAmountOfLoan,
                                "month"         => $request->input("datepicker")
                            );

                            UserLoan::create($payLoan);
                        }
                    }

                    # Change loan status as paid
                    $pendingLoan = $employeeLoan->activeLoan->loan_amount - ($employeeLoans->sum("amount_paid") + $installmentAmountOfLoan);
                    if((int) $pendingLoan == 0) {
                        $employeeLoan->activeLoan->update(array(
                            "status" => Loan::STATUS_PAID
                        ));
                    }
                } else {
                    $installmentAmountOfLoan = 0;
                }

                $data = array(
                    "user_id"           => $user->id,
                    "pay_grade_id"      => $currentPromotion->payGrade->id,
                    "tax_id"            => $currentPromotion->payGrade->tax_id != 0 ? $currentPromotion->payGrade->tax_id : null,
                    "total_earning"     => $getEmployeeTotalEarnings,
                    "total_deduction"   => $getEmployeeTotalDeductions,
                    "taxable_amount"    => $taxableAmount,
                    "loan_amount"       => $installmentAmountOfLoan,
                    "payable_amount"    => round($payableAmount, 2),
                    "status"            => Salary::STATUS_PAID,
                    "month"             => $month,
                    "year"              => $year,
                    "paid_at"           => now()
                );

                $composite = array(
                    "user_id"           => $user->id,
                    "month"             => $month,
                    "year"              => $year
                );

                $salary  = Salary::firstOrCreate($composite, $data);

                if($salary->wasRecentlyCreated === true) {
                    session()->flash('message', 'Salary Generated Successfully');
                } elseif ($salary->wasRecentlyCreated === false) {
                    session()->flash('type', 'danger');
                    session()->flash('message', 'Salary already generated for this month');
                }
            }

            $redirect = redirect()->route("home");
        } catch (Exception $exception) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Salary not generated yet!!');
            $redirect = redirect()->route("home");
        }

        return $redirect;
    }

    /**
     * @param Request $request
     * @param $month
     * @param $year
     * @return mixed
     */
    protected function filterEmployees(Request $request, $month, $year)
    {
        # Filter employees by date, department, office division
        $startTime = Carbon::createFromDate($year, $month)->startOfMonth()->subYear(50)->format("Y-m-d");
        $endTime = Carbon::createFromDate($year, $month)->lastOfMonth()->format("Y-m-d");

        $users = User::with("currentPromotion")->whereBetween("created_at", array($startTime, $endTime))->select("id", "email")->get();
        if($request->has("user_id") AND !is_null($request->input("user_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request) {
                if(in_array($item->id, $request->input("user_id"))) return $item;
            });
        } elseif($request->has("department_id") AND !is_null($request->input("department_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request) {
                if($item->currentPromotion->department_id == $request->input("department_id")) return $item;
            });
        } elseif($request->has("office_division_id") AND !is_null($request->input("office_division_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request) {
                if($item->currentPromotion->office_division_id == $request->input("office_division_id")) return $item;
            });
        }

        return $filteredUser;
    }

    /**
     * @param $employee
     * @param $grossSalary
     * @param $basicSalary
     * @param $employeeEarnings
     * @param $basedOn
     * @param $tobeDividedBy
     * @return array
     */
    protected function getEmployeeTotalEarnings($employee, $grossSalary, $basicSalary, $employeeEarnings, $basedOn, $tobeDividedBy)
    {
        $employeeEarningData = [];
        $totalEarnings = 0;
        $employeeCashEarningData = [];
        $totalCashEarnings = 0;
        $hasRemaining = false;
        $remainingName = null;
        $employeeRemainingEarning = null;

        if($basedOn == \App\Models\PayGrade::BASED_ON_BASIC) $basedOn = $basicSalary;
        elseif($basedOn == \App\Models\PayGrade::BASED_ON_GROSS) $basedOn = $grossSalary;

        # Earnings
        foreach($employeeEarnings as $employeeEarning)
        {
            if($employeeEarning->non_taxable == 0) {
                if($employeeEarning->type !== PayGradeEarning::TYPE_REMAINING) {
                    if ($employeeEarning->type === \App\Models\PayGradeEarning::TYPE_PERCENTAGE) $amount = $basedOn * ($employeeEarning->value / 100);
                    else $amount = $employeeEarning->value;

                    # Get Tax Eligible Amount
                    $taxEligibleAmount = $this->getTaxEligibleAmount($employeeEarning, $amount, $grossSalary, $basicSalary, $tobeDividedBy);

                    $totalEarnings += $amount;
                    array_push($employeeEarningData, [
                        "name"              => $employeeEarning->earning->name,
                        "amount"            => $amount,
                        "taxEligibleAmount" => $taxEligibleAmount,
                    ]);
                }
            }
            elseif($employeeEarning->non_taxable == 1) {
                if($employeeEarning->type !== PayGradeEarning::TYPE_REMAINING) {
                    if ($employeeEarning->type === \App\Models\PayGradeEarning::TYPE_PERCENTAGE) $amount = $basedOn * ($employeeEarning->value / 100);
                    else $amount = $employeeEarning->value;

                    $totalCashEarnings += $amount;
                    array_push($employeeCashEarningData, [
                        "name"              => $employeeEarning->earning->name,
                        "amount"            => $amount,
                        "taxEligibleAmount" => 0
                    ]);
                }
            }

            if($employeeEarning->type === PayGradeEarning::TYPE_REMAINING AND $hasRemaining !== true) {
                $hasRemaining = true;
                $remainingName = $employeeEarning->earning->name;
                $employeeRemainingEarning = $employeeEarning;
            }
        }

        if($hasRemaining === true) {
            $remainingAmount = ($grossSalary - ($basicSalary + $totalEarnings + $totalCashEarnings));
            $totalEarnings += $remainingAmount;

            # Get Tax Eligible Amount
            $taxEligibleAmount = $this->getTaxEligibleAmount($employeeRemainingEarning, $remainingAmount, $grossSalary, $basicSalary, $tobeDividedBy);

            array_push($employeeEarningData, [
                "name"              => $remainingName,
                "amount"            => $remainingAmount,
                "taxEligibleAmount" => $taxEligibleAmount
            ]);
        }

        return [
            "earnings"          => $employeeEarningData,
            "totalAmount"       => $totalEarnings,
            "cashEarnings"      => $employeeCashEarningData,
            "totalCashAmount"   => $totalCashEarnings,
            "taxEligibleAmount" => collect($employeeEarningData)->sum("taxEligibleAmount")
        ];
    }

    /**
     * @param $employeeEarning
     * @param $amount
     * @param $gross
     * @param $basic
     * @param $tobeDividedBy
     * @return float|int
     */
    protected function getTaxEligibleAmount($employeeEarning, $amount, $gross, $basic, $tobeDividedBy)
    {
        $eligibleTaxExempted = 0;
        if($employeeEarning->tax_exempted != 0 AND $employeeEarning->tax_exempted_percentage != 0) {
            $taxExemptedAmountPerMonth              = $employeeEarning->tax_exempted / $tobeDividedBy;
            $taxExemptedAmountPercentagePerMonth    = ($basic * ($employeeEarning->tax_exempted_percentage / 100));
            $eligibleTaxExempted = ($taxExemptedAmountPerMonth > $taxExemptedAmountPercentagePerMonth) ? $taxExemptedAmountPercentagePerMonth : $taxExemptedAmountPerMonth;
        } elseif ($employeeEarning->tax_exempted != 0 AND $employeeEarning->tax_exempted_percentage == 0) {
            $eligibleTaxExempted = $employeeEarning->tax_exempted / $tobeDividedBy;
            $eligibleTaxExemptedByPercentage = ($gross * ($employeeEarning->value / 100));

            if($employeeEarning->earning->name == Earning::MEDICAL_ALLOWANCE){
                $eligibleTaxExemptedByPercentage = ($basic * (Tax::MEDICAL_TAX_EXEMPTED_PERCENTAGE_OF_BASIC / 100));
            }
            $eligibleTaxExempted = ($eligibleTaxExempted > $eligibleTaxExemptedByPercentage)? $eligibleTaxExemptedByPercentage: $eligibleTaxExempted;

        } elseif ($employeeEarning->tax_exempted == 0 AND $employeeEarning->tax_exempted_percentage != 0) {
            $eligibleTaxExempted = $basic * ($employeeEarning->tax_exempted_percentage / 100);
        }
        $taxEligibleAmount = $amount - $eligibleTaxExempted;

        return $taxEligibleAmount > 0 ? $taxEligibleAmount : 0;
    }

    /**
     * @param $employee
     * @param $grossSalary
     * @param $basicSalary
     * @param $employeeDeductions
     * @param $basedOn
     * @return array
     */
    protected function getEmployeeTotalDeductions($employee, $grossSalary, $basicSalary, $employeeDeductions, $basedOn)
    {
        $employeeDeductionData = [];
        $totalDeductions = 0;

        if($basedOn == \App\Models\PayGrade::BASED_ON_BASIC) $basedOn = $basicSalary;
        elseif($basedOn == \App\Models\PayGrade::BASED_ON_GROSS) $basedOn = $grossSalary;

        # Deductions
        foreach($employeeDeductions as $employeeDeduction)
        {
            if($employeeDeduction->type === \App\Models\PayGradeDeduction::TYPE_PERCENTAGE) $amount = $basedOn * ($employeeDeduction->value / 100);
            else $amount = $employeeDeduction->value;

            $totalDeductions += $amount;
            array_push($employeeDeductionData, [
                "name"      => $employeeDeduction->deduction->name,
                "amount"    => $amount,
            ]);
        }

        return [
            "deductions"    => $employeeDeductionData,
            "totalAmount"   => $totalDeductions
        ];
    }

    /**
     * @param User $user
     * @param $currentPromotion
     * @param $grossSalary
     * @param $taxEligibleAmount
     * @param $tobeDividedBy
     * @return float
     */
    protected function employeeTaxableAmountForCurrentMonth(User $user, $currentPromotion, $grossSalary, $taxEligibleAmount, $tobeDividedBy)
    {
        try {
            if(!is_null($currentPromotion->payGrade->tax_id)) {
                $employeeGender = $user->load("profile")->profile->gender;
                $salary = $grossSalary;
                $employeePayGrade = $currentPromotion->payGrade;
                $basicSalary = $salary * ($employeePayGrade->percentage_of_basic / 100);

                $employeeTax = $employeePayGrade->load("tax.rules")->tax;
                $employeeTaxRules = $employeePayGrade->load("tax.rules")->tax->rules->groupBy("gender");

                if ($employeeGender === Profile::GENDER_MALE) {
                    $gender = TaxRule::GENDER_MALE;
                } elseif ($employeeGender === Profile::GENDER_FEMALE) {
                    $gender = TaxRule::GENDER_FEMALE;
                }

                $taxRules = $employeeTaxRules[$gender];

                $taxRulesPerMonth = array();
                $taxRatesPerMonth = array();
                $temp = 0;
                foreach ($taxRules as $taxRule) {
                    if ($taxRule->slab != TaxRule::SLAB_REMAINING) {
                        $temp += $taxRule->slab / $tobeDividedBy;
                        array_push($taxRulesPerMonth, $temp);
                        array_push($taxRatesPerMonth, $taxRule->rate);
                    } elseif ($taxRule->slab == TaxRule::SLAB_REMAINING) {
                        array_push($taxRulesPerMonth, PHP_INT_MAX);
                        array_push($taxRatesPerMonth, $taxRule->rate);
                    }
                }

                $taxRulesPerMonth = array_reverse($taxRulesPerMonth);
                $taxRatesPerMonth = array_reverse($taxRatesPerMonth);

                $totalTaxEligibleAmount = $basicSalary + $taxEligibleAmount;

                $taxEligibleAmountYearly = $totalTaxEligibleAmount * $tobeDividedBy;

                $taxableAmount = 0;
                for ($i = 0; $i < count($taxRulesPerMonth); $i++) {
                    if ($totalTaxEligibleAmount >= $taxRulesPerMonth[$i]) {
                        $taxableInThisMonth = $totalTaxEligibleAmount - $taxRulesPerMonth[$i];
                        $taxableAmount += $taxableInThisMonth * ($taxRatesPerMonth[$i - 1] / 100);
                        $totalTaxEligibleAmount -= $taxableInThisMonth;
                    }
                }

                /*$taxableAmount = 0;$taxArray = [];
                $totalTaxEligibleAmountYearly = $taxEligibleAmountYearly;
                foreach ($taxRules as $key => $taxRule) {//if($key == 1)dd($taxRule);
                    if ($totalTaxEligibleAmountYearly > $taxRule->slab) {
                        if ($taxRule->rate != 0) {
                            $taxableAmount += ($taxRule->slab * ($taxRule->rate/100)); //(rate*amount)/slab
                            $taxArray[$key] = ($taxRule->slab * ($taxRule->rate/100));
                        }
                        $totalTaxEligibleAmountYearly -= $taxRule->slab;
                    }else{dd($totalTaxEligibleAmountYearly);
                        if ($taxRule->rate != 0) {$taxArray[$key] = ($taxRule->slab * ($taxRule->rate/100));
                            $taxableAmount += ($totalTaxEligibleAmountYearly * ($taxRule->rate/100)); //(rate*amount)/slab
                        }
                    }
                }dd($taxableAmount, $taxArray);*/

                # Rebate manipulation
                $taxableAmount = $this->getTaxAfterRebate($taxEligibleAmountYearly, $employeeTax, $taxableAmount, $employeeTaxRules[TaxRule::TYPE_REBATE], $tobeDividedBy);
            }
            else
            {
                $taxableAmount = 0;
            }
        } catch (Exception $exception) {
            $taxableAmount = 0;
        }

        return round($taxableAmount, 2);
    }

    /**
     * @param $taxEligibleAmountYearly
     * @param $employeeTax
     * @param $taxableAmount
     * @param $rebateRules
     * @param $tobeDividedBy
     * @return float|int|mixed
     */
    protected function getTaxAfterRebate($taxEligibleAmountYearly, $employeeTax, $taxableAmount, $rebateRules, $tobeDividedBy) {
        $taxAfterRebate = $taxEligibleAmountYearly * ($employeeTax->eligible_rebate / 100);
        $taxLiabilityYearly = $taxableAmount * $tobeDividedBy;

        $taxAfterRebateAmount = 0;
        foreach ($rebateRules as $rebateRule) {
            $taxAfterRebateAmount += $taxAfterRebate * ($rebateRule->rate / 100);
        }

        if($taxLiabilityYearly) {
            $taxableAmountYearly = $taxLiabilityYearly - $taxAfterRebateAmount;
            $taxableAmountPerMonth = $taxableAmountYearly / 12;

            $minimumTaxAmountMonthly = $employeeTax->min_tax_amount / 12;
            $taxableAmountPerMonth = max($taxableAmountPerMonth, $minimumTaxAmountMonthly);
        } else {
            $taxableAmountPerMonth = 0;
        }

        return $taxableAmountPerMonth;
    }

    /**
     * @param $datepicker
     * @param User $employee
     * @param $salary
     * @return float|int
     */
    protected function getEmployeeTotalLeaveUnpaidAmount($datepicker, User $employee, $salary)
    {
        $data = \Functions::getMonthAndYearFromDatePicker($datepicker);
        $leaveUnpaidTotalOnTheMonth = LeaveUnpaid::whereMonth("leave_date", $data["month"])->where("user_id", $employee->id)->count();
        $amountToBeDeducted = ($salary / 30) * $leaveUnpaidTotalOnTheMonth;

        return $amountToBeDeducted;
    }

    /**
     * TODO: Optimization: Part 2
     */
    /**
     * @return Builder[]|Collection
     */
    public function queries()
    {
        /**
         * fetch users with their current promotion
         * TODO: Replace all scope named latestPromotion with currentPromotion and finally remove that scope from User model
         */
        $rightQuery = User::with("currentPromotion")->get();

        /**
         * Filter department wise employees
         * This one can be used to prepare salary for specific departments
         */
        $result = $rightQuery->filter(function ($item) {
            if($item->currentPromotion->department_id === 1) return $item;
        });

        // this one can be used instead of first query
        $rightQuery = User::with("currentPromotion")->whereHas("currentPromotion", function ($query) {
            return $query->orderByDesc("id")->take(1);
        })->get();

        /**
         * This one can be used on attendance module
         * to retrieve IN TIME & OUT TIME by a single query of all employees.
         * TODO: Replace the previous scopes and use this query
         */
        $rightQuery = User::addSelect(["lastPromotion" => Promotion::select("id")
            ->whereColumn("user_id", "users.id")
            ->orderByDesc("id") // orderByDesc("created_at") is being avoided due to some data is being generated by seeder
            ->limit(1)
        ])->get();

        return $rightQuery;
    }

    public function generate2()
    {
        $employeeEarnings = $this->employeeEarnings();
        $employeeDeductions = $this->employeeDeductions();
        $employeeTaxableAmounts = $this->employeeTaxableAmounts();
        $employeeLoans = $this->employeeLoans();
        $employeeLoans = $this->employeeBonus();
        $employeeAttendances = $this->employeeAttendances();

        return 0;
    }

    public function employeeEarnings() {}

    public function employeeDeductions() {}

    public function employeeTaxableAmounts() {}

    public function employeeLoans() {}

    public function employeeBonus() {}

    public function employeeAttendances() {}

    /**
     * @param int $number
     * @return false|string
     */
    public static function convertToWord($number = 0)
    {
        $number = round($number, 2);

        $formatter = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
        $word = $formatter->format($number);
        $word = ucwords($word);

        return $word;
    }

    /**
     * @param $employee
     * @param $totalLate
     * @param $unitSalary
     * @param $year
     * @return array
     */
    protected function getEmployeeLateDeductionAmount($employee, $totalLate, $unitSalary, $year)
    {
        $lateDeduction = LateDeduction::where("department_id", $employee->currentPromotion->department_id)->first();

        if(isset($employee->lateAllow->allow)) {
            $totalDays = $employee->lateAllow->allow;
        } else {
            $totalDays = $lateDeduction->total_days;
        }

        $tobeDeducted = $totalDays > 0 ? (int) ($totalLate / $totalDays) : 0;

        $leaveApplication = [];
        $salaryTobeDeducted = 0;
        $previousDeductionForUnpaidLeave = 0;
        if($lateDeduction->type === LateDeduction::TYPE_SALARY) {
            $salaryTobeDeducted = $tobeDeducted * $unitSalary;
        } elseif($lateDeduction->type === LateDeduction::TYPE_LEAVE) {
            $currentLeaveBalance = UserLeave::where("user_id", $employee->id)
                ->where("year", $year)
                ->first();

            $currentLeaveBalanceDetails = collect(json_decode($currentLeaveBalance->leaves));

            $leaveTypes = LeaveType::orderBy('priority', 'ASC')->pluck('id', 'id')->toArray();
            $leaveTypesArr = $leaveTypes;

            /** Sort leaves according to leave_type priority **/
            foreach ($currentLeaveBalanceDetails as $currentLeaveBalDet){
                $leaveTypeId = array_search($currentLeaveBalDet->leave_type_id, $leaveTypesArr);
                if($leaveTypeId){
                    $leaveTypes[$leaveTypeId] = $currentLeaveBalDet;
                }
            }

            $currentLeaveBalanceDetails = $leaveTypes;
            foreach ($currentLeaveBalanceDetails as $currentLeaveBalanceDetail) {
                $leaveTypeName = LeaveType::find($currentLeaveBalanceDetail->leave_type_id)->name;

                if ($currentLeaveBalanceDetail->total_days > 0) {
                    $tobeDeductedNow = $currentLeaveBalanceDetail->total_days - $tobeDeducted;

                    if ($tobeDeductedNow < 0) {
                        array_push($leaveApplication, [
                            "leave_type_id" => $currentLeaveBalanceDetail->leave_type_id,
                            "leave_type_name"   => $leaveTypeName,
                            "to_be_deducted" => $currentLeaveBalanceDetail->total_days,
                        ]);

                        $tobeDeducted -= $currentLeaveBalanceDetail->total_days;

                        if ($currentLeaveBalanceDetail->leave_type_id == LeaveType::UNPAID_LEAVE_ID) {
                            $previousDeductionForUnpaidLeave += $currentLeaveBalanceDetail->total_days;
                        }
                    } else {
                        if ($tobeDeducted > 0) {
                            array_push($leaveApplication, [
                                "leave_type_id" => $currentLeaveBalanceDetail->leave_type_id,
                                "leave_type_name"   => $leaveTypeName,
                                "to_be_deducted" => $tobeDeducted,
                            ]);
                        }

                        $tobeDeducted = 0;
                    }
                }
            }
        }

        if ($tobeDeducted > 0) $salaryTobeDeducted = $tobeDeducted * $unitSalary;

        /** Salary Deduction for Unpaid-Leave **/
        foreach ($leaveApplication as $leaveDeduction) {
            if ($leaveDeduction['leave_type_id'] == LeaveType::UNPAID_LEAVE_ID && $previousDeductionForUnpaidLeave > 0) {
                $salaryTobeDeducted += ($previousDeductionForUnpaidLeave * $unitSalary);
            } elseif ($leaveDeduction['leave_type_id'] == LeaveType::UNPAID_LEAVE_ID && $previousDeductionForUnpaidLeave == 0) {
                $salaryTobeDeducted += ($leaveDeduction['to_be_deducted'] * $unitSalary);
            }
        }

        $deduction = [
            "leave" => json_encode($leaveApplication),
            "salary"=> $salaryTobeDeducted,
        ];

        return $deduction;
    }

    /**
     * @param $employee
     * @param $totalLate
     * @param $unitSalary
     * @return float|int
     */
    protected function getEmployeeLateDeductionAmountOld($employee, $totalLate, $unitSalary)
    {
        $lateDeduction = LateDeduction::where("department_id", $employee->currentPromotion->department_id)->first();

        $lateSalaryDeduction = 0;
        $salaryToBeDeductedForDays = 0;

        if($lateDeduction->type === LateDeduction::TYPE_SALARY) {
            if(isset($employee->lateAllow)) {
                $salaryToBeDeductedForDays = $employee->lateAllow->allow - $totalLate;

                if($salaryToBeDeductedForDays > 0) $salaryToBeDeductedForDays = 0;
                else $salaryToBeDeductedForDays = abs($salaryToBeDeductedForDays);
            }

            if($salaryToBeDeductedForDays > 0) {
                $salaryToBeDeductedForDays = $salaryToBeDeductedForDays / $lateDeduction->total_days;
                $salaryToBeDeductedForDays = floor($salaryToBeDeductedForDays);
            }

            $lateSalaryDeduction = $salaryToBeDeductedForDays * $unitSalary;
        }

        return $lateSalaryDeduction;
    }

    /**
     * @param $employee
     * @param $overtimeHours
     * @param $grossSalary
     * @param $basicSalary
     * @return mixed
     */
    protected function getEmployeeOvertimeAmount($employee, $overtimeHours, $grossSalary, $basicSalary) {
        $formula = $employee->currentPromotion->payGrade->overtime_formula;
        $parameters = ["gross" => $grossSalary, "basic" => $basicSalary, "othours" => $overtimeHours];

        return $this->executeFormula($formula, $parameters);
    }

    /**
     * @param $employee
     * @param $grossSalary
     * @param $basicSalary
     * @param $weekendHolidayDuty
     * @param $officialHolidayDuty
     * @param $month
     * @param $year
     * @return array
     */
    protected function getEmployeeHolidayAmount($employee, $grossSalary, $basicSalary, $weekendHolidayDuty, $officialHolidayDuty, $month, $year) {
        $weeklyHolidayAmount            = 0;
        $organizationalHolidayAmount    = 0;
        $parameters = ["gross" => $grossSalary, "basic" => $basicSalary];

        # Weekly Holiday
        $formula            = $employee->currentPromotion->payGrade->weekend_allowance_formula;
        $perDayAmount       = $this->executeFormula($formula, $parameters);
        $weeklyHolidayAmount= $weekendHolidayDuty * $perDayAmount;

        # Official Holiday
        $formula                    = $employee->currentPromotion->payGrade->holiday_allowance_formula;
        $perDayAmount               = $this->executeFormula($formula, $parameters);
        $organizationalHolidayAmount= $officialHolidayDuty * $perDayAmount;

        $totalHolidayAmount = $weeklyHolidayAmount + $organizationalHolidayAmount;

        return [
            "total_holiday_amount"  => $totalHolidayAmount,
            "holiday_amount"        => json_encode([
                HolidayAllowance::TYPE_WEEKLY           => $weeklyHolidayAmount,
                HolidayAllowance::TYPE_ORGANIZATIONAL   => $organizationalHolidayAmount
            ]),
        ];
    }

    /**
     * @param $employee
     * @param $month
     * @param $year
     * @return array
     */
    protected function getEmployeeLoanAdvanceSalaryAmount($employee, $month, $year): array
    {
        $totalLoan = 0;
        $totalAdvance = 0;

        # Define active loans of the specific employee.
        $employeeLoans = $employee->load("activeLoans.userLoans");
        $activeLoans = $employeeLoans->activeLoans;

        foreach ($activeLoans as $activeLoan) {
            $loan = 0;
            $advance = 0;

            if (!empty($activeLoan->userLoans)) {
                $thisMonthInstalment = $activeLoan->userLoans
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('status', UserLoan::AMOUNT_APPROVED)
                    ->first();

                if ($thisMonthInstalment) {
                    $thisMonthInstalment->status = UserLoan::DEDUCTION_PENDING;
                    $thisMonthInstalment->updated_by = auth()->id();
                    $thisMonthInstalment->update();

                    if ($activeLoan->type === Loan::TYPE_LOAN) $loan = $thisMonthInstalment->amount_paid;
                    else $advance = $thisMonthInstalment->amount_paid;
                }

                $totalLoan += $loan;
                $totalAdvance += $advance;
            }
        }

        return [
            "loan" => $totalLoan,
            "advance" => $totalAdvance
        ];
    }

    /**
     * @param $formula
     * @param $parameters
     * @return mixed
     */
    protected function executeFormula($formula, $parameters)
    {
        extract($parameters);
        return eval("return ${formula};");
    }

    /**
     * @param $data
     * @param $partial
     * @return array
     */
    protected function calculatePartial($data, $partial)
    {
        $employee = $partial->where("user_id", $data["user_id"]);

        if($employee->count() > 0) {

            $toBePaid = $employee->first()["to_be_paid"];

            $salary             = ($data["salary"] * $toBePaid) / 100;
            $basic              = ($data["basic"] * $toBePaid) / 100;
            $totalEarnings      = ($data["total_earning"] * $toBePaid) / 100;
            $totalCashEarnings  = ($data["total_cash_earning"] * $toBePaid ) / 100;
            $totalDeduction     = ($data["total_deduction"] * $toBePaid) / 100;
            $overTimeAmount     = $data["overtime_amount"];
            $totalHolidayAmount = $data["total_holiday_amount"];
            $leaveUnpaidTotal   = $data["leave_unpaid_amount"];
            $payableTaxAmount   = $data["payable_tax_amount"];
            $loanAmount         = $data["loan_amount"];

            $payableAmount = $basic + $totalEarnings + $totalCashEarnings - $totalDeduction + $overTimeAmount + $totalHolidayAmount + $leaveUnpaidTotal - $payableTaxAmount - $loanAmount;

            $result = [
                "user_id"               => $data["user_id"],
                "office_division_id"    => $data["office_division_id"],
                "department_id"         => $data["department_id"],
                "pay_grade_id"          => $data["pay_grade_id"],
                "tax_id"                => $data["tax_id"],
                "salary"                => $salary,
                "basic"                 => $basic,
                "earnings"              => $this->getPartialAmounts($data["earnings"], $toBePaid),
                "cash_earnings"         => $this->getPartialAmounts($data["cash_earnings"], $toBePaid),
                "total_earning"         => $totalEarnings,
                "total_cash_earning"    => $totalCashEarnings,
                "deductions"            => $this->getPartialAmounts($data["deductions"], $toBePaid),
                "total_deduction"       => $totalDeduction,
                "overtime_amount"       => $overTimeAmount,
                "holiday_amount"        => $data["holiday_amount"],
                "total_holiday_amount"  => $totalHolidayAmount,
                "leave_unpaid_amount"   => $leaveUnpaidTotal,
                "taxable_amount"        => $data["taxable_amount"],
                "payable_tax_amount"    => $payableTaxAmount,
                "loan_amount"           => $loanAmount,
                "payable_amount"        => $payableAmount,
                "status"                => $data["status"],
                "month"                 => $data["month"],
                "year"                  => $data["year"],
                "paid_at"               => $data["paid_at"]
            ];
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * @param $data
     * @param $toBePaid
     * @return array
     */
    protected function getPartialAmounts($data, $toBePaid)
    {
        $result = [];
        foreach ($data as $key => $item) {
            $subItem = [];
            foreach ($item as $index => $value) {
                $subItem[$index] = !is_numeric($value) ? $value : ($value !== 0 ? ($value * $toBePaid) / 100 : 0);
            }
            array_push($result, $subItem);
        }
        return $result;
    }

    /**
     * @param $employeeId
     * @return float|mixed
     */
    protected function remainingTaxOpening($employeeId)
    {
        $remainingTaxOpeningBalanceSoFar = Salary::where("user_id", $employeeId)->orderByDesc("id")->first();

        if(isset($remainingTaxOpeningBalanceSoFar)) {
            $remainingTaxOpeningBalanceSoFar = $remainingTaxOpeningBalanceSoFar->remaining_tax_opening_balance;
        } else {
            $remainingTaxOpeningBalanceSoFar = BankUser::where("user_id", $employeeId)->first()->tax_opening_balance ?? 0.00 ;
        }

        return $remainingTaxOpeningBalanceSoFar;
    }
}
