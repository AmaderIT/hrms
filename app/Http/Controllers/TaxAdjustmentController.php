<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\SalaryDepartment;
use App\Models\SalaryLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxAdjustmentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function adjustTaxAmount(Request $request): JsonResponse
    {
        $netPayableTaxAmount = round($request->payable_tax_amount, 2);

        if ($netPayableTaxAmount >= 0) {

            $salaryDpt = SalaryDepartment::where('uuid', $request->uuid_dpt)->first();

            if ($salaryDpt && $salaryDpt->status == SalaryDepartment::STATUS_PAID) {
                return response()->json(['status' => 'error', 'message' => 'Adjustment could not be processed because of paid salary!']);
            }

            $salary = Salary::where('uuid', $request->uuid)->first();


            if ($salary) {
                $previousTaxAmount = $salary->payable_tax_amount;
                $netPayableAmount = round($salary->net_payable_amount + $salary->payable_tax_amount, 2);
                $netPayableAmount = round(($netPayableAmount - $netPayableTaxAmount), 2);

                $salary->payable_tax_amount = $netPayableTaxAmount;
                $salary->net_payable_amount = $netPayableAmount;

                if ($netPayableTaxAmount <= $salary->payable_amount && $salary->save()) {

                    $dptSalaryIds = SalaryDepartment::where('uuid', $request->uuid_dpt)->pluck('id');

                    $totalNetPayable = round(Salary::where('salary_department_id', $dptSalaryIds)->sum('net_payable_amount'), 2);

                    if ($salaryDpt) {
                        $salaryDpt->total_payable_amount = round($totalNetPayable, 2);

                        $salaryDpt->divisional_approval_status = 0;
                        $salaryDpt->divisional_approval_by = null;
                        $salaryDpt->divisional_approved_date = null;

                        $salaryDpt->departmental_approval_status = 0;
                        $salaryDpt->departmental_approval_by = null;
                        $salaryDpt->departmental_approved_date = null;

                        $salaryDpt->hr_approval_status = 0;
                        $salaryDpt->hr_approval_by = null;
                        $salaryDpt->hr_approved_date = null;

                        $salaryDpt->accounts_approval_status = 0;
                        $salaryDpt->accounts_approval_by = null;
                        $salaryDpt->accounts_approved_date = null;

                        $salaryDpt->managerial_approval_status = 0;
                        $salaryDpt->managerial_approval_by = null;
                        $salaryDpt->managerial_approved_date = null;
                        $salaryDpt->status = SalaryDepartment::STATUS_PENDING;

                        $salaryDpt->save();
                    }

                    /**
                     * salary tax amount update log
                     */
                    $remarks = "Updated for -{$salary->user->name} ({$salary->user->fingerprint_no}). Previous Amount-{$previousTaxAmount} & New Amount-{$netPayableTaxAmount}";

                    $action = SalaryLog::TAX_ADJUSTMENT;
                    SalaryLog::generateSalaryLog($salaryDpt->uuid, $action, auth()->user()->id, $remarks);

                    return response()->json(['status' => 'success', 'message' => 'Tax adjustment successfully completed.']);
                }
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Invalid Tax Amount!']);
    }
}
