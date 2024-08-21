<?php

namespace App\Http\Controllers;

use App\Http\Requests\loan\RequestLoan;
use App\Http\Requests\loan\RequestLoanForAdmin;
use App\Http\Requests\loan\RequestLoanUpdate;
use App\Models\Department;
use App\Models\Loan;
use App\Models\OfficeDivision;
use App\Models\User;
use App\Models\UserLoan;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestedLoanAdvanceController extends Controller
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
        } else {
            $itemsQuery->whereIn('status', [Loan::STATUS_ACTIVE, Loan::STATUS_PENDING, Loan::STATUS_HOLD, Loan::STATUS_PAID]);
        }

        $items = $itemsQuery->orderBy("status", 'DESC')
            ->paginate(\Functions::getPaginate());

        $officeDivisions = OfficeDivision::whereIn('id', $officeDivisionIds)->pluck('name', 'id')->toArray();
        $departments = Department::whereIn('id', $departmentIds)->pluck("name", "id")->toArray();

        return view("requested-loan-advance.index", compact("items", "officeDivisions", "departments"));
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function create()
    {
        $currentPromotion = auth()->user()->load("currentPromotion");

        $officeDivisionIds = null;
        $officeDivisions = null;
        if (auth()->user()->can('Show All Salary List')) {
            $officeDivisionIds = FilterController::getDivisionIds(true, true);
            $departmentIds = FilterController::getDepartmentIds(0, true, true);
        } else {
            $officeDivisionIds = FilterController::getDivisionIds(false, true);
            $departmentIds = FilterController::getDepartmentIds(0, false, true);
        }

        if (count($officeDivisionIds) == 0) {
            $officeDivisions = OfficeDivision::where('id', $currentPromotion->currentPromotion->office_division_id)->pluck('name', 'id')->toArray();
        } else {
            $officeDivisions = OfficeDivision::whereIn('id', $officeDivisionIds)->pluck('name', 'id')->toArray();
        }

        $today = date("y-m-d");
        $userQuery = User::select(['users.id', 'name', 'fingerprint_no'])
            ->join('promotions', function ($join) use ($today) {
                $join->on('promotions.user_id', 'users.id');
                $join->on('promotions.id', DB::raw("( select max(p.id) from promotions p where p.user_id= users.id and p.promoted_date <= '" . $today . "' limit 1)"));
            })
            ->where('status', 1);

        if (count($departmentIds) == 0) {
            $departmentIds[] = $currentPromotion->currentPromotion->department_id;
            $userQuery = $userQuery->where('users.id', Auth::id());
        } else {
            $userQuery = $userQuery->whereIn('promotions.department_id', $departmentIds);
        }
        $users = $userQuery->get();
        $departments = Department::whereIn('id', $departmentIds)->pluck("name", "id")->toArray();

        $departmentIds = json_encode($departmentIds);

        return view('requested-loan-advance.create', compact(
            'officeDivisions',
            'departments',
            'departmentIds',
            'users',
            'currentPromotion'
        ));
    }

    /**
     * @param RequestLoan $request
     * @return RedirectResponse
     */
    public function store(RequestLoanForAdmin $request)
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

            $loanController = new LoanController();
            $violations = $loanController->getPolicyViolations(['type' => $request->type, 'loan_amount' => $request->loan_amount], $request->user_id);

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

            session()->flash("message", "The Employee Loan Application has been Submitted");
            $redirect = redirect()->route("requested-loan-advance.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->input());
        }

        return $redirect;
    }

    /**
     * @param Loan $loan
     * @return Factory|View
     */
    public function edit(Loan $loan)
    {
        $currentPromotion = auth()->user()->load("currentPromotion");

        $officeDivisionIds = null;
        $officeDivisions = null;
        if (auth()->user()->can('Show All Salary List')) {
            $officeDivisionIds = FilterController::getDivisionIds(true, true);
            $departmentIds = FilterController::getDepartmentIds(0, true, true);
        } else {
            $officeDivisionIds = FilterController::getDivisionIds(false, true);
            $departmentIds = FilterController::getDepartmentIds(0, false, true);
        }

        if (count($officeDivisionIds) == 0) {
            $officeDivisions = OfficeDivision::where('id', $currentPromotion->currentPromotion->office_division_id)->pluck('name', 'id')->toArray();
        } else {
            $officeDivisions = OfficeDivision::whereIn('id', $officeDivisionIds)->pluck('name', 'id')->toArray();
        }

        $today = date("y-m-d");
        $userQuery = User::select(['users.id', 'name', 'fingerprint_no'])
            ->join('promotions', function ($join) use ($today) {
                $join->on('promotions.user_id', 'users.id');
                $join->on('promotions.id', DB::raw("( select max(p.id) from promotions p where p.user_id= users.id and p.promoted_date <= '" . $today . "' limit 1)"));
            })
            ->where('status', 1);

        if (count($departmentIds) == 0) {
            $departmentIds[] = $currentPromotion->currentPromotion->department_id;
            $userQuery = $userQuery->where('users.id', Auth::id());
        } else {
            $userQuery = $userQuery->whereIn('promotions.department_id', $departmentIds);
        }
        $users = $userQuery->get();
        $departments = Department::whereIn('id', $departmentIds)->pluck("name", "id")->toArray();

        $departmentIds = json_encode($departmentIds);

        $isApproved = $loan->status == Loan::STATUS_ACTIVE ? true : false;

        return view('requested-loan-advance.edit', compact(
            'loan',
            'isApproved',
            'officeDivisions',
            'departments',
            'departmentIds',
            'users',
            'currentPromotion'
        ));
    }

    /**
     * @param RequestLoanUpdate $request
     * @param Loan $loan
     * @return RedirectResponse
     */
    public function update(RequestLoanForAdmin $request, Loan $loan)
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

            $loanController = new LoanController();
            $violations = $loanController->getPolicyViolations(['type' => $request->type, 'loan_amount' => $request->loan_amount], $loan->user_id);

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
            $inputs['updated_by'] = Auth::id();
            Loan::where('id', $loan->id)->update($inputs);

            # Insert New Installments
            if (count($newLoanInstallments) > 0) {
                UserLoan::insert($newLoanInstallments);
            }

            # Delete Instalments
            if (count($userLoanIds) > 0) {
                UserLoan::destroy($userLoanIds);
            }

            session()->flash("message", 'Loan/Advance Successfully Updated');
            $redirect = redirect()->route("requested-loan-advance.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
    }

    /**
     * @param RequestLoanUpdate $request
     * @param Loan $loan
     * @return RedirectResponse
     */
    public function instalmentUpdate(Request $request, Loan $loan)
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

            $loanController = new LoanController();
            $violations = $loanController->getPolicyViolations(['type' => $request->type, 'loan_amount' => $request->loan_amount], $loan->user_id);

            $violations = array_filter($violations, function ($val) {
                return $val == false;
            });

            $newLoanInstallments = [];
            foreach ($months as $key => $monthDate) {
                $datePicker = \Functions::getMonthAndYearFromDatePicker($monthDate);
                $month = $datePicker["month"];
                $year = $datePicker["year"];

                if ((!empty($inputIds[$key])) && in_array($inputIds[$key], $userLoanIds)) {

                    $isPaidInstalment = UserLoan::where(['id' => $inputIds[$key], 'status' => UserLoan::DEDUCTED])->first();
                    if ($isPaidInstalment) {
                        unset($userLoanIds[$key - 1]);
                        continue;
                    }

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
                        'status' => UserLoan::AMOUNT_APPROVED,
                        'remark' => !empty($remarks[$key]) ? $remarks[$key] : '',
                        'created_by' => Auth::id(),
                        'created_at' => Carbon::now(),
                    ];
                }
            }

            # Update Loan
            if (count($violations) > 0) {
                Loan::where('id', $loan->id)->update(['policy_violations' => json_encode($violations)]);
            }

            # Insert New Installments
            if (count($newLoanInstallments) > 0) {
                UserLoan::insert($newLoanInstallments);
            }

            # Delete Instalments
            if (count($userLoanIds) > 0) {
                UserLoan::destroy($userLoanIds);
            }

            session()->flash("message", 'Loan/Advance Successfully Updated');
            $redirect = redirect()->route("requested-loan-advance.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
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
}
