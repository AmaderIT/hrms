<?php

namespace App\Http\Controllers;

use App\Exports\Report\SalarySheetBankExport;
use App\Exports\Report\SalarySheetExport;
use App\Library\GenerateSalary;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\Loan;
use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\Salary;
use App\Models\SalaryDepartment;
use App\Models\SalaryLog;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\ZKTeco\DailyAttendance;
use App\Models\UserLoan;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryController extends Controller
{
    use GenerateSalary;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $allowedStr = '/[^A-Za-z0-9\-\.]/';
    protected $paymnetModes = [];


    public function __construct()
    {
        $this->middleware('auth');
        $this->paymnetModes = User::paymentModes();
    }

    /**
     * @return Factory|View
     */
    public function prepare()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("salary.prepare", compact("data"));
    }

    /**
     * @param $date
     * @return array|RedirectResponse
     */
    protected function parcelCommission($date)
    {
        try {
            $baseUrl = env('KX_APP_URL');

            # Login
            $response = Http::post($baseUrl . '/api/v1/hrms/login', [
                "phone" => env('KX_APP_PHONE'),
                "password" => env('KX_APP_PASSWORD')
            ]);

            $bearerToken = "";
            if ($response['status']) $bearerToken = $response->json("data")["access_token"];

            # Commission
            $headers = ['Authorization' => 'Bearer ' . $bearerToken, 'Content-Type' => 'application/json'];
            $payload = ["date" => $date];
            $response = Http::withHeaders($headers)->post($baseUrl . '/api/v1/hrms/driver/commission', $payload);

            $result = [];
            if ($response["status"]) $result = $response->json("data")['driver_comission'];
            $result = collect($result);
        } catch (Exception $exception) {
            /*session()->flash('type', 'error');
            session()->flash('message', 'Error! KX API Unavailable!!');
            $result = redirect()->back();*/
            $result = [];
            $result = collect($result);
        }

        return $result;
    }

    /**
     * @return Factory|View
     */
    public function prepareSalary()
    {
        $data = array();
        $divisionIds = [];
        $departmentIds = [];

        $filter_obj = new FilterController();

        if (auth()->user()->can('Show All Salary List')) {
            $divisionIds = $filter_obj->getDivisionIds(true, true);
            $allDepartmentIds = $filter_obj->getDepartmentIds(0, true, true);
        } else {
            $divisionIds = $filter_obj->getDivisionIds(false, true);
            $allDepartmentIds = $filter_obj->getDepartmentIds(0, false, true);
        }

        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department')) || auth()->user()->can('Show All Salary List')) {
            /* $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id', $divisionIds)->get();*/
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $allDepartmentIds)->get();
            $departmentIds = [];
            foreach ($data['officeDepartments'] as $item) {
                $departmentIds[] = $item->id;
            }
        } else {
            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('department_id')->toArray();
            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('office_division_id')->toArray();
            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $departmentIds)->get();
        }

        $departmentIds_in_string = implode(',',$departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);

        return view("salary.prepare-salary", compact("data"));
    }

    /**
     * @return Factory|View
     */
    public function paySalary()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("salary.prepare-salary", compact("data"));
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return JsonResponse
     */
    public function getDepartmentByOfficeDivision(OfficeDivision $officeDivision)
    {
        return response()->json(["data" => $officeDivision->load("departments")]);
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return JsonResponse
     */
    public function getSupervisorDepartmentByOfficeDivision(OfficeDivision $officeDivision)
    {
        if (auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $departments = $officeDivision->load("departments")->departments;
        } else {
            $departmentIds = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id");
            $departments = $officeDivision->load("departments")->departments->whereIn("id", $departmentIds);
        }

        return response()->json(["data" => $departments]);
    }

    /**
     * @param $officeDivision
     * @return JsonResponse
     */
    public function getDepartmentByAllOfficeDivision($officeDivision)
    {
        if ($officeDivision === "all") {
            $officeDivision = OfficeDivision::select("id", "name")->get();
        } else {
            $officeDivision = OfficeDivision::find($officeDivision);
        }

        return response()->json(["data" => $officeDivision->load("departments")]);
    }

    /**
     * @param Department $department
     * @return JsonResponse
     */
    public function getEmployeeByDepartment(Department $department)
    {
        # Filter on collection
        $data = User::with("currentPromotion")->active()->select("id", "name", "email")->get()->filter(function ($user) use ($department) {
            if (!empty($user->currentPromotion->department_id)) {
                return $user->currentPromotion->department_id == $department->id;
            }
        });

        # Fetch data using SubQuery
        $data = User::whereHas("currentPromotion", function ($query) use ($department) {
            $query->where("id", function ($q) {
                $q->from("promotions as latestPromotion")
                    ->selectRaw("max(id)")
                    ->whereRaw("promotions.user_id = latestPromotion.user_id");
            })->where("department_id", $department->id);
        })
            ->active()
            ->select("id", "fingerprint_no", "name", "email", "status")
            ->get();

        return response()->json(array("data" => $data));
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return JsonResponse
     */
    public function getEmployeeByOfficeDivision(OfficeDivision $officeDivision)
    {
        # Fetch data using SubQuery
        $data = User::whereHas("currentPromotion", function ($query) use ($officeDivision) {
            $query->where("id", function ($q) {
                $q->from("promotions as latestPromotion")
                    ->selectRaw("max(id)")
                    ->whereRaw("promotions.user_id = latestPromotion.user_id");
            })->where("office_division_id", $officeDivision->id);
        })
            ->active()
            ->select("id", "name", "email", "status", "fingerprint_no")
            ->get();

        return response()->json(array("data" => $data));
    }

    /**
     * @return Factory|View
     */
    public function showSalary()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::get(),
            "departments" => Department::get(),
        );

        return view("salary.pay-salary", compact("data"));
    }

    /**
     * @param Request $request
     * @return Builder|Builder[]|Collection
     */
    public function filterSalary(Request $request)
    {
        try {
            $items = Salary::with("user", "officeDivision", "department");

            if (!is_null($request["monthAndYear"])) {
                $datePicker = explode("-", $request["monthAndYear"]);
                $month = (int)$datePicker[0];
                $year = (int)$datePicker[1];

                $items->where("month", $month)->where("year", $year);
            }

            if (!is_null($request["officeDivisionID"])) {
                if ($request["officeDivisionID"] !== "all") {
                    $officeDivisionID = array($request["officeDivisionID"]);
                } else {
                    $officeDivisionID = OfficeDivision::pluck("id");
                }

                $items->whereIn("office_division_id", $officeDivisionID);
            }

            if (!is_null($request["departmentID"]) and $request["officeDivisionID"] !== "all") {
                $items->where("department_id", $request["departmentID"]);
            }

            if (!is_null($request["status"])) {
                $items->where("status", $request["status"]);
            }

            $items = $items->get();
            $status = true;
        } catch (Exception $exception) {
            $items = null;
            $status = false;
        }

        return response()->json(array("status" => $status, "items" => $items));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function payNow(Request $request)
    {
        try {
            $feedback["status"] = Salary::whereIn("id", $request->input("ids"))->update(["status" => 1]);
        } catch (Exception $exception) {
            $feedback["status"] = false;
        }

        return $feedback;
    }

    /**
     * @param Salary $salary
     * @return Factory|View
     */
    public function viewPaySlip(Salary $salary)
    {
        $salary = $salary->load("user.currentPromotion.department", "user.currentPromotion.designation");
        return view('salary.generate_pay_slip', compact("salary"));
    }

    /**
     * @param Salary $salary
     * @return mixed
     */
    public function pdfDownload(Salary $salary)
    {
        $salary = $salary->load("officeDivision", "user.currentPromotion.designation");
        activity('payslip-download')->by(auth()->user())->log('Pay Slip has been exported');

        $fileName = 'salary-sheet-' . $salary->user->name . '-' . date('M-Y', mktime(null, null, null, $salary->month, null, $salary->year)) . '.pdf';
        $fileName = preg_replace($this->allowedStr, '-', $fileName);
        return PDF::loadView('salary.pay_slip_pdf', compact("salary"))->download($fileName);
    }

    /**
     * @param Salary $salary
     * @return mixed
     */
    public function pdfCashDownload(Salary $salary)
    {
        $salary = $salary->load("officeDivision", "user.currentPromotion.designation");
        activity('payslip-download')->by(auth()->user())->log('Pay Slip(Cash) has been exported');

        $fileName = 'salary-sheet-' . $salary->user->name . '-' . date('M-Y', mktime(null, null, null, $salary->month, null, $salary->year)) . '-cash' . '.pdf';
        $fileName = preg_replace($this->allowedStr, '-', $fileName);
        return PDF::loadView('salary.cash_pay_slip_pdf', compact("salary"))->download($fileName);
    }

    /**
     * @return Factory|View
     */
    public function paySlip()
    {
        $salaries = Salary::where("user_id", auth()->user()->id)->orderByDesc("id")->paginate(\Functions::getPaginate());
        return \view("salary.pay-slip", compact("salaries"));
    }

    /**
     * @param Request $request
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function status(Request $request)
    {
        try {
            $data = array(
                "officeDivisions" => OfficeDivision::select("id", "name")->get()
            );

            if ($request->has('datepicker') and $request->input('datepicker') != null) {
                $monthAndYear = \Functions::getMonthAndYearFromDatePicker($request->input("datepicker"));
                $salaryByDepartment = SalaryDepartment::with("officeDivision", "department")->where("month", $monthAndYear["month"])->where("year", $monthAndYear["year"])->orderBy('month', 'ASC')->get();
                $salary = Salary::where("month", $monthAndYear["month"])->where("year", $monthAndYear["year"])->get();
            } else {
                $salary = Salary::where("year", $request->input('year_alone'))->get();
                $salaryByDepartment = SalaryDepartment::with("officeDivision", "department")
                    ->where("year", $request->input('year_alone'))
                    ->orderByDesc('month')
                    ->get();
            }

            if ($request->has('office_division_id')) {
                $salaryByDepartment = $salaryByDepartment->where('office_division_id', $request->input('office_division_id'));
                if ($request->has('department_id')) {
                    $salaryByDepartment = $salaryByDepartment->whereIn('department_id', $request->input('department_id'));
                }
            }

            if ($request->has('department_id')) {
                $salary = $salary->whereIn('department_id', $request->input('department_id'));
            }

            $paidDepartments = $salaryByDepartment->where("status", 1)->pluck("department_id")->toArray();
            $unPaidDepartments = $salaryByDepartment->where("status", 0)->pluck("department_id")->toArray();

            $paidAmount = 0.00;
            $unpaidAmount = 0.00;

            $salary->filter(function ($query) use (&$paidAmount, &$unpaidAmount, $paidDepartments, $unPaidDepartments) {
                if (in_array($query->department_id, $paidDepartments)) $paidAmount += $query->payable_amount;
                elseif (in_array($query->department_id, $unPaidDepartments)) $unpaidAmount += $query->payable_amount;
            });

            $totalAmount = $paidAmount + $unpaidAmount;


            $salaryByDepartment = $salaryByDepartment->filter(function ($query) use ($request) {
                return $query->status == $request->input("payment_status");
            })->values();

            $salaryByDepartment = $salaryByDepartment->groupBy('month');

            $result = [
                "amount" => [
                    "total" => $totalAmount,
                    "paid" => $paidAmount,
                    "unpaid" => $unpaidAmount,
                ],
                "salaryByDepartment" => $salaryByDepartment,
            ];

            $status = true;
        } catch (Exception $exception) {
            $status = false;
            $result = [];
        }


        if (($request->has('datepicker') || $request->has('year_alone)'))) {
            if ($totalAmount == 0) {
                session()->flash("message", "The requested salary history does not exist.");
            }
        }

        return \view("salary.salary-status", compact('data', 'result'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function paySalaryByDepartment(Request $request)
    {
        try {
            $success = false;
            DB::transaction(function () use ($request, &$success) {
                if ($request->input('datepicker') and $request->input('datepicker') != null) {
                    $monthAndYear = \Functions::getMonthAndYearFromDatePicker($request->input("datepicker"));
                    Salary::whereIn("department_id", $request->input("department_id"))
                        ->where("month", $monthAndYear["month"])
                        ->where("year", $monthAndYear["year"])
                        ->update([
                            "status" => Salary::STATUS_PAID,
                            "paid_at" => now()
                        ]);

                    SalaryDepartment::whereIn("department_id", $request->input("department_id"))
                        ->where("month", $monthAndYear["month"])
                        ->where("year", $monthAndYear["year"])
                        ->update([
                            "status" => SalaryDepartment::STATUS_PAID,
                            "paid_at" => now()
                        ]);
                } else {
                    $year = $request->input('year_alone');
                    $month = $request->input('month');
                    Salary::whereIn("department_id", $request->input("department_id"))
                        ->where("month", $month)
                        ->where("year", $year)
                        ->update([
                            "status" => Salary::STATUS_PAID,
                            "paid_at" => now()
                        ]);

                    SalaryDepartment::whereIn("department_id", $request->input("department_id"))
                        ->where("month", $month)
                        ->where("year", $year)
                        ->update([
                            "status" => SalaryDepartment::STATUS_PAID,
                            "paid_at" => now()
                        ]);
                }

            });
        } catch (Exception $exception) {

        }

        session()->flash("message", "Salary payment by departments successful.");

        return back();
    }

    /**
     * @param User $user
     * @return User
     */
    public function salaryDetails(User $user)
    {
        return $user->load("salaries");
    }

    public function salaryHistory(Request $request)
    {
        try {
            $data = array(
                "officeDivisions" => OfficeDivision::select("id", "name")->get()
            );

            if ($request->has('datepicker') and $request->input('datepicker') != null) {
                $monthAndYear = \Functions::getMonthAndYearFromDatePicker($request->input("datepicker"));
                $salaryByDepartment = SalaryDepartment::with("officeDivision", "department")->where("month", $monthAndYear["month"])->where("year", $request->input('year_alone'))->orderBy('month', 'ASC')->get();
                $salary = Salary::where("month", $monthAndYear["month"])->where("year", $request->input('year_alone'))->get();
            } else {
                $salary = Salary::where("year", $request->input('year_alone'))->get();
                $salaryByDepartment = SalaryDepartment::with("officeDivision", "department")
                    ->where("year", $request->input('year_alone'))
                    ->orderByDesc('month')
                    ->get();
            }

            if ($request->has('office_division_id')) {
                $salaryByDepartment = $salaryByDepartment->where('office_division_id', $request->input('office_division_id'));
                if ($request->has('department_id')) {
                    $salaryByDepartment = $salaryByDepartment->whereIn('department_id', $request->input('department_id'));
                }
            }

            if ($request->has('department_id')) {
                $salary = $salary->whereIn('department_id', $request->input('department_id'));
            }

            $paidDepartments = $salaryByDepartment->where("status", 1)->pluck("department_id")->toArray();
            $unPaidDepartments = $salaryByDepartment->where("status", 0)->pluck("department_id")->toArray();

            $paidAmount = 0.00;
            $unpaidAmount = 0.00;

            $salary->filter(function ($query) use (&$paidAmount, &$unpaidAmount, $paidDepartments, $unPaidDepartments) {
                if (in_array($query->department_id, $paidDepartments)) $paidAmount += $query->payable_amount;
                elseif (in_array($query->department_id, $unPaidDepartments)) $unpaidAmount += $query->payable_amount;
            });

            $totalAmount = $paidAmount + $unpaidAmount;


            $salaryByDepartment = $salaryByDepartment->filter(function ($query) use ($request) {
                return $query->status == $request->input("payment_status");
            })->values();

            $salaryByDepartment = $salaryByDepartment->groupBy('month');

            $result = [
                "amount" => [
                    "total" => $totalAmount,
                    "paid" => $paidAmount,
                    "unpaid" => $unpaidAmount,
                ],
                "salaryByDepartment" => $salaryByDepartment,
            ];

            $status = true;
        } catch (Exception $exception) {
            $status = false;
            $result = [];
        }

        return \view("salary.salary-history", compact('data', 'result'));
    }

    /**
     * TODO: Fix method employeeTaxableAmountForCurrentMonth with proper parameter passing [$tobeDividedBy]
     *
     * @param User $user
     * @return float
     */
    public function taxDeduction(User $user)
    {
        $joiningMonthNumber = (int)date('m', strtotime($user->employeeStatus()->where("action_reason_id", 2)->first()->action_date));
        $tobeDividedBy = (7 - $joiningMonthNumber > 0) ? (7 - $joiningMonthNumber) : (19 - $joiningMonthNumber);

        try {
            $currentPromotion = $user->currentPromotion->load("payGrade");
            $grossSalary = $currentPromotion->salary;
            $basedOn = $currentPromotion->payGrade->based_on;
            $percentageOfBasic = $currentPromotion->payGrade->percentage_of_basic;
            $basicSalary = $grossSalary * ($percentageOfBasic / 100);

            $employeeEarnings = $user->currentPromotion->payGrade->earnings;
            $employeeDeductions = $user->currentPromotion->payGrade->deductions;

            $getEmployeeTotalEarnings = $this->getEmployeeTotalEarnings($user, $grossSalary, $basicSalary, $employeeEarnings, $basedOn, $tobeDividedBy);
            $getEmployeeTotalDeductions = $this->getEmployeeTotalDeductions($user, $grossSalary, $basicSalary, $employeeDeductions, $basedOn);

            # Overtime Amount Calculation
            # Holiday Amount Calculation
            # Leave Unpaid Amount Total

            # Taxable Amount
            $taxableAmount = $this->employeeTaxableAmountForCurrentMonth($user, $currentPromotion, $grossSalary, $getEmployeeTotalEarnings["taxEligibleAmount"], $tobeDividedBy);
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }

        return $taxableAmount;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function generateSalary(Request $request, $reGenerate = false)
    {
        app('debugbar')->disable();

        DB::beginTransaction();

        try {
            $today = (date('Y-m-d'));

            $office_division_id = $request->office_division_id;
            $department_ids = $request->department_id;
            $inputUsers = [];

            if ($office_division_id == 'all') {
                $find_division = true;
            } else {
                $find_division = false;
            }
            if (in_array("all", $department_ids)) {
                $find_department = true;
            } else {
                $find_department = false;
            }
            if (!in_array("all", $request->user_id)) {
                $inputUsers = $request->user_id;
            }

            if ($find_department) {
                if ($find_division) {
                    if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                        $filter_obj = new FilterController();
                        $divisionIds = $filter_obj->getDivisionIds();

                        $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id', $divisionIds)->get();
                        $departmentIds = [];
                        foreach ($data['officeDepartments'] as $item) {
                            $departmentIds[] = $item->id;
                        }

                        $departmentIds_in_string = implode(',', $departmentIds);

//                        $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
//                        $users = DB::select($sql);
                    } else {
                        $filter_obj = new FilterController();
                        $divisionIds = $filter_obj->getDivisionIds();
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->whereIn("office_division_id", $divisionIds)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->whereIn("office_division_id", $divisionIds)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $departmentIds_in_string = implode(',', $departmentIds);
                        $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                    }
                } else {
                    if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                        $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                        $departmentIds = [];
                        foreach ($departments as $item) {
                            $departmentIds[] = $item->id;
                        }
                        $departmentIds_in_string = implode(',', $departmentIds);
                        $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                    } else {
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $request->office_division_id)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $request->office_division_id)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $departmentIds_in_string = implode(',', $departmentIds);
                        # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                    }
                }
            } else {
                $departmentIds_in_string = implode(',', $department_ids);
                # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                $departmentIds = $department_ids;
            }

            # $departmentIds -> Department IDs as array
            # $department_ids -> Department IDs from Salary Generation Form
            # $departmentIds_in_string -> implode by comma separator of $departmentIds

            # Parse Date and Month from the DatePicker input type
            $datePicker = \Functions::getMonthAndYearFromDatePicker($request->input("month_and_year"));
            $month = $datePicker["month"];
            $year = $datePicker["year"];
            $monthYear = $year . '-' . sprintf("%02s", $month) . '-%';

            # Remove Departments which already generates salary
            /*$generatedDepartments = SalaryDepartment::where("month", $month)
                ->where("year", $year)
                ->whereIn("department_id", $departmentIds)
                ->pluck("department_id");


            $salaryToBeGeneratedForDepartments = collect($departmentIds)->reject(function ($id) use ($generatedDepartments) {
                if (in_array($id, $generatedDepartments->toArray())) return $id;
            })->toArray();*/


            $parcelCommission = $this->parcelCommission($year . '-' . $month . '-1');

            /*foreach ($salaryToBeGeneratedForDepartments as $departmentId) {*/
            foreach ($departmentIds as $departmentId) {
                $totalPayableAmountOfDepartment = 0;
                $salaryDepartment = null;

                $sql = "SELECT users.id
                        FROM
                        `users`
                        INNER JOIN
                        promotions as prm
                        ON
                        prm.user_id = users.id
                        AND
                        prm.id = (
                            SELECT MAX( pm.id ) FROM `promotions` AS pm
                                where pm.user_id = users.id
                            )
                        WHERE `users`.`id` IN
                            ( SELECT `promotions`.`user_id` FROM `promotions`
                                WHERE `promotions`.`department_id` IN ( $departmentId )
                                AND `promotions`.`id` IN
                                    ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )
                            )
                          AND users.id IN ( SELECT user_id FROM `daily_attendances`
                          WHERE `daily_attendances`.`user_id` = users.id
                            AND `date` LIKE '$monthYear' AND (`daily_attendances`.present_count > 0 OR `daily_attendances`.leave_count > 0) )
                        GROUP BY `users`.id;
                        /*AND
                        `users`.`status` = 1*/";


                $users = DB::select($sql);
                $userIds = collect($users)->pluck("id");

                # Change userIds if input user exist
                if(count($inputUsers) > 0){
                    $departmentWiseInputUsers = [];
                    foreach ($userIds as $departmentUserId) {
                        if(in_array($departmentUserId, $inputUsers)){
                            $departmentWiseInputUsers[] = $departmentUserId;
                        }
                    }
                    $userIds = $departmentWiseInputUsers;
                }

                if(count($userIds) == 0) continue;

                $salaries = [];

                $totalDayInTheMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                # Check meta data before generating the salary
                #$salaryDepartment = SalaryDepartment::where("department_id", $departmentId)->where("month", $month)->where("year", $year)->first();

                # Generate Salary whether not generated yet
                /*if (!isset($salaryDepartment)) {*/

                    $employees = User::with("currentPromotion.designation", "currentPromotion.payGrade", "lateAllow")
                        ->whereIn("id", $userIds)
                        /*->active()*/ ->get();

                    $employeeIds = $employees->pluck("id");


                    if ($employeeIds->count() == 0) continue;

                    $employeeIds = $employeeIds->count() > 0 ? implode(", ", $employeeIds->all()) : "";

                    $sql = "SELECT
                                `user_id`, `emp_code`, `date`,

                                COUNT(`date`) AS total_days,

                                SUM(CASE WHEN `is_weekly_holiday` <> 1 AND `is_public_holiday` <> 1
                                    THEN `present_count` ELSE 0 END) as regular_duty,

                                SUM(CASE WHEN `present_count` = 1  AND `is_weekly_holiday` = 1 AND `is_public_holiday` <> 1
                                     THEN `is_weekly_holiday` ELSE 0 END) weekly_holiday_duty,

                                SUM(CASE WHEN `present_count` = 1  AND `is_public_holiday` = 1
                                     THEN `is_public_holiday` ELSE 0 END) official_holiday_duty,

                                SUM(`leave_count`) as total_leave,

                                (SUM(`overtime_min`) / 60) as overtime_hours,

                                SUM(`is_weekly_holiday`) as weekend_holiday,

                                SUM(`is_relax_day`) as relax_day,

                                SUM(`is_public_holiday`) as public_holiday,

                                SUM(`is_late_final`) as total_late,

                                SUM(`absent_count`) as total_absent,

                                ROUND((SUM(`working_min`) / 60 / SUM(`present_count`)), 2) as working_hours

                            FROM `daily_attendances` WHERE `user_id` IN($employeeIds) AND `date` LIKE '$monthYear'

                            GROUP BY `user_id`
                            ;
                    ";
                    $reports = DB::select($sql);
                    $reports = collect($reports);

                    # Check user salary already been generated or not
                    $salaryGeneratedUsers = Salary::where(['month' => $month, 'year' => $year])->pluck('user_id')->toArray();
                    $departmentSalaryGeneration = false;

                    foreach ($employees as $employee) {//if($employee->fingerprint_no != 950) continue;

                        $oldReports = $reports;

                        #Check the employee is eligible or not for this month salary by joining date. I need to be optimized by employee status
                        $lastDateOfSalaryMonth = date("Y-m-t", strtotime(date("$year-$month-10")));
                        $firstDateOfSalaryMonth = date("Y-m-d", strtotime(date("$year-$month-01")));
                        $joiningDate = date('Y-m-d', strtotime($employee->employeeStatusJoining->action_date));

                        if ($joiningDate > $lastDateOfSalaryMonth) {
                            continue;
                        }

                        if (in_array($employee->id, $salaryGeneratedUsers)) {
                            continue;
                        }

                        /***
                         * Day count for...
                         * Those New employee who joined after first day of salary month
                         * Those employee who are switched the company before last date of salary month
                         ***/
                        $firstDateForCountSalaryDate = $firstDateOfSalaryMonth;
                        $lastDateForCountSalaryDate = $lastDateOfSalaryMonth;
                        $countTotalDaysForSpecialCase = false;
                        if (($joiningDate > $firstDateOfSalaryMonth) && ($joiningDate <= $lastDateOfSalaryMonth)) {
                            $firstDateForCountSalaryDate = $joiningDate;
                            $countTotalDaysForSpecialCase = true;
                        }
                        if ($employee->currentPromotion->type == Promotion::TYPE_TERMINATED) {
                            $terminationDate = date('Y-m-d', strtotime($employee->currentPromotion->promoted_date));
                            if (($terminationDate >= $firstDateOfSalaryMonth) && ($terminationDate < $lastDateOfSalaryMonth)) {
                                $lastDateForCountSalaryDate = $terminationDate;
                                $countTotalDaysForSpecialCase = true;
                            }
                        }

                        if ($countTotalDaysForSpecialCase) {
                            $specialSql = "SELECT
                                `user_id`, `emp_code`, `date`,

                                COUNT(`date`) AS total_days,

                                SUM(CASE WHEN `is_weekly_holiday` <> 1 AND `is_public_holiday` <> 1
                                    THEN `present_count` ELSE 0 END) as regular_duty,

                                SUM(CASE WHEN `present_count` = 1  AND `is_weekly_holiday` = 1 AND `is_public_holiday` <> 1
                                     THEN `is_weekly_holiday` ELSE 0 END) weekly_holiday_duty,

                                SUM(CASE WHEN `present_count` = 1  AND `is_public_holiday` = 1
                                     THEN `is_public_holiday` ELSE 0 END) official_holiday_duty,

                                SUM(`leave_count`) as total_leave,

                                (SUM(`overtime_min`) / 60) as overtime_hours,

                                SUM(`is_weekly_holiday`) as weekend_holiday,

                                SUM(`is_relax_day`) as relax_day,

                                SUM(`is_public_holiday`) as public_holiday,

                                SUM(`is_late_final`) as total_late,

                                SUM(`absent_count`) as total_absent,

                                ROUND((SUM(`working_min`) / 60 / SUM(`present_count`)), 2) as working_hours

                            FROM `daily_attendances` WHERE `user_id` IN($employee->id) AND `date` BETWEEN '$firstDateForCountSalaryDate' AND '$lastDateForCountSalaryDate'

                            GROUP BY `user_id`;";

                            $specialReports = DB::select($specialSql);
                            $specialReports = collect($specialReports);
                            $reports = $specialReports;
                        }

                        $currentPromotion = $employee->currentPromotion->load("payGrade");
                        $grossSalaryOriginal = $currentPromotion->salary;

                        # START::Define gross
                        $totalDays = $reports->where("user_id", $employee->id)->first()->total_days ?? 0;

                        # Get total days for special case
                        if ($countTotalDaysForSpecialCase) {
                            $totalDays = DailyAttendance::where('user_id', $employee->id)->whereBetween('date', [$firstDateForCountSalaryDate, $lastDateForCountSalaryDate])->count();
                        }

                        $grossPercentageToBePaid = ($totalDays / $totalDayInTheMonth);
                        $grossSalary = $grossSalaryOriginal * $grossPercentageToBePaid;
                        # END::Define gross

                        $unitSalary = $totalDays > 0 ? ($grossSalary / $totalDays) : 0;

                        $basedOn = $currentPromotion->payGrade->based_on;
                        $percentageOfBasic = $currentPromotion->payGrade->percentage_of_basic;
                        $basicSalary = $grossSalary * ($percentageOfBasic / 100);
                        $basicSalaryOriginal = $grossSalaryOriginal * ($percentageOfBasic / 100);

                        $employeeEarnings = $employee->currentPromotion->payGrade->earnings;
                        $employeeDeductions = $employee->currentPromotion->payGrade->deductions;

                        # Start::Get next tax year closing time
                        $taxYearClosing = '';
                        $currentMonth = date('m', strtotime("$year-$month-01"));
                        if ((int)$currentMonth > 6) $nextTaxYear = date('Y', strtotime("$year-$month-01")) + 1;
                        else $nextTaxYear = date('Y', strtotime("$year-$month-01"));

                        $taxYearClosing = $nextTaxYear . '-06-30';

                        $joiningDate = $employee->employeeStatus()->where("action_reason_id", 2)->orderBy('id', 'DESC')->first()->action_date;

                        /**
                         * Check whether joining this month or not.
                         * If salary generation month and year is the joining month ,
                         * then deduct no tax for the month of that specific employee,
                         * otherwise deduct tax until tax year closing.
                         */
                        $joiningYear = (int)date('Y', strtotime($joiningDate));
                        $joiningMonth = (int)date('m', strtotime($joiningDate));

                        $joiningThisMonth = 0;
                        if ($month === $joiningMonth and $year === $joiningYear) $joiningThisMonth = 1;
                        //

                        $origin = new \DateTime($joiningDate);
                        $target = new \DateTime($taxYearClosing);
                        $interval = $origin->diff($target);
                        /*if ($interval->format("%y") > 0) $tobeDividedBy = 12;
                        else $tobeDividedBy = $interval->format("%m");*/
                        if ($interval->format("%y") > 0) {
                            $tobeDividedBy = 12;
                        } else {
                            $tobeDividedBy = $interval->format("%m");
                            $tobeDividedBy += 1;
                        }
                        # Close::Get next tax year closing time

                        $getEmployeeTotalEarnings = $this->getEmployeeTotalEarnings($employee, $grossSalary, $basicSalary, $employeeEarnings, $basedOn, $tobeDividedBy);
                        $getEmployeeTotalDeductions = $this->getEmployeeTotalDeductions($employee, $grossSalary, $basicSalary, $employeeDeductions, $basedOn);

                        $getEmployeeTotalOriginalEarnings = $this->getEmployeeTotalEarnings($employee, $grossSalaryOriginal, $basicSalaryOriginal, $employeeEarnings, $basedOn, $tobeDividedBy);
                        $houseRentOriginal = collect($getEmployeeTotalOriginalEarnings["earnings"])->where("name", "House Rent")->first()['amount'];
                        $medicalAllowanceOriginal = collect($getEmployeeTotalOriginalEarnings["earnings"])->where("name", "Medical Allowance")->first()['amount'];
                        $conveyanceOriginal = collect($getEmployeeTotalOriginalEarnings["earnings"])->where("name", "Conveyance")->first()['amount'];

                        # Taxable Amount
                        /* This condition commented bcoz of employee joining month tax will be count
                         * if ($joiningThisMonth === 1) $taxableAmount = 0;
                        else */
                        $taxableAmount = $this->employeeTaxableAmountForCurrentMonth($employee, $currentPromotion, $grossSalaryOriginal, $getEmployeeTotalOriginalEarnings["taxEligibleAmount"], $tobeDividedBy);
                        //
                        $houseRent = collect($getEmployeeTotalEarnings["earnings"])->where("name", "House Rent")->first()['amount'];
                        $medicalAllowance = collect($getEmployeeTotalEarnings["earnings"])->where("name", "Medical Allowance")->first()['amount'];
                        $conveyance = collect($getEmployeeTotalEarnings["earnings"])->where("name", "Conveyance")->first()['amount'];
                        $regular = $reports->where("user_id", $employee->id)->first()->regular_duty ?? 0;

                        $weekendHolidayDuty = $reports->where("user_id", $employee->id)->first()->weekly_holiday_duty ?? 0;
                        $officialHolidayDuty = $reports->where("user_id", $employee->id)->first()->official_holiday_duty ?? 0;
                        $leave = $reports->where("user_id", $employee->id)->first()->total_leave ?? 0;
                        $overtimeHours = $reports->where("user_id", $employee->id)->first()->overtime_hours ?? 0;
                        $weekendHoliday = $reports->where("user_id", $employee->id)->first()->weekend_holiday ?? 0;
                        $relaxDay = $reports->where("user_id", $employee->id)->first()->relax_day ?? 0;
                        $officialHoliday = $reports->where("user_id", $employee->id)->first()->public_holiday ?? 0;
                        $totalLate = $reports->where("user_id", $employee->id)->first()->total_late ?? 0;
                        $totalAbsent = $reports->where("user_id", $employee->id)->first()->total_absent ?? 0;

                        # Salary to be deducted for absent
                        $absentSalaryDeduction = 0;
                        if ($request->has("absent_deduction")) {
                            $absentSalaryDeduction = $unitSalary * $totalAbsent;
                        }

                        # Salary to be deducted for late
                        $lateLeaveDeduction = "[]";
                        $lateSalaryDeduction = 0;
                        $casualLeave = 0;
                        $earnLeave = 0;
                        if ($request->has("late_deduction")) {
                            $lateDeductionAmount = $this->getEmployeeLateDeductionAmount($employee, $totalLate, $unitSalary, $year);
                            $lateLeaveDeduction = $lateDeductionAmount["leave"];
                            $casualLeave = collect(json_decode($lateLeaveDeduction))->where("leave_type_id", 5)->first()->to_be_deducted ?? 0;
                            $earnLeave = collect(json_decode($lateLeaveDeduction))->where("leave_type_id", 3)->first()->to_be_deducted ?? 0;
                            $lateSalaryDeduction = $lateDeductionAmount["salary"];
                        }

                        # Overtime Amount Calculation
                        if ($request->has("overtime")) {
                            $overtimePay = $this->getEmployeeOvertimeAmount($employee, $overtimeHours, $grossSalaryOriginal, $basicSalaryOriginal) ?? 0;
                        } else {
                            $overtimePay = 0;
                        }

                        # Holiday Amount Calculation
                        $holidayAmount = $this->getEmployeeHolidayAmount($employee, $grossSalaryOriginal, $basicSalaryOriginal, $weekendHolidayDuty, $officialHolidayDuty, $month, $year);
                        $weekendHolidayPay = json_decode($holidayAmount["holiday_amount"])->weekly ?? 0;
                        $publicHolidayPay = json_decode($holidayAmount["holiday_amount"])->organizational ?? 0;
                        $holidayPay = $weekendHolidayPay + $publicHolidayPay;

                        # Loan / Advance Salary
                        $loanAdvanceSalaryAmount = $this->getEmployeeLoanAdvanceSalaryAmount($employee, $month, $year);
                        $loan = $loanAdvanceSalaryAmount["loan"];
                        $advance = $loanAdvanceSalaryAmount["advance"];

                        $parcelCharge = 0;
                        $deliveryBonus = 0;
                        $distanceBonus = 0;
                        $driver = $parcelCommission->where("fingerprint_no", $employee->fingerprint_no)->first();
                        if (isset($driver)) {
                            $parcelCharge = $driver['total_pickup_commission'];
                            $deliveryBonus = $driver['total_cancel_commission'] + $driver['total_delivered_commission'];
                            $distanceBonus = $driver['total_distance_commission'];
                        }

                        #Fix minimum gross salary issue for temporary. Need to change it into permanent solution
                        if ($taxableAmount <= 0 && $grossSalaryOriginal >= 40000) {
                            $taxableAmount = (5000 / 12);
                        }

                        $totalPayable = (double)$basicSalary + (double)$houseRent + (double)$medicalAllowance + (double)$conveyance + (double)$holidayPay + (double)$overtimePay + (double)$parcelCharge + (double)$deliveryBonus + (double)$distanceBonus;
                        $netPayable = (double)$totalPayable - (double)$advance - (double)$loan - (double)$absentSalaryDeduction - (double)$lateSalaryDeduction - (double)$taxableAmount;

                        $totalPayableAmountOfDepartment += $netPayable;

                        if(!$departmentSalaryGeneration){
                            $salaryDepartment = SalaryDepartment::create([
                                "office_division_id" => $employee->currentPromotion->office_division_id,
                                "department_id" => $employee->currentPromotion->department_id,
                                "month" => $month,
                                "year" => $year,
                                "status" => SalaryDepartment::STATUS_UNPAID,
                                "total_payable_amount" => $totalPayableAmountOfDepartment,
                                "prepared_by" => auth()->user()->id,
                                "prepared_date" => date('Y-m-d H:i:s'),
                            ]);

                            $departmentSalaryGeneration = true;
                        }

                        if($salaryDepartment){
                            $salary = [
                                "salary_department_id" => $salaryDepartment->id,
                                "uuid" => \Functions::getNewUuid(),
                                "user_id" => $employee->id,
                                "office_division_id" => $employee->currentPromotion->office_division_id,
                                "department_id" => $employee->currentPromotion->department_id,
                                "designation_id" => $employee->currentPromotion->designation_id,
                                "pay_grade_id" => $employee->currentPromotion->pay_grade_id,
                                "tax_id" => $employee->currentPromotion->payGrade->tax_id,
                                "gross" => $grossSalaryOriginal,
                                "basic" => $basicSalaryOriginal,
                                "house_rent" => $houseRentOriginal,
                                "medical_allowance" => $medicalAllowanceOriginal,
                                "conveyance" => $conveyanceOriginal,
                                "this_month_earnings" => json_encode($getEmployeeTotalEarnings),
                                "earnings" => json_encode($getEmployeeTotalOriginalEarnings["earnings"]),
                                "cash_earnings" => json_encode($getEmployeeTotalOriginalEarnings["cashEarnings"]),
                                "total_earning" => $getEmployeeTotalOriginalEarnings["totalAmount"],
                                "total_cash_earning" => $getEmployeeTotalOriginalEarnings["totalCashAmount"],
                                "regular_duty" => $regular,
                                "weekend_holiday_duty" => $weekendHolidayDuty,
                                "official_holiday_duty" => $officialHolidayDuty,
                                "leave_days" => $leave,
                                "absent_days" => $totalAbsent,
                                "absent_salary_deduction" => $absentSalaryDeduction,
                                "overtime_hours" => $overtimeHours,
                                "weekend_holiday_days" => $weekendHoliday,
                                "relax_day_days" => $relaxDay,
                                "official_holiday_days" => $officialHoliday,
                                "deductions" => json_encode($getEmployeeTotalDeductions["deductions"]),
                                "total_deduction" => $getEmployeeTotalDeductions["totalAmount"],
                                "overtime_amount" => $overtimePay,
                                "holiday_amount" => $holidayPay ?? 0,
                                "parcel_charge" => $parcelCharge,
                                "delivery_bonus" => $deliveryBonus,
                                "distance_bonus" => $distanceBonus,
                                // leave_unpaid_amount
                                "taxable_amount" => $taxableAmount,
                                "payable_tax_amount" => $taxableAmount,
                                "advance" => $advance,
                                "casual_leave" => $casualLeave ?? 0,
                                "earn_leave" => $earnLeave ?? 0,
                                "loan" => $loan,
                                "late" => $totalLate,
                                "remaining_tax_opening_balance" => 0,
                                "payable_amount" => $totalPayable ?? 0,
                                "net_payable_amount" => $netPayable ?? 0,
                                "attendance_hours" => $reports->where("user_id", $employee->id)->first()->working_hours ?? 0,
                                "late_leave_deduction" => $lateLeaveDeduction,
                                "late_salary_deduction" => $lateSalaryDeduction,
                                "status" => 0,
                                "month" => $month,
                                "year" => $year,
                                "payment_mode" => $this->paymnetModes[$employee->payment_mode] ?? 1,
                                "remarks" => "",
                                "created_at" => now(),
                                "updated_at" => now(),
                            ];
                            array_push($salaries, $salary);
                        }

                        $reports = $oldReports;
                    }

                    # Insert a meta data to the corresponding table related to salary
                    if (count($salaries) > 0) {
                        /*$salaryDepartment = SalaryDepartment::create([
                            "office_division_id" => $employee->currentPromotion->office_division_id,
                            "department_id" => $employee->currentPromotion->department_id,
                            "month" => $month,
                            "year" => $year,
                            "status" => SalaryDepartment::STATUS_UNPAID,
                            "total_payable_amount" => $totalPayableAmountOfDepartment,
                            "prepared_by" => auth()->user()->id,
                            "prepared_date" => date('Y-m-d H:i:s'),
                        ]);*/

                        Salary::insert($salaries);

                        #Insert Salary Log
                        $action = ($reGenerate) ? SalaryLog::SALARY_RE_GENERATED : SalaryLog::SALARY_GENERATED;
                        SalaryLog::generateSalaryLog($salaryDepartment->uuid, $action, auth()->user()->id);
                    }
                /*} else {
                    DB::commit();
                    session()->flash('type', 'error');
                    session()->flash('message', 'Sorry! Data already exists!!');
                    return redirect()->route("salary.viewSalary");
                }*/
            }

            DB::commit();
            session()->flash('message', 'Salary Generated Successfully');
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception->getMessage());

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->route("salary.viewSalary");
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function viewSalary()
    {
        # Define Latest Salary Month and Year
        $latest = $this->getLatestSalaryGenerationMonthYear();

        # Define Corresponding Office Division and Department of the Logged In Employee for proper data access
        $data = array();
        $filter_obj = new FilterController();

        $divisionIds = null;
        $allDepartmentIds = null;
        if (auth()->user()->can('Show All Salary List')) {
            $divisionIds = $filter_obj->getDivisionIds(true, true);
            $allDepartmentIds = $filter_obj->getDepartmentIds(0, true, true);
        } else {
            $divisionIds = $filter_obj->getDivisionIds(false, true);
            $allDepartmentIds = $filter_obj->getDepartmentIds(0, false, true);
        }

        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department')) || (auth()->user()->can('Show All Salary List'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $allDepartmentIds)->get();

            $departmentIds = [];
            foreach ($data['officeDepartments'] as $item) {
                $departmentIds[] = $item->id;
            }
        } else {
            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('department_id')->toArray();
            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('office_division_id')->toArray();
            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $departmentIds)->get();
        }

        if (\request()->has("office_division_id") && ((auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) || auth()->user()->can('Show All Salary List'))) {
            $data['officeDepartments'] = Department::select("id", "name")->where('office_division_id', \request()->get("office_division_id"))->get();
        }

        # START
        $today = (date('Y-m-d'));

        $office_division_id = \request()->get('office_division_id');
        $department_ids = \request()->get('department_id');

        if ($office_division_id == 'all') {
            $find_division = true;
        } else {
            $find_division = false;
        }
        if (!is_null($department_ids) and in_array("all", $department_ids)) {
            $find_department = true;
        } else {
            $find_department = false;
        }

        if ($find_department) {
            if ($find_division) {
                if ((auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) || auth()->user()->can('Show All Salary List')) {
                    $filter_obj = new FilterController();

                    $divisionIds = null;
                    if (auth()->user()->can('Show All Salary List')) {
                        $divisionIds = $filter_obj->getDivisionIds(true, true);
                    } else {
                        $divisionIds = $filter_obj->getDivisionIds(false, true);
                    }

                    $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id', $divisionIds)->get();
                    $departmentIds = [];
                    foreach ($data['officeDepartments'] as $item) {
                        $departmentIds[] = $item->id;
                    }

                    $departmentIds_in_string = implode(',', $departmentIds);

//                        $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
//                        $users = DB::select($sql);
                } else {
                    $filter_obj = new FilterController();
                    $divisionIds = $filter_obj->getDivisionIds(false, true);
                    $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn("office_division_id", $divisionIds)
                        ->pluck('department_id')->toArray();
                    $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn("office_division_id", $divisionIds)
                        ->pluck('office_division_id')->toArray();
                    $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                    $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                    $departmentIds_in_string = implode(',', $departmentIds);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            } else {
                if ((auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) || auth()->user()->can('Show All Salary List')) {
                    $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                    $departmentIds = [];
                    foreach ($departments as $item) {
                        $departmentIds[] = $item->id;
                    }
                    $departmentIds_in_string = implode(',', $departmentIds);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                } else {
                    $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->where("office_division_id", '=', \request()->office_division_id)
                        ->pluck('department_id')->toArray();
                    $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->where("office_division_id", '=', \request()->office_division_id)
                        ->pluck('office_division_id')->toArray();
                    $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                    $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                    $departmentIds_in_string = implode(',', $departmentIds);
                    # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }
        } else {
//            $departmentIds_in_string = implode(',',$department_ids);
            $departmentIds_in_string = implode(',', $data['officeDepartments']->all());
            # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
            $departmentIds = $department_ids;
        }

        # $departmentIds -> Department IDs as array
        # $department_ids -> Department IDs from Salary Generation Form
        # $departmentIds_in_string -> implode by comma separator of $departmentIds
        # END

        # Define Items to View Salary
        $items = SalaryDepartment::with(
            "officeDivision", "department", "preparedBy", "divisionalApprovalBy",
            "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $isAdmin = auth()->user()->isAdmin();
        $isAccountant = false;
        $accountsDepartmentsIds = [];
        if (auth()->user()->can('Salary Accounts Approval') && !$isAdmin) {
            $isAccountant = true;
            $accountsDepartmentsIds = $filter_obj->getDepartmentIds();
            /*$items = $items->where('hr_approval_status', 1);*/
        }

        if (auth()->user()->can('Salary Managerial Approval') && !$isAdmin) {
            $items = $items->where(['hr_approval_status' => 1, 'accounts_approval_status' => 1]);
        }

        if (\request()->has('month_and_year') and \request()->input('month_and_year') != null) {
            $monthAndYear = \Functions::getMonthAndYearFromDatePicker(\request()->get("month_and_year"));
            $items = $items->where("month", $monthAndYear["month"])
                ->where("year", $monthAndYear["year"]);
        } else {
            $items = $items->where("month", $latest["month"])->where("year", $latest["year"])->orderByDesc("id");
        }

        if (\request()->has('office_division_id') and in_array(\request()->get("office_division_id"), $divisionIds)) {
            $items = $items->whereIn("office_division_id", [\request()->get("office_division_id")]);
        } else {
            $items = $items->whereIn("office_division_id", $data["officeDivisions"]->pluck("id"));
        }

        if (\request()->has('department_id')) {
            $items = $items->whereIn("department_id", array_intersect($departmentIds, \request()->get("department_id")));
        } else {
            $items = $items->whereIn("department_id", $data["officeDepartments"]->pluck("id"));
        }

        if (\request()->has('payment_status')) {
            $items = $items->where("status", \request()->get("payment_status"));
        }

        $items = $items->get();

        $data = array(
            "officeDivisions" => $data['officeDivisions'],
            "departments" => $data['officeDepartments'],
        );

        return \view("salary.view-salary", compact('data', 'items', 'latest', 'isAccountant', 'accountsDepartmentsIds'));
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function viewAllSalary()
    {
        $latest = $this->getLatestSalaryGenerationMonthYear();

        $items = SalaryDepartment::with(
            "officeDivision", "department", "preparedBy", "divisionalApprovalBy",
            "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        if (\request()->has('month_and_year') and \request()->input('month_and_year') != null) {
            $monthAndYear = \Functions::getMonthAndYearFromDatePicker(\request()->get("month_and_year"));
            $items = $items->where("month", $monthAndYear["month"])
                ->where("year", $monthAndYear["year"]);
        } else {
            $items = $items->where("month", $latest["month"])->where("year", $latest["year"])->orderByDesc("id");
        }

        if (\request()->has('office_division_id')) $items = $items->where("office_division_id", \request()->get("office_division_id"));

        if (\request()->has('department_id')) $items = $items->whereIn("department_id", \request()->get("department_id"));

        if (\request()->has('payment_status')) $items = $items->where("status", \request()->get("payment_status"));

        $items = $items->get();

        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::where("office_division_id", \request()->get("office_division_id"))->get(),
        );

        return \view("salary.view-salary", compact('data', 'items', 'latest'));
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function salaryReportFilter()
    {
        $data = array(
            "officeDivisions" => [],
            "officeDepartments" => [],
            "employees" => []
        );
        if (auth()->user()->can('Show All Salary List')) {
            $data['officeDivisions'] = OfficeDivision::select("id", "name")->get();
            $data['officeDepartments'] = Department::select("id", "name")->get();
            $data['employees'] = User::select("id", "name", "fingerprint_no")->active()->get();

        } elseif (auth()->user()->hasRole([User::ROLE_SUPERVISOR])) {
            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->get();
            $divisions = [];
            $departmentIds = [];
            foreach ($permit_info as $info) {
                $divisions[] = $info->office_division_id;
                $departmentIds[] = $info->department_id;
            }
            if ($divisions) {
                $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisions)->get();
                $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $departmentIds)->get();
                $departmentIds_in_string = implode(',', $departmentIds);
                $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        } elseif (auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $departmentIds = $this->getDepartmentSupervisorIds();
            $depts = Department::select("id", "name", "office_division_id")->whereIn('id', $departmentIds)->get();
            $divisions = [];
            foreach ($depts as $info) {
                $divisions[$info->office_division_id] = $info->office_division_id;
            }
            if ($divisions) {
                $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisions)->get();
                $data['officeDepartments'] = $depts;
                $departmentIds_in_string = implode(',', $departmentIds);
                $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        } else {
            $data['officeDivisions'] = OfficeDivision::select("id", "name")->get();
            $data['officeDepartments'] = Department::select("id", "name")->get();
            $data['employees'] = User::select("id", "name", "fingerprint_no")->active()->get();
        }

        return view("salary.report-filter", compact("data"));
    }

    /**
     * @param Request $request
     * @return Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function generateSalaryReportView(Request $request)
    {
        try {
            $office_division_id = $request->input('office_division_id');
            $month_year = $request->input('datepicker');
            $department_ids = $request->input('department_id');
            $user_ids = $request->input('user_id');
            $filter = [];
            $filter['office_division_id'] = $office_division_id;
            $filter['department_id'] = $department_ids;
            $filter['user_id'] = $user_ids;
            $filter['datepicker'] = $month_year;
            $month_year = explode("-", $month_year);
            $month = $month_year[0];
            $year = $month_year[1];
            $dateObj = \DateTime::createFromFormat('!m', $month);
            $monthName = $dateObj->format('F');
            $monthAndYear = $monthName . ", " . $year;
            $YearAndmonth = $year . "-" . $month;
            $date = $year . "-" . $month . "-" . "01";
            $firstDateOfMonth = $date;
            $date = new \DateTime($date);
            $date = $date->format('t');
            $lastDayOfMonth = (int)$date;
            $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;
            if ($office_division_id == 'all') {
                $find_division = true;
            } else {
                $find_division = false;
            }
            if (in_array("all", $department_ids)) {
                $find_department = true;
            } else {
                $find_department = false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee = true;
            } else {
                $find_employee = false;
            }
            if ($find_employee) {
                if ($find_department) {
                    if (auth()->user()->hasRole([User::ROLE_SUPERVISOR])) {
                        if ($find_division) {
                            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->get();
                            $departmentIds = [];
                            foreach ($permit_info as $info) {
                                $departmentIds[] = $info->department_id;
                            }
                            $departmentIds_in_string = implode(',', $departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        } else {
                            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->where("office_division_id", $office_division_id)->active()->get();
                            $departmentIds = [];
                            foreach ($permit_info as $info) {
                                $departmentIds[] = $info->department_id;
                            }
                            $departmentIds_in_string = implode(',', $departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    } elseif (auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
                        $departmentIds = $this->getDepartmentSupervisorIds();
                        if ($find_division) {
                            $departmentIds_in_string = implode(',', $departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        } else {
                            $depts = Department::select("id", "name", "office_division_id")->whereIn('id', $departmentIds)->get();
                            $departmentIds = [];
                            foreach ($depts as $info) {
                                if ($info->office_division_id == $office_division_id) {
                                    $departmentIds[] = $info->id;
                                }
                            }
                            $departmentIds_in_string = implode(',', $departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    } else {
                        if ($find_division) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        } else {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE `users`.`id` IN ( SELECT `promotions`.`user_id` FROM `promotions` WHERE `promotions`.`office_division_id` IN ( $office_division_id ) AND `promotions`.`id` IN ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )) AND `users`.`status`=1";
                            $users = DB::select($sql);
                        }
                    }
                } else {
                    $departmentIds_in_string = implode(',', $department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            } else {
                $user_ids_in_string = implode(',', $user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }
            if (!isset($user_ids_in_string)) {
                $user_ids = [];
                foreach ($users as $key => $user) {
                    if ($key == 0) {
                        $user_ids_in_string = $user->id;
                    } else {
                        $user_ids_in_string .= ',' . $user->id;
                    }
                    $user_ids[] = $user->id;
                }
            }

            $salaries = Salary::whereIn("user_id", $user_ids)
                ->where("month", (int)$month)
                ->where("year", (int)$year)
                ->orderBy("id")
                ->get();

            $filter_type = $request->filter_type;

            if ($salaries->count() > 0) {
                $redirect = view('salary.report-view', compact('salaries', 'filter_type'));
            } else {
                $redirect = redirect()->back();
                session()->flash("type", "error");
                session()->flash("message", "Sorry! No Salary Data Found!!");
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @return mixed
     */
    protected function getLatestSalaryGenerationMonthYear()
    {
        $sql = "SELECT MAX(month) as latest_month, year as latest_year
                    FROM salary_department
                    WHERE
                    year = (
                        SELECT MAX(year) FROM salary_department
                    );
                ";
        $latestData = DB::select($sql);

        $latest['month'] = collect($latestData)->first()->latest_month;
        $latest['year'] = collect($latestData)->first()->latest_year;

        return $latest;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function paySalaryToDepartment(Request $request)
    {
        try {
            DB::beginTransaction();

            $salaryDepartment = SalaryDepartment::uuid($request->input("salary_department_id"))->first();

            $salaryDepartment->update([
                "status" => SalaryDepartment::STATUS_PAID,
                "paid_at" => now()
            ]);

            #Insert Salary Log
            SalaryLog::generateSalaryLog($request->input("salary_department_id"), SalaryLog::SALARY_PAID, auth()->id());

            $salaryQuery = Salary::where("office_division_id", $salaryDepartment->office_division_id)
                ->where("department_id", $salaryDepartment->department_id)
                ->where("month", $salaryDepartment->month)
                ->where("year", $salaryDepartment->year)
                ->where("salary_department_id", $salaryDepartment->id);

            $salaries = $salaryQuery->get();

            # Sync User Leave Balance on Salary Pay
            foreach ($salaries as $salary) {
                $userLeaveDeductions = json_decode($salary->late_leave_deduction);

                if (count($userLeaveDeductions) > 0) {
                    $userLeave = UserLeave::where("user_id", $salary->user_id)
                        ->where("year", $salary->year)
                        ->first();

                    $result = [];
                    $userLeaves = json_decode($userLeave->leaves);
                    $userLeaveDeductions = collect($userLeaveDeductions);
                    foreach ($userLeaves as $userLeave) {
                        $tobeDeducted = $userLeaveDeductions->where("leave_type_id", $userLeave->leave_type_id)->first()->to_be_deducted ?? 0;
                        array_push($result, ["leave_type_id" => $userLeave->leave_type_id, "total_days" => $userLeave->total_days - $tobeDeducted]);
                    }

                    UserLeave::where("user_id", $salary->user_id)->where("year", $salary->year)->update([
                        "leaves" => json_encode($result),
                        "total_leaves" => collect($result)->sum("total_days")
                    ]);
                }

                if ($salary->loan > 0 || $salary->advance > 0) {
                    $employeeLoans = $salary->user->load("activeLoans.userLoans");
                    $activeLoans = $employeeLoans->activeLoans;

                    foreach ($activeLoans as $activeLoan) {
                        # Update UserLoan Table
                        if (!empty($activeLoan->userLoans)) {
                            $userLoanIds = $activeLoan->userLoans
                                ->where('month', $salary->month)
                                ->where('year', $salary->year)
                                ->where('status', UserLoan::DEDUCTION_PENDING)
                                ->pluck('id')
                                ->toArray();

                            UserLoan::whereIn('id', $userLoanIds)->update(['status' => UserLoan::DEDUCTED, 'updated_by' => auth()->id()]);
                        }

                        # Check Paid Status
                        $totalAmount = $activeLoan->loan_amount;
                        $userLoanPaid = UserLoan::where(['user_id' => $salary->user_id, 'loan_id' => $activeLoan->id, 'status' => UserLoan::DEDUCTED])->sum("amount_paid");

                        # Update loan table
                        if ($totalAmount <= round($userLoanPaid + .5)) {
                            $activeLoan->update(array(
                                "status" => Loan::STATUS_PAID
                            ));
                        }
                    }
                }
            }

            $salaryQuery->update([
                "status" => Salary::STATUS_PAID,
                "paid_at" => now()
            ]);

            DB::commit();

            session()->flash('message', 'Salary Paid Successfully');
        } catch (Exception $exception) {
            DB::rollBack();

            $message = "ERROR: Pay Salary to Department " . $exception->getMessage() . " at line no " . $exception->getLine();
            Log::error($message);

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param SalaryDepartment $salaryDepartment
     * @return Factory|\Illuminate\Contracts\View\View
     * @throws Exception
     */
    public function details(SalaryDepartment $salaryDepartment)
    {
        $salaryDepartment = $salaryDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $uuid = $salaryDepartment->uuid;
        $workingDay = getTotalWorkingDaysOfDepartmentByMonth($salaryDepartment->department_id, $salaryDepartment->month, $salaryDepartment->year);

        $salaries = Salary::with("user.employeeStatusJoining", "officeDivision", "department", "designation")
            ->where("office_division_id", $salaryDepartment->office_division_id)
            ->where("department_id", $salaryDepartment->department_id)
            ->where("month", $salaryDepartment->month)
            ->where("year", $salaryDepartment->year)
            ->where("salary_department_id", $salaryDepartment->id)
            ->get();

        # Define View
        $hasCommission = false;
        foreach ($salaries as $salary) {
            if ($salary->parcel_charge > 0) {
                $hasCommission = true;
                break;
            }
        }

        if ($hasCommission) $view = "salary.view-salary-generate-hub";
        else $view = "salary.view-salary-generate";

        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $departmentIds = $filter_obj->getDepartmentIds();
        $departmentIds = is_object($departmentIds) ? $departmentIds->toArray() : $departmentIds;

        return \view($view, compact('salaries', 'salaryDepartment', 'uuid', 'workingDay', 'divisionIds', 'departmentIds'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalDivisional(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            SalaryDepartment::uuid($request->input("uuid"))->update([
                "divisional_approval_status" => $request->input("divisional_status") === "approved" ? SalaryDepartment::STATUS_APPROVED : SalaryDepartment::STATUS_REJECTED,
                "divisional_approval_by" => $userId,
                "divisional_approved_date" => date('Y-m-d H:i:s'),
                "divisional_remarks" => $request->input("reject_reason"),
            ]);

            #Insert Salary Log
            $action = $request->input("divisional_status") === "approved" ? SalaryLog::DIVISION_APPROVED : SalaryLog::DIVISION_REJECTED;
            SalaryLog::generateSalaryLog($request->input("uuid"), $action, $userId, $request->input("reject_reason"));

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalDepartmental(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            SalaryDepartment::uuid($request->input("uuid"))->update([
                "departmental_approval_status" => $request->input("departmental_status") === "approved" ? SalaryDepartment::STATUS_APPROVED : SalaryDepartment::STATUS_REJECTED,
                "departmental_approval_by" => $userId,
                "departmental_approved_date" => date('Y-m-d H:i:s'),
                "departmental_remarks" => $request->input("reject_reason"),
            ]);

            #Insert Salary Log
            $action = $request->input("departmental_status") === "approved" ? SalaryLog::DEPARTMENT_APPROVED : SalaryLog::DEPARTMENT_REJECTED;
            SalaryLog::generateSalaryLog($request->input("uuid"), $action, $userId, $request->input("reject_reason"));

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalHr(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            SalaryDepartment::uuid($request->input("uuid"))->update([
                "hr_approval_status" => $request->input("hr_status") === "approved" ? SalaryDepartment::STATUS_APPROVED : SalaryDepartment::STATUS_REJECTED,
                "hr_approval_by" => $userId,
                "hr_approved_date" => date('Y-m-d H:i:s'),
                "hr_remarks" => $request->input("reject_reason"),
            ]);

            #Insert Salary Log
            $action = $request->input("hr_status") === "approved" ? SalaryLog::HR_APPROVED : SalaryLog::HR_REJECTED;
            SalaryLog::generateSalaryLog($request->input("uuid"), $action, $userId, $request->input("reject_reason"));

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalAccounts(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            SalaryDepartment::uuid($request->input("uuid"))->update([
                "accounts_approval_status" => $request->input("accounts_status") === "approved" ? SalaryDepartment::STATUS_APPROVED : SalaryDepartment::STATUS_REJECTED,
                "accounts_approval_by" => $userId,
                "accounts_approved_date" => date('Y-m-d H:i:s'),
                "accounts_remarks" => $request->input("reject_reason"),
            ]);

            #Insert Salary Log
            $action = $request->input("accounts_status") === "approved" ? SalaryLog::ACCOUNTS_APPROVED : SalaryLog::ACCOUNTS_REJECTED;
            SalaryLog::generateSalaryLog($request->input("uuid"), $action, $userId, $request->input("reject_reason"));

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalManagerial(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            SalaryDepartment::uuid($request->input("uuid"))->update([
                "managerial_approval_status" => $request->input("managerial_status") === "approved" ? SalaryDepartment::STATUS_APPROVED : SalaryDepartment::STATUS_REJECTED,
                "managerial_approval_by" => $userId,
                "managerial_approved_date" => date('Y-m-d H:i:s'),
                "managerial_remarks" => $request->input("reject_reason"),
            ]);

            #Insert Salary Log
            $action = $request->input("managerial_status") === "approved" ? SalaryLog::MANAGEMENT_APPROVED : SalaryLog::MANAGEMENT_REJECTED;
            SalaryLog::generateSalaryLog($request->input("uuid"), $action, $userId, $request->input("reject_reason"));

            DB::commit();

            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param SalaryDepartment $salaryDepartment
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function salaryExport(Request $request, SalaryDepartment $salaryDepartment)
    {
        $monthName = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $salaryDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $fileName = 'salary-sheet-' . $salaryDepartment->load("department")->department->name . '-' . $monthName[$salaryDepartment->month] . '-' . $salaryDepartment->year;
        if ($request->input("type") === "Export CSV" and auth()->user()->can("Export Salary CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new SalarySheetExport([$salaryDepartment->id], $salaryDepartment->month, $salaryDepartment->year), $fileName);
        }

        if ($request->input("type") === "Export PDF" and auth()->user()->can("Export Salary PDF")) {
            $fileName .= '.pdf';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            $workingDay = getTotalWorkingDaysOfDepartmentByMonth($salaryDepartment->department_id, $salaryDepartment->month, $salaryDepartment->year);

            $salaries = Salary::with("user.employeeStatusJoining", "officeDivision", "department", "designation")
                ->where("office_division_id", $salaryDepartment->office_division_id)
                ->where("department_id", $salaryDepartment->department_id)
                ->where("month", $salaryDepartment->month)
                ->where("year", $salaryDepartment->year)
                ->where("salary_department_id", $salaryDepartment->id)
                ->get();

            # Define View
            $hasCommission = false;
            foreach ($salaries as $salary) {
                if ($salary->parcel_charge > 0) {
                    $hasCommission = true;
                    break;
                }
            }

            return PDF::loadView('salary.salary_export_pdf', compact("salaries", "workingDay", "hasCommission", 'salaryDepartment'))->setPaper('a4', 'landscape')->download($fileName);
        }

        # Bank Statement PDF
        if ($request->input("type") === "Bank Statement PDF" and auth()->user()->can("Export Salary Bank Statement PDF")) {
            $fileName .= '.pdf';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);

            $salaries = Salary::with("user.currentBank")
                ->where("office_division_id", $salaryDepartment->office_division_id)
                ->where("department_id", $salaryDepartment->department_id)
                ->where("month", $salaryDepartment->month)
                ->where("year", $salaryDepartment->year)
                ->where("salary_department_id", $salaryDepartment->id)
                ->get();

            return PDF::loadView('salary.salary_export_bank_statement_pdf', compact("salaries"))->download($fileName);
        }

        # Bank Statement CSV
        if ($request->input("type") === "Bank Statement CSV" and auth()->user()->can("Export Salary Bank Statement CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new SalarySheetBankExport([$salaryDepartment->id], $salaryDepartment->month, $salaryDepartment->year), $fileName);
        }
    }

    /**
     * @param $input
     * @return string
     */
    protected function formatBDT($input)
    {
        //CUSTOM FUNCTION TO GENERATE ##,##,###.##
        $dec = "";
        $pos = strpos($input, ".");
        if ($pos === false) {
            //no decimals
        } else {
            //decimals
            $dec = substr(round(substr($input, $pos), 2), 1);
            $input = substr($input, 0, $pos);
        }
        $num = substr($input, -3); //get the last 3 digits
        $input = substr($input, 0, -3); //omit the last 3 digits already stored in $num
        while (strlen($input) > 0) //loop the process - further get digits 2 by 2
        {
            $num = substr($input, -2) . "," . $num;
            $input = substr($input, 0, -2);
        }
        return $num . $dec;
    }

    /**
     * @param $floatcurr
     * @param string $curr
     * @return string
     */
    public static function currencyFormat($floatcurr, $curr = "BDT")
    {
        $currencies['ARS'] = array(2, ',', '.');          //  Argentine Peso
        $currencies['AMD'] = array(2, '.', ',');          //  Armenian Dram
        $currencies['AWG'] = array(2, '.', ',');          //  Aruban Guilder
        $currencies['AUD'] = array(2, '.', ' ');          //  Australian Dollar
        $currencies['BSD'] = array(2, '.', ',');          //  Bahamian Dollar
        $currencies['BHD'] = array(3, '.', ',');          //  Bahraini Dinar
        $currencies['BDT'] = array(2, '.', ',');          //  Bangladesh, Taka
        $currencies['BZD'] = array(2, '.', ',');          //  Belize Dollar
        $currencies['BMD'] = array(2, '.', ',');          //  Bermudian Dollar
        $currencies['BOB'] = array(2, '.', ',');          //  Bolivia, Boliviano
        $currencies['BAM'] = array(2, '.', ',');          //  Bosnia and Herzegovina, Convertible Marks
        $currencies['BWP'] = array(2, '.', ',');          //  Botswana, Pula
        $currencies['BRL'] = array(2, ',', '.');          //  Brazilian Real
        $currencies['BND'] = array(2, '.', ',');          //  Brunei Dollar
        $currencies['CAD'] = array(2, '.', ',');          //  Canadian Dollar
        $currencies['KYD'] = array(2, '.', ',');          //  Cayman Islands Dollar
        $currencies['CLP'] = array(0, '', '.');           //  Chilean Peso
        $currencies['CNY'] = array(2, '.', ',');          //  China Yuan Renminbi
        $currencies['COP'] = array(2, ',', '.');          //  Colombian Peso
        $currencies['CRC'] = array(2, ',', '.');          //  Costa Rican Colon
        $currencies['HRK'] = array(2, ',', '.');          //  Croatian Kuna
        $currencies['CUC'] = array(2, '.', ',');          //  Cuban Convertible Peso
        $currencies['CUP'] = array(2, '.', ',');          //  Cuban Peso
        $currencies['CYP'] = array(2, '.', ',');          //  Cyprus Pound
        $currencies['CZK'] = array(2, '.', ',');          //  Czech Koruna
        $currencies['DKK'] = array(2, ',', '.');          //  Danish Krone
        $currencies['DOP'] = array(2, '.', ',');          //  Dominican Peso
        $currencies['XCD'] = array(2, '.', ',');          //  East Caribbean Dollar
        $currencies['EGP'] = array(2, '.', ',');          //  Egyptian Pound
        $currencies['SVC'] = array(2, '.', ',');          //  El Salvador Colon
        $currencies['ATS'] = array(2, ',', '.');          //  Euro
        $currencies['BEF'] = array(2, ',', '.');          //  Euro
        $currencies['DEM'] = array(2, ',', '.');          //  Euro
        $currencies['EEK'] = array(2, ',', '.');          //  Euro
        $currencies['ESP'] = array(2, ',', '.');          //  Euro
        $currencies['EUR'] = array(2, ',', '.');          //  Euro
        $currencies['FIM'] = array(2, ',', '.');          //  Euro
        $currencies['FRF'] = array(2, ',', '.');          //  Euro
        $currencies['GRD'] = array(2, ',', '.');          //  Euro
        $currencies['IEP'] = array(2, ',', '.');          //  Euro
        $currencies['ITL'] = array(2, ',', '.');          //  Euro
        $currencies['LUF'] = array(2, ',', '.');          //  Euro
        $currencies['NLG'] = array(2, ',', '.');          //  Euro
        $currencies['PTE'] = array(2, ',', '.');          //  Euro
        $currencies['GHC'] = array(2, '.', ',');          //  Ghana, Cedi
        $currencies['GIP'] = array(2, '.', ',');          //  Gibraltar Pound
        $currencies['GTQ'] = array(2, '.', ',');          //  Guatemala, Quetzal
        $currencies['HNL'] = array(2, '.', ',');          //  Honduras, Lempira
        $currencies['HKD'] = array(2, '.', ',');          //  Hong Kong Dollar
        $currencies['HUF'] = array(0, '', '.');           //  Hungary, Forint
        $currencies['ISK'] = array(0, '', '.');           //  Iceland Krona
        $currencies['INR'] = array(2, '.', ',');          //  Indian Rupee
        $currencies['IDR'] = array(2, ',', '.');          //  Indonesia, Rupiah
        $currencies['IRR'] = array(2, '.', ',');          //  Iranian Rial
        $currencies['JMD'] = array(2, '.', ',');          //  Jamaican Dollar
        $currencies['JPY'] = array(0, '', ',');           //  Japan, Yen
        $currencies['JOD'] = array(3, '.', ',');          //  Jordanian Dinar
        $currencies['KES'] = array(2, '.', ',');          //  Kenyan Shilling
        $currencies['KWD'] = array(3, '.', ',');          //  Kuwaiti Dinar
        $currencies['LVL'] = array(2, '.', ',');          //  Latvian Lats
        $currencies['LBP'] = array(0, '', ' ');           //  Lebanese Pound
        $currencies['LTL'] = array(2, ',', ' ');          //  Lithuanian Litas
        $currencies['MKD'] = array(2, '.', ',');          //  Macedonia, Denar
        $currencies['MYR'] = array(2, '.', ',');          //  Malaysian Ringgit
        $currencies['MTL'] = array(2, '.', ',');          //  Maltese Lira
        $currencies['MUR'] = array(0, '', ',');           //  Mauritius Rupee
        $currencies['MXN'] = array(2, '.', ',');          //  Mexican Peso
        $currencies['MZM'] = array(2, ',', '.');          //  Mozambique Metical
        $currencies['NPR'] = array(2, '.', ',');          //  Nepalese Rupee
        $currencies['ANG'] = array(2, '.', ',');          //  Netherlands Antillian Guilder
        $currencies['ILS'] = array(2, '.', ',');          //  New Israeli Shekel
        $currencies['TRY'] = array(2, '.', ',');          //  New Turkish Lira
        $currencies['NZD'] = array(2, '.', ',');          //  New Zealand Dollar
        $currencies['NOK'] = array(2, ',', '.');          //  Norwegian Krone
        $currencies['PKR'] = array(2, '.', ',');          //  Pakistan Rupee
        $currencies['PEN'] = array(2, '.', ',');          //  Peru, Nuevo Sol
        $currencies['UYU'] = array(2, ',', '.');          //  Peso Uruguayo
        $currencies['PHP'] = array(2, '.', ',');          //  Philippine Peso
        $currencies['PLN'] = array(2, '.', ' ');          //  Poland, Zloty
        $currencies['GBP'] = array(2, '.', ',');          //  Pound Sterling
        $currencies['OMR'] = array(3, '.', ',');          //  Rial Omani
        $currencies['RON'] = array(2, ',', '.');          //  Romania, New Leu
        $currencies['ROL'] = array(2, ',', '.');          //  Romania, Old Leu
        $currencies['RUB'] = array(2, ',', '.');          //  Russian Ruble
        $currencies['SAR'] = array(2, '.', ',');          //  Saudi Riyal
        $currencies['SGD'] = array(2, '.', ',');          //  Singapore Dollar
        $currencies['SKK'] = array(2, ',', ' ');          //  Slovak Koruna
        $currencies['SIT'] = array(2, ',', '.');          //  Slovenia, Tolar
        $currencies['ZAR'] = array(2, '.', ' ');          //  South Africa, Rand
        $currencies['KRW'] = array(0, '', ',');           //  South Korea, Won
        $currencies['SZL'] = array(2, '.', ', ');         //  Swaziland, Lilangeni
        $currencies['SEK'] = array(2, ',', '.');          //  Swedish Krona
        $currencies['CHF'] = array(2, '.', '\'');         //  Swiss Franc
        $currencies['TZS'] = array(2, '.', ',');          //  Tanzanian Shilling
        $currencies['THB'] = array(2, '.', ',');          //  Thailand, Baht
        $currencies['TOP'] = array(2, '.', ',');          //  Tonga, Paanga
        $currencies['AED'] = array(2, '.', ',');          //  UAE Dirham
        $currencies['UAH'] = array(2, ',', ' ');          //  Ukraine, Hryvnia
        $currencies['USD'] = array(2, '.', ',');          //  US Dollar
        $currencies['VUV'] = array(0, '', ',');           //  Vanuatu, Vatu
        $currencies['VEF'] = array(2, ',', '.');          //  Venezuela Bolivares Fuertes
        $currencies['VEB'] = array(2, ',', '.');          //  Venezuela, Bolivar
        $currencies['VND'] = array(0, '', '.');           //  Viet Nam, Dong
        $currencies['ZWD'] = array(2, '.', ' ');          //  Zimbabwe Dollar

        if ($curr == "BDT") {
            return (new self())->formatBDT($floatcurr);
        } else {
            return number_format($floatcurr, $currencies[$curr][0], $currencies[$curr][1], $currencies[$curr][2]);
        }
    }

    /**
     * @param $number
     * @return string
     */
    public static function getBangladeshCurrency($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $Taka = implode('', array_reverse($str));
        $poysa = ($decimal) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' poisa' : '';
        $inWords = ($Taka ? $Taka . 'taka ' : '') . $poysa . ' Only';
        return ucwords($inWords);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function regenerate(Request $request)
    {
        try {
            DB::beginTransaction();

            $salaryDepartment = SalaryDepartment::uuid($request->input("salary_department_id"))->first();

            $salaryQuery = Salary::where("office_division_id", $salaryDepartment->office_division_id)
                ->where("department_id", $salaryDepartment->department_id)
                ->where("month", $salaryDepartment->month)
                ->where("year", $salaryDepartment->year)
                ->where("salary_department_id", $salaryDepartment->id);

            $salaryUsers = $salaryQuery->pluck('user_id')->toArray();

            #Delete related pending loan payments
            if (count($salaryUsers) > 0) {
                UserLoan::whereIn('user_id', $salaryUsers)
                    ->where(['month' => $salaryDepartment->month, 'year' => $salaryDepartment->year, 'status' => UserLoan::DEDUCTION_PENDING])
                    ->update(['status' => UserLoan::AMOUNT_APPROVED, 'updated_by' => auth()->id()]);
            }

            $salaryQuery->delete();

            $generateSalaryRequest = [
                "office_division_id" => $salaryDepartment->office_division_id,
                "department_id" => [$salaryDepartment->department_id],
                "month_and_year" => $salaryDepartment->month . "-" . $salaryDepartment->year,
                "user_id" => $salaryUsers,
            ];

            if ($request->has("overtime")) {
                $generateSalaryRequest = array_merge($generateSalaryRequest, [
                    "overtime" => "on"
                ]);
            }

            if ($request->has("late_deduction")) {
                $generateSalaryRequest = array_merge($generateSalaryRequest, [
                    "late_deduction" => "on"
                ]);
            }

            if ($request->has("absent_deduction")) {
                $generateSalaryRequest = array_merge($generateSalaryRequest, [
                    "absent_deduction" => "on"
                ]);
            }

            $generateSalaryRequest = new Request($generateSalaryRequest);
            $salaryDepartment->delete();

            $this->generateSalary($generateSalaryRequest, true);

            DB::commit();

            session()->flash('message', 'Salary Regenerated Successfully!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @return array
     */
    protected function getDepartmentSupervisorIds()
    {
        $divisionSupervisor = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->orderByDesc("id")->pluck("office_division_id")->toArray();
        $departmentSupervisor = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id")->toArray();

        if (count($divisionSupervisor) > 0) {
            $departmentIds = Department::whereIn("office_division_id", $divisionSupervisor)->pluck("id")->toArray();
        } elseif (count($departmentSupervisor) > 0) {
            $departmentIds = $departmentSupervisor;
        } else {
            $departmentIds = [];
        }

        return $departmentIds;
    }

    protected function departmentDivisionWiseSalaryEmployees(Request $request)
    {
        app('debugbar')->disable();

        $salaryEmployees = [];

        $today = (date('Y-m-d'));

        $office_division_id = $request->office_division_id;
        $department_ids = $request->department_id;

        if ($office_division_id == 'all') {
            $find_division = true;
        } else {
            $find_division = false;
        }
        if (in_array("all", $department_ids)) {
            $find_department = true;
        } else {
            $find_department = false;
        }

        if ($find_department) {
            if ($find_division) {
                if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                    $filter_obj = new FilterController();
                    $divisionIds = $filter_obj->getDivisionIds();

                    $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id', $divisionIds)->get();
                    $departmentIds = [];
                    foreach ($data['officeDepartments'] as $item) {
                        $departmentIds[] = $item->id;
                    }

                    $departmentIds_in_string = implode(',', $departmentIds);

//                        $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
//                        $users = DB::select($sql);
                } else {
                    $filter_obj = new FilterController();
                    $divisionIds = $filter_obj->getDivisionIds();
                    $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn("office_division_id", $divisionIds)
                        ->pluck('department_id')->toArray();
                    $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn("office_division_id", $divisionIds)
                        ->pluck('office_division_id')->toArray();
                    $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                    $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                    $departmentIds_in_string = implode(',', $departmentIds);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            } else {
                if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                    $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                    $departmentIds = [];
                    foreach ($departments as $item) {
                        $departmentIds[] = $item->id;
                    }
                    $departmentIds_in_string = implode(',', $departmentIds);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                } else {
                    $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->where("office_division_id", '=', $request->office_division_id)
                        ->pluck('department_id')->toArray();
                    $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->where("office_division_id", '=', $request->office_division_id)
                        ->pluck('office_division_id')->toArray();
                    $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                    $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                    $departmentIds_in_string = implode(',', $departmentIds);
                    # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }
        } else {
            $departmentIds_in_string = implode(',', $department_ids);
            # $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
            $departmentIds = $department_ids;
        }

        # $departmentIds -> Department IDs as array
        # $department_ids -> Department IDs from Salary Generation Form
        # $departmentIds_in_string -> implode by comma separator of $departmentIds

        # Parse Date and Month from the DatePicker input type
        $datePicker = \Functions::getMonthAndYearFromDatePicker($request->input("month_and_year"));
        $month = $datePicker["month"];
        $year = $datePicker["year"];
        $monthYear = $year . '-' . sprintf("%02s", $month) . '-%';

        # Remove Departments which already generates salary
        $generatedDepartments = SalaryDepartment::where("month", $month)
            ->where("year", $year)
            ->whereIn("department_id", $departmentIds)
            ->pluck("department_id");


        $salaryToBeGeneratedForDepartments = collect($departmentIds)->reject(function ($id) use ($generatedDepartments) {
            if (in_array($id, $generatedDepartments->toArray())) return $id;
        })->toArray();


        $parcelCommission = $this->parcelCommission($year . '-' . $month . '-1');

        foreach ($salaryToBeGeneratedForDepartments as $departmentId) {
            $totalPayableAmountOfDepartment = 0;

            $sql = "SELECT users.id
                    FROM
                    `users`
                    INNER JOIN
                    promotions as prm
                    ON
                    prm.user_id = users.id
                    AND
                    prm.id = (
                        SELECT MAX( pm.id ) FROM `promotions` AS pm
                            where pm.user_id = users.id
                        )
                    WHERE `users`.`id` IN
                        ( SELECT `promotions`.`user_id` FROM `promotions`
                            WHERE `promotions`.`department_id` IN ( $departmentId )
                            AND `promotions`.`id` IN
                                ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )
                        )
                      AND users.id IN ( SELECT user_id FROM `daily_attendances`
                      WHERE `daily_attendances`.`user_id` = users.id
                        AND `date` LIKE '$monthYear' AND (`daily_attendances`.present_count > 0 OR `daily_attendances`.leave_count > 0) )
                    GROUP BY `users`.id;
                    /*AND
                    `users`.`status` = 1*/";


            $users = DB::select($sql);
            $userIds = collect($users)->pluck("id");


            $salaries = [];

            $totalDayInTheMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            # Check meta data before generating the salary
            $salaryDepartment = SalaryDepartment::where("department_id", $departmentId)->where("month", $month)->where("year", $year)->first();

            # Generate Salary whether not generated yet
            if (!isset($salaryDepartment)) {
                $employees = User::with("currentPromotion.designation", "currentPromotion.payGrade", "lateAllow")
                    ->whereIn("id", $userIds)
                    /*->active()*/ ->get();

                $employeeIds = $employees->pluck("id");


                if ($employeeIds->count() == 0) continue;

                $employeeIds = $employeeIds->count() > 0 ? implode(", ", $employeeIds->all()) : "";

                $sql = "SELECT
                            `user_id`, `emp_code`, `date`,

                            COUNT(`date`) AS total_days,

                            SUM(CASE WHEN `is_weekly_holiday` <> 1 AND `is_public_holiday` <> 1
                                THEN `present_count` ELSE 0 END) as regular_duty,

                            SUM(CASE WHEN `present_count` = 1  AND `is_weekly_holiday` = 1
                                 THEN `is_weekly_holiday` ELSE 0 END) weekly_holiday_duty,

                            SUM(CASE WHEN `present_count` = 1  AND `is_public_holiday` = 1
                                 THEN `is_public_holiday` ELSE 0 END) official_holiday_duty,

                            SUM(`leave_count`) as total_leave,

                            (SUM(`overtime_min`) / 60) as overtime_hours,

                            SUM(`is_weekly_holiday`) as weekend_holiday,

                            SUM(`is_public_holiday`) as public_holiday,

                            SUM(`is_late_final`) as total_late,

                            SUM(`absent_count`) as total_absent,

                            ROUND((SUM(`working_min`) / 60 / SUM(`present_count`)), 2) as working_hours

                        FROM `daily_attendances` WHERE `user_id` IN($employeeIds) AND `date` LIKE '$monthYear'

                        GROUP BY `user_id`
                        ;
                ";
                $reports = DB::select($sql);
                $reports = collect($reports);


                foreach ($employees as $employee) {//if($employee->fingerprint_no != 950) continue;

                    #Check the employee is eligible or not for this month salary by joining date. I need to be optimized by employee status
                    $lastDateOfSalaryMonth = date("Y-m-t", strtotime(date("$year-$month-10")));
                    $firstDateOfSalaryMonth = date("Y-m-d", strtotime(date("$year-$month-01")));
                    $joiningDate = date('Y-m-d', strtotime($employee->employeeStatusJoining->action_date));

                    if ($joiningDate > $lastDateOfSalaryMonth) {
                        continue;
                    }

                    $salaryEmployees[] = [
                        $employee->id => $employee->fingerprint_no . ' - ' . $employee->name
                    ];
                }

            } else {
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry! Data already exists!!');
                return redirect()->route("salary.viewSalary");
            }
        }

        return json_encode($salaryEmployees);
    }
}
