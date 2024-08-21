<?php

namespace App\Http\Controllers;

use App\Http\Requests\loan\RequestLoan;
use App\Http\Requests\loan\RequestLoanUpdate;
use App\Models\Department;
use App\Models\Loan;
use App\Models\OfficeDivision;
use App\Models\User;
use App\Models\UserLoan;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        $departmentIds = FilterController::getDepartmentIds();
        $officeDivisionIds = FilterController::getDivisionIds();

        $itemsQuery = Loan::with("user.currentPromotion", "user.currentPromotion.department", "user.currentPromotion.designation");

        if (request()->has('department_id')) {
            $itemsQuery = $itemsQuery->whereIn("department_id", request()->get("department_id"));
        } elseif (count($departmentIds) > 0) {
            $itemsQuery = $itemsQuery->whereIn('department_id', $departmentIds);
        } else {
            $itemsQuery = $itemsQuery->where('user_id', auth()->id());
        }

        if (request()->has('office_division_id') and in_array(request()->get("office_division_id"), $officeDivisionIds)) {
            $itemsQuery = $itemsQuery->whereIn("office_division_id", [request()->get("office_division_id")]);
        } elseif (count($officeDivisionIds) > 0) {
            $itemsQuery = $itemsQuery->whereIn('office_division_id', $officeDivisionIds);
        } else {
            $itemsQuery = $itemsQuery->where('user_id', auth()->id());
        }

        if (!empty(request()->get('type'))) {
            $itemsQuery = $itemsQuery->where("type", request()->get('type'));
        }

        if (!empty(request()->get('status'))) {
            $itemsQuery = $itemsQuery->where("status", request()->get('status'));
        }

        $officeDivisions = OfficeDivision::whereIn('id', $officeDivisionIds)->pluck('name', 'id')->toArray();
        $departments = Department::whereIn('id', $departmentIds)->pluck("name", "id")->toArray();

        $items = $itemsQuery->orderByDesc("id")
            ->paginate(\Functions::getPaginate());

        return view("loan.index", compact("items", "officeDivisions", "departments"));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        return view('loan.create');
    }

    /**
     * @param Loan $loan
     * @return Factory|View
     */
    public function edit(Loan $loan)
    {
        $loan = $loan->load("user");

        $departmentIds = FilterController::getDepartmentIds();

        $unauthorizedAccess = false;
        if (count($departmentIds) > 0) {
            if (!in_array($loan->department_id, $departmentIds)) {
                $unauthorizedAccess = true;
            }
        } else {
            if ($loan->user_id != auth()->id()) {
                $unauthorizedAccess = true;
            }
        }

        if (
            $loan->status != Loan::STATUS_PENDING || $loan->departmental_approval_status == Loan::STATUS_APPROVED ||
            $loan->divisional_approval_status == Loan::STATUS_APPROVED || $loan->hr_approval_status == Loan::STATUS_APPROVED
        ) {
            $unauthorizedAccess = true;
        }

        if ($unauthorizedAccess) {
            session()->flash("type", "error");
            session()->flash("message", 'Unauthorized Access!!');
            return \Redirect::route('loan.index');
        }

        if ($loan->user_id)

            $data = array(
                "officeDivisions" => OfficeDivision::select("id", "name")->get(),
                "departments" => Department::where("office_division_id", $loan->office_division_id)->select("id", "name")->get(),
            );

        return view("loan.create", compact("loan", "data"));
    }

    /**
     * @param RequestLoan $request
     * @return RedirectResponse
     */
    public function store(RequestLoan $request)
    {
        try {
            $months = $request->month;
            $amounts = $request->amount_paid;
            $remarks = $request->remark;

            if (count($months) != count($amounts) || count($amounts) != count($remarks)) {
                throw new Exception('Invalid installments data!!');
            }

            $duplicateMonth = false;
            $monthCounts = array_count_values($months);
            foreach ($monthCounts as $monthVal) {
                if ($monthVal > 1) {
                    $duplicateMonth = true;
                }
            }

            if ($duplicateMonth) {
                throw new Exception('Instalment month must be unique!!');
            }

            if ($request->loan_amount != array_sum($amounts)) {
                throw new Exception('Loan amount and sum of instalment amount must be same!!');
            }

            $inputs = $request->validated();

            $violations = $this->getPolicyViolations(['type' => $request->type, 'loan_amount' => $request->loan_amount], Auth::id());

            $violations = array_filter($violations, function ($val) {
                return $val == false;
            });

            if (count($violations) > 0) {
                $inputs['policy_violations'] = json_encode($violations);
            }

            $loan = Loan::create($inputs);

            $loanInstallments = [];
            foreach ($months as $key => $monthDate) {
                $datePicker = \Functions::getMonthAndYearFromDatePicker($monthDate);
                $month = $datePicker["month"];
                $year = $datePicker["year"];

                $loanInstallments[] = [
                    'uuid' => \Functions::getNewUuid(),
                    'user_id' => Auth::id(),
                    'loan_id' => $loan->id,
                    'amount_paid' => $amounts[$key],
                    'month' => $month,
                    'year' => $year,
                    'remark' => !empty($remarks[$key]) ? $remarks[$key] : '',
                    'created_by' => Auth::id(),
                    'created_at' => Carbon::now(),
                ];
            }

            if (count($loanInstallments) == 0) {
                throw new Exception('Invalid installments data!!');
            }

            UserLoan::insert($loanInstallments);

            session()->flash("message", "Loan Application has been Submitted");
            $redirect = redirect()->route("loan.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->input());
        }

        return $redirect;
    }

    /**
     * @param RequestLoanUpdate $request
     * @param Loan $loan
     * @return RedirectResponse
     */
    public function update(RequestLoanUpdate $request, Loan $loan)
    {
        $inputIds = $request->ids ?? [];
        $months = $request->month;
        $amounts = $request->amount_paid;
        $remarks = $request->remark;

        $userLoanIds = $loan->userLoans()->pluck('id')->toArray();
        $mixMatchUserLoanIds = [];

        if (count($inputIds) > 0) {
            $mixMatchUserLoanIds = array_diff(array_values($inputIds), array_values($userLoanIds));
        }

        try {
            $departmentIds = FilterController::getDepartmentIds();

            $unauthorizedAccess = false;
            if (count($departmentIds) > 0) {
                if (!in_array($loan->department_id, $departmentIds)) {
                    $unauthorizedAccess = true;
                }
            } else {
                if ($loan->user_id != auth()->id()) {
                    $unauthorizedAccess = true;
                }
            }

            if (
                $loan->status != Loan::STATUS_PENDING || $loan->departmental_approval_status == Loan::STATUS_APPROVED ||
                $loan->divisional_approval_status == Loan::STATUS_APPROVED || $loan->hr_approval_status == Loan::STATUS_APPROVED
            ) {
                $unauthorizedAccess = true;
            }

            if ($unauthorizedAccess) {
                throw new Exception('Unauthorized Access!!');
            }

            if (!empty($mixMatchUserLoanIds)) {
                throw new Exception('Something went wrong! Please try again.');
            }

            if ((count($months) != count($amounts)) || (count($amounts) != count($remarks))) {
                throw new Exception('Invalid installments data!!');
            }

            $duplicateMonth = false;
            $monthCounts = array_count_values($months);
            foreach ($monthCounts as $monthVal) {
                if ($monthVal > 1) {
                    $duplicateMonth = true;
                }
            }

            if ($duplicateMonth) {
                throw new Exception('Instalment month must be unique!!');
            }

            if ($request->loan_amount != array_sum($amounts)) {
                throw new Exception('Loan amount and sum of instalment amount must be same!!');
            }

            $inputs = $request->validated();

            $violations = $this->getPolicyViolations(['type' => $request->type, 'loan_amount' => $request->loan_amount], $loan->user_id);

            $violations = array_filter($violations, function ($val) {
                return $val == false;
            });

            if (count($violations) > 0) {
                $inputs['policy_violations'] = json_encode($violations);
            }

            $newLoanInstallments = [];
            foreach ($months as $key => $monthDate) {
                $datePicker = \Functions::getMonthAndYearFromDatePicker($monthDate);
                $month = $datePicker["month"];
                $year = $datePicker["year"];

                if ((!empty($inputIds[$key])) && in_array($inputIds[$key], $userLoanIds)) {

                    $loanInstallment = [
                        'amount_paid' => $amounts[$key],
                        'month' => $month,
                        'year' => $year,
                        'remark' => !empty($remarks[$key]) ? $remarks[$key] : '',
                        'updated_by' => Auth::id(),
                        'updated_at' => Carbon::now(),
                    ];

                    #Update Instalments
                    UserLoan::where('id', $inputIds[$key])->update($loanInstallment);
                    unset($userLoanIds[$key - 1]);
                } else {
                    $newLoanInstallments[] = [
                        'uuid' => \Functions::getNewUuid(),
                        'user_id' => $loan->user_id,
                        'loan_id' => $loan->id,
                        'amount_paid' => $amounts[$key],
                        'month' => $month,
                        'year' => $year,
                        'remark' => !empty($remarks[$key]) ? $remarks[$key] : '',
                        'created_by' => Auth::id(),
                        'created_at' => Carbon::now(),
                    ];
                }
            }

            # Update Loan
            Loan::where('id', $loan->id)->update($inputs);

            # Insert New Installments
            if (count($newLoanInstallments) > 0) {
                UserLoan::insert($newLoanInstallments);
            }

            # Delete Instalments
            if (count($userLoanIds) > 0) {
                UserLoan::destroy($userLoanIds);
            }

            session()->flash("message", "Loan/Advance Successfully Updated");
            $redirect = redirect()->route("loan.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
    }

    /**
     * @param Loan $loan
     * @return Factory|View
     */
    public function show(Loan $loan)
    {
        $loan = $loan->load("user");

        $divisionIds = null;
        $departmentIds = null;
        if (auth()->user()->can('Show All Salary List')) {
            $divisionIds = FilterController::getDivisionIds(true, true);
            $departmentIds = FilterController::getDepartmentIds(0, true, true);
        } else {
            $divisionIds = FilterController::getDivisionIds(false, true);
            $departmentIds = FilterController::getDepartmentIds(0, false, true);
        }

        $userDepartmentIds = FilterController::getDepartmentIds();

        return view("loan.show", compact("loan", "divisionIds", "departmentIds", "userDepartmentIds"));
    }

    /**
     * @param Loan $loan
     * @return mixed
     */
    public function delete(Loan $loan)
    {
        try {
            $feedback['status'] = $loan->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function getActiveLoans($userId)
    {
        $user = User::where('id', $userId)->first();

        $data = [];
        if ($user) {
            $data = Loan::where(['user_id' => $user->id, 'status' => Loan::STATUS_ACTIVE])
                ->select("id", "type", "loan_amount", "installment_amount")
                ->get();
        }

        return response()->json(array("data" => $data));
    }

    public function checkLoanPolicy(Request $request, $userId = null)
    {
        $inputs = [];
        parse_str($request->data, $inputs);

        if (!$userId) {
            $userId = Auth::id();
        }

        if (empty($inputs['type'])) {
            $loan = Loan::where('uuid', $inputs['loan_uuid'])->first();
            $inputs['type'] = $loan->type;
            $userId = $loan->user_id;
        }

        $policyViolations = $this->getPolicyViolations($inputs, $userId);

        return $policyViolations;

    }

    public function getPolicyViolations($data, $userId)
    {
        $policyViolations = [];

        $user = User::where('id', $userId)->first();
        $joiningDate = !empty($user->employeeStatusJoining->action_date) ? new \DateTime($user->employeeStatusJoining->action_date) : new \DateTime(date('Y-m-d'));
        $interval = $joiningDate->diff(new \DateTime(date('Y-m-d')));
        $salary = $user->currentPromotion->salary ?? 0;

        # Loan Violations
        if ((!empty($data['type'])) && $data['type'] == Loan::TYPE_LOAN) {
            $policyViolations = Loan::LOAN_POLICIES;
            $policyViolations = array_fill(1, count($policyViolations), false);
            $policyViolations[Loan::MAX_12_MONTH_DEDUCTION] = true;
            $policyViolations[Loan::SEVEN_BUSINESS_DAY] = true;
            $policyViolations[Loan::LOAN_RESIGNATION] = true;

            # Six Month Duration
            if ($interval->y > 0 || $interval->m > 5) {
                $policyViolations[Loan::SIX_MONTH_DURATION] = true;
            }

            # Loan Amount
            if ((!empty($data['loan_amount'])) && $data['loan_amount'] <= ($salary * 2)) {
                $policyViolations[Loan::LOAN_AMOUNT] = true;
            }

            # First Loan Unpaid
            $activeLoan = $user->activeLoan()->where('type', Loan::TYPE_LOAN)->first() ?? null;
            if (!$activeLoan) {
                $policyViolations[Loan::FIRST_LOAN_UNPAID] = true;
            }

            # Six Month Gap
            $lastPaidLoan = $user->lastPaidLoan()->where('type', Loan::TYPE_LOAN)->first() ?? null;
            if ($lastPaidLoan) {
                $lastLoanPaidDate = !empty($lastPaidLoan->instalment_paid_at) ? new \DateTime($lastPaidLoan->instalment_paid_at) : new \DateTime(date('Y-m-d'));
                $loanPaidInterval = $lastLoanPaidDate->diff(new \DateTime(date('Y-m-d')));
                if ($loanPaidInterval->y > 0 || $loanPaidInterval->m > 5) {
                    $policyViolations[Loan::SIX_MONTH_GAP] = true;
                }
            } else {
                $policyViolations[Loan::SIX_MONTH_GAP] = true;
            }

        }

        # Advance Violations
        if ((!empty($data['type'])) && $data['type'] == Loan::TYPE_ADVANCE) {
            $policyViolations = Loan::ADVANCE_POLICIES;
            $policyViolations = array_fill(8, count($policyViolations), false);
            $policyViolations[Loan::RUNNING_MONTH_DEDUCTION] = true;

            # Three Month Duration
            if ($interval->y > 0 || $interval->m > 2) {
                $policyViolations[Loan::THREE_MONTH_DURATION] = true;
            }

            # Advance Amount
            if ((!empty($data['loan_amount'])) && $data['loan_amount'] <= ($salary / 2)) {
                $policyViolations[Loan::ADVANCE_AMOUNT] = true;
            }

            # Six Month Gap
            $lastPaidAdvance = $user->lastPaidLoan()->where('type', Loan::TYPE_ADVANCE)->first() ?? null;
            if ($lastPaidAdvance) {
                $lastAdvancePaidDate = !empty($lastPaidAdvance->instalment_paid_at) ? new \DateTime($lastPaidAdvance->instalment_paid_at) : new \DateTime(date('Y-m-d'));
                $advancePaidInterval = $lastAdvancePaidDate->diff(new \DateTime(date('Y-m-d')));
                if ($advancePaidInterval->y > 0 || $advancePaidInterval->m > 5) {
                    $policyViolations[Loan::ADVANCE_SIX_MONTH_GAP] = true;
                }
            } else {
                $policyViolations[Loan::ADVANCE_SIX_MONTH_GAP] = true;
            }

            # 5th to 20th days
            $todayDate = date('d');
            if (!($todayDate > 4 && $todayDate < 21)) {
                $policyViolations[Loan::FIFTH_20TH_DAY] = true;
            }
        }

        return $policyViolations;
    }

    public function generateInstallmentTable(Request $request)
    {
        $installmentStartMonth = $request->installment_start_month;
        $installmentStartMonth = date('Y-m-d', strtotime("01-$installmentStartMonth"));
        $loanTenure = (int)$request->loan_tenure;
        $loanAmount = (int)$request->loan_amount;

        $months = \Functions::getAllMonth(false, 24);

        $error = false;
        if ((!isset($loanTenure)) || $loanTenure < 1) {
            $error = true;
        }
        if ((!isset($loanAmount)) || $loanAmount < 1) {
            $error = true;
        }

        if ($error) {
            return '<div class="alert alert-warning">Something is wrong! Instalment table could not be generated.</div>';
        }

        return view("loan.installment-table", compact("installmentStartMonth", "loanTenure", "loanAmount", "months"));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function loanApproval(Request $request, $type)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            if (!in_array($type, Loan::APPROVAL_TYPE)) {
                throw new Exception('Invalid Input');
            }

            $data = [
                $type . "_approval_status" => $request->input($type . "_status") === "approved" ? Loan::STATUS_APPROVED : Loan::STATUS_REJECTED,
                $type . "_approval_by" => $userId,
                $type . "_approved_date" => date('Y-m-d H:i:s'),
                $type . "_remarks" => $request->input("reject_reason"),
            ];

            if ($request->input($type . "_status") != "approved") {
                $data['status'] = Loan::STATUS_REJECT;
            }

            Loan::uuid($request->input("uuid"))->update($data);

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function loanPayment(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            if (empty($request->uuid)) {
                throw new Exception('Loan identity can\'t be matched!');
            }

            $loan = Loan::where([
                'status' => Loan::STATUS_PENDING,
                'hr_approval_status' => Loan::STATUS_APPROVED,
                'accounts_approval_status' => Loan::STATUS_APPROVED,
                'managerial_approval_status' => Loan::STATUS_APPROVED,
                'loan_paid_status' => 0,
                'uuid' => $request->uuid
            ])->first();

            if (empty($loan)) {
                throw new Exception('Employee Loan Not Found!!');
            }

            $loan->loan_paid_status = Loan::STATUS_APPROVED;
            $loan->loan_paid_by = $userId;
            $loan->loan_paid_date = date('Y-m-d H:i:s');
            $loan->status = Loan::STATUS_ACTIVE;
            $loan->save();

            # Update Instalment Table
            UserLoan::where('loan_id', $loan->id)->update(['status' => UserLoan::AMOUNT_APPROVED]);

            DB::commit();

            session()->flash('message', 'Loan has been paid successfully');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function loanHold(Request $request)
    {
        $userId = auth()->user()->id;
        $isResume = $request->is_resume;

        try {
            DB::beginTransaction();

            if (empty($request->uuid)) {
                throw new Exception('Loan identity can\'t be matched!');
            }

            $loanQuery = Loan::where([
                'hr_approval_status' => Loan::STATUS_APPROVED,
                'accounts_approval_status' => Loan::STATUS_APPROVED,
                'managerial_approval_status' => Loan::STATUS_APPROVED,
                'uuid' => $request->uuid
            ]);

            if ($isResume == 'Y') {
                $loanQuery->where('status', Loan::STATUS_HOLD);
            } else {
                $loanQuery->where('status', Loan::STATUS_ACTIVE);
            }

            $loan = $loanQuery->first();

            if (empty($loan)) {
                throw new Exception('Employee Loan Not Found!!');
            }

            $message = '';
            if ($isResume == 'Y') {
                $loan->status = Loan::STATUS_ACTIVE;
                $message = 'Loan has been resumed successfully';
            } else {
                $loan->status = Loan::STATUS_HOLD;
                $message = 'Loan has been hold successfully';
            }

            $loan->hold_remarks = trim(strip_tags($request->hold_remarks));
            $loan->updated_by = $userId;
            $loan->save();

            DB::commit();

            session()->flash('message', $message);
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->back();
    }
}
