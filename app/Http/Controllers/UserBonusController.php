<?php

namespace App\Http\Controllers;

use App\Exports\Report\BonusSheetBankExport;
use App\Exports\Report\BonusSheetExport;
use App\Http\Requests\bonus\RequestUserBonus;
use App\Library\GenerateSalary;
use App\Models\Bonus;
use App\Models\BonusDepartment;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\OfficeDivision;
use App\Models\Profile;
use App\Models\TaxRule;
use App\Models\User;
use App\Models\UserBonus;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserBonusController extends Controller
{
    use GenerateSalary;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $allowedStr = '/[^A-Za-z0-9\-\.]/';
    protected $paymentModes = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->paymentModes = User::paymentModes();
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        # Define The Latest Salary Month and Year
        $latest = $this->getLatestBonusGenerationMonthYear();

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
                }
            } else {
                if ((auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) || auth()->user()->can('Show All Salary List')) {
                    $departments = Department::where("office_division_id", '=', $office_division_id)->select("id", "name")->get();
                    $departmentIds = [];
                    foreach ($departments as $item) {
                        $departmentIds[] = $item->id;
                    }
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
                }
            }
        } else {
            $departmentIds = $department_ids;
        }

        # Define Items to View Salary
        $items = BonusDepartment::with(
            "officeDivision", "department", "preparedBy", "divisionalApprovalBy",
            "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $isAdmin = auth()->user()->isAdmin();
        $isAccountant = false;
        $accountsDepartmentsIds = [];
        if (auth()->user()->can('Salary Accounts Approval') && !$isAdmin) {
            $isAccountant = true;
            $accountsDepartmentsIds = $filter_obj->getDepartmentIds();
        }

        if (auth()->user()->can('Salary Managerial Approval') && !$isAdmin) {
            $items = $items->where(['hr_approval_status' => 1, 'accounts_approval_status' => 1]);
        }

        if (\request()->has('bonus_id')) {
            $items = $items->whereIn("bonus_id", [\request()->get("bonus_id")]);
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

        $bonuses = Bonus::where('status', 1)->orderBy('festival_name')->pluck('festival_name', 'id')->toArray();

        $data = array(
            "officeDivisions" => $data['officeDivisions'],
            "departments" => $data['officeDepartments'],
            "bonuses" => $bonuses
        );

        return \view("user-bonus.index", compact('data', 'items', 'latest', 'isAccountant', 'accountsDepartmentsIds'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
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

        $departmentIds_in_string = implode(',', $departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        $data['bonuses'] = Bonus::where('status', 1)->orderBy('festival_name')->pluck('festival_name', 'id')->toArray();

        return view('user-bonus.create', compact("data"));
    }

    /**
     * TODO: Fix it
     * Bonus amount is fully taxable as per last meeting.
     *
     * @param RequestUserBonus $request
     * @return RedirectResponse
     */
    public function store(RequestUserBonus $request, $reGenerate = false)
    {
        app('debugbar')->disable();

        DB::beginTransaction();

        try {
            $bonus = Bonus::find($request->input("bonus_id"));

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

            # Parse Date and Month from the DatePicker input type
            $datePicker = \Functions::getMonthAndYearFromDatePicker($request->input("month_and_year"));
            $month = $datePicker["month"];
            $year = $datePicker["year"];
            $monthYear = $year . '-' . sprintf("%02s", $month) . '-%';

            $isTaxGenerated = false;
            foreach ($departmentIds as $departmentId) {
                $totalPayableAmountOfDepartment = 0;
                $bonusDepartment = null;

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
                        WHERE `users`.`status` = 1 AND `users`.`id` IN
                            ( SELECT `promotions`.`user_id` FROM `promotions`
                                WHERE `promotions`.`department_id` IN ( $departmentId )
                                AND `promotions`.`id` IN
                                    ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )
                            )
                          /*AND users.id IN ( SELECT user_id FROM `daily_attendances`
                          WHERE `daily_attendances`.`user_id` = users.id
                            AND `date` LIKE '$monthYear' AND (`daily_attendances`.present_count > 0 OR `daily_attendances`.leave_count > 0) )*/
                        GROUP BY `users`.id;
                        /*AND
                        `users`.`status` = 1*/";


                $users = DB::select($sql);
                $userIds = collect($users)->pluck("id");

                # Change userIds if input user exist
                if (count($inputUsers) > 0) {
                    $departmentWiseInputUsers = [];
                    foreach ($userIds as $departmentUserId) {
                        if (in_array($departmentUserId, $inputUsers)) {
                            $departmentWiseInputUsers[] = $departmentUserId;
                        }
                    }
                    $userIds = $departmentWiseInputUsers;
                }

                if (count($userIds) == 0) continue;

                $bonuses = [];

                $employees = User::with("currentPromotion.designation")->whereIn("id", $userIds)->get();

                $employeeIds = $employees->pluck("id");

                if ($employeeIds->count() == 0) continue;

                # Check user salary already been generated or not
                $bonusGeneratedUsers = UserBonus::where(['month' => $month, 'year' => $year, 'bonus_id' => $bonus->id])->pluck('user_id')->toArray();
                $departmentBonusGeneration = false;

                foreach ($employees as $employee) {//if($employee->fingerprint_no != 516) continue;

                    if (in_array($employee->id, $bonusGeneratedUsers)) {
                        continue;
                    }

                    $currentPromotion = $employee->currentPromotion;
                    $grossSalary = $currentPromotion->salary;
                    $percentageOfBasic = $currentPromotion->payGrade->percentage_of_basic;
                    $basicSalary = $grossSalary * ($percentageOfBasic / 100);
                    $employeeEarnings = $employee->currentPromotion->payGrade->earnings;
                    $basedOn = $currentPromotion->payGrade->based_on;
                    $tobeDividedBy = 12;

                    $salaryAmountToBeCalculate = 0;
                    if ($bonus->type == Bonus::TYPE_GROSS) {
                        $salaryAmountToBeCalculate = $grossSalary;
                    } elseif ($bonus->type == Bonus::TYPE_BASIC) {
                        $salaryAmountToBeCalculate = $basicSalary;
                    }

                    # Define Employee is eligible for bonus or not. Also Calculate Eligible Percentage of Bonus
                    $joiningDate = $employee->employeeStatus()->where("action_reason_id", 2)->orderBy('id', 'DESC')->first()->action_date;
                    $bonusEffectiveDate = date('Y-m-d', strtotime($bonus->effective_date. '+2 day'));

                    $origin = new \DateTime($joiningDate);
                    $target = new \DateTime($bonusEffectiveDate);
                    $interval = $origin->diff($target);//dd($origin, $target, $interval);

                    /*$origin = Carbon::parse($joiningDate);
                    $target = Carbon::parse($bonusEffectiveDate);
                    $interval = $origin->diffInMonths($target);*///dd($origin, $target, $interval);

                    $isEmployeeEligible = false;
                    $eligiblePercentage = 0;
                    $bonusPaymentDetails = json_decode($bonus->payment_details, true);
                    arsort($bonusPaymentDetails);

                    foreach ($bonusPaymentDetails as $eligibleMonth => $eligiblePercentageOfThisMonth) {
                        if ($interval->format("%y") > 0 || $interval->format("%m") >= $eligibleMonth) {
                            $isEmployeeEligible = true;
                            $eligiblePercentage = $eligiblePercentageOfThisMonth;
                            break;
                        }

                        /*if ($interval >= $eligibleMonth) {
                            $isEmployeeEligible = true;
                            $eligiblePercentage = $eligiblePercentageOfThisMonth;
                            break;
                        }*/
                    }

                    if (!$isEmployeeEligible) {
                        continue;
                    }

                    # Calculate payable bonus amount
                    $bonusAmount = 0;
                    $bonusAmount = (($salaryAmountToBeCalculate * $eligiblePercentage / 100));

                    # Calculate Tax
                    $taxAmount = 0;
                    if ($request->has("tax")) {
                        $taxAmount =  $this->employeeTaxableAmountForCurrentMonth($employee, $currentPromotion, $grossSalary, $bonusAmount, $tobeDividedBy);
                    }

                    $totalPayable = (double)$bonusAmount;
                    $netPayable = (double)$totalPayable - (double)$taxAmount;

                    $totalPayableAmountOfDepartment += $netPayable;

                    $getEmployeeTotalEarnings = $this->getEmployeeTotalEarnings($employee, $grossSalary, $basicSalary, $employeeEarnings, $basedOn, $tobeDividedBy);
                    $houseRent = collect($getEmployeeTotalEarnings["earnings"])->where("name", "House Rent")->first()['amount'];
                    $medicalAllowance = collect($getEmployeeTotalEarnings["earnings"])->where("name", "Medical Allowance")->first()['amount'];
                    $conveyance = collect($getEmployeeTotalEarnings["earnings"])->where("name", "Conveyance")->first()['amount'];

                    if (!$departmentBonusGeneration) {
                        $bonusDepartment = BonusDepartment::create([
                            "uuid" => \Functions::getNewUuid(),
                            "office_division_id" => $employee->currentPromotion->office_division_id,
                            "department_id" => $employee->currentPromotion->department_id,
                            "bonus_id" => $bonus->id,
                            "month" => $month,
                            "year" => $year,
                            "status" => BonusDepartment::STATUS_UNPAID,
                            "total_payable_amount" => $totalPayableAmountOfDepartment,
                            "prepared_by" => Auth::id(),
                            "prepared_date" => date('Y-m-d H:i:s'),
                            "created_by" => Auth::id(),
                        ]);

                        $departmentBonusGeneration = true;
                    }

                    if ($bonusDepartment) {
                        $userBonus = [
                            "bonus_department_id" => $bonusDepartment->id,
                            "uuid" => \Functions::getNewUuid(),
                            "user_id" => $employee->id,
                            "office_division_id" => $employee->currentPromotion->office_division_id,
                            "department_id" => $employee->currentPromotion->department_id,
                            "bonus_id" => $bonus->id,
                            "designation_id" => $employee->currentPromotion->designation_id,
                            "pay_grade_id" => $employee->currentPromotion->pay_grade_id,
                            "tax_id" => $employee->currentPromotion->payGrade->tax_id,
                            "basic" => $basicSalary,
                            "house_rent" => $houseRent,
                            "medical_allowance" => $medicalAllowance,
                            "conveyance" => $conveyance,
                            "gross" => $grossSalary,
                            "amount" => $totalPayable,
                            "tax" => $taxAmount,
                            "net_payable_amount" => $netPayable,
                            "status" => 0,
                            "month" => $month,
                            "year" => $year,
                            "payment_mode" => $this->paymentModes[$employee->payment_mode] ?? $this->paymentModes[User::BANK_MODE],
                            "remarks" => "",
                            "created_at" => Carbon::now(),
                        ];
                        array_push($bonuses, $userBonus);
                    }
                }

                # Insert a meta data to the corresponding table related to salary
                if (count($bonuses) > 0) {
                    UserBonus::insert($bonuses);

                    $bonusDepartment->total_payable_amount = $totalPayableAmountOfDepartment;
                    $bonusDepartment->update();

                    $isTaxGenerated = true;
                }
            }

            DB::commit();

            if($isTaxGenerated){
                session()->flash('message', 'Bonus Generated Successfully');
            }else{
                session()->flash('message', 'Employee is not eligible for the bonus!!');
            }
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception->getMessage());

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->route("user-bonus.index");
    }

    /**
     * @param User $user
     * @param $currentPromotion
     * @param $taxEligibleAmount
     * @param $bonus
     * @return float|int
     */
    protected function employeeTaxableAmountOnBonusForCurrentMonth(User $user, $currentPromotion, $taxEligibleAmount, Bonus $bonus)
    {
        try {
            if (!is_null($currentPromotion->payGrade->tax_id)) {
                $employeeGender = $user->load("profile")->profile->gender;
                $salary = $currentPromotion->salary;
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
                        $temp += $taxRule->slab / 12;
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

                if ($bonus->type == Bonus::TYPE_GROSS) {
                    $totalTaxEligibleAmount += (($salary * $bonus->percentage_of_bonus / 100) / 12);
                } elseif ($bonus->type == Bonus::TYPE_BASIC) {
                    $totalTaxEligibleAmount += (($basicSalary * $bonus->percentage_of_bonus / 100) / 12);
                }

                $taxEligibleAmountYearly = $totalTaxEligibleAmount * 12;

                $taxableAmount = 0;
                for ($i = 0; $i < count($taxRulesPerMonth); $i++) {
                    if ($totalTaxEligibleAmount >= $taxRulesPerMonth[$i]) {
                        $taxableInThisMonth = $totalTaxEligibleAmount - $taxRulesPerMonth[$i];
                        $taxableAmount += $taxableInThisMonth * ($taxRatesPerMonth[$i - 1] / 100);
                        $totalTaxEligibleAmount -= $taxableInThisMonth;
                    }
                }

                # Rebate manipulation
                $taxableAmount = $this->getTaxAfterRebate(
                    $taxEligibleAmountYearly, $employeeTax, $taxableAmount, $employeeTaxRules[TaxRule::TYPE_REBATE], 12
                );
            } else {
                $taxableAmount = 0;
            }
        } catch (Exception $exception) {
            $taxableAmount = 0;
        }

        return round($taxableAmount, 2);
    }

    /**
     * @return Factory|View
     */
    public function paySlip()
    {
        $userBonuses = UserBonus::with("bonus")
            ->where("user_id", auth()->user()->id)
            ->orderByDesc("id")
            ->paginate(\Functions::getPaginate());

        return \view("user-bonus.pay-slip", compact("userBonuses"));
    }

    /**
     * @param UserBonus $userBonus
     * @return Factory|View
     */
    public function generatePaySlip($userBonusUuid)
    {
        $userBonus = UserBonus::where('uuid', $userBonusUuid )->first();
        $userBonus = $userBonus->load("bonus", "user.currentPromotion.department", "user.currentPromotion.designation");
        return view('user-bonus.generate_pay_slip', compact("userBonus"));
    }

    /**
     * @param UserBonus $userBonus
     * @return mixed
     */
    public function pdfDownload($userBonusUuid)
    {
        $userBonus = UserBonus::where('uuid', $userBonusUuid )->first();
        $userBonus = $userBonus->load("bonus", "officeDivision", "user.currentPromotion.designation");
        activity('payslip-download')->by(auth()->user())->log('Pay Slip for Bonus has been exported');

        $fileName = 'bonus-pay-slip-' . $userBonus->bonus->festival_name . '-' . date('M-Y', mktime(null, null, null, $userBonus->month, null, $userBonus->year)) . '.pdf';
        $fileName = preg_replace($this->allowedStr, '-', $fileName);

        return PDF::loadView('user-bonus.pay_slip_pdf', compact("userBonus"))->download($fileName);
    }

    /**
     * @return mixed
     */
    protected function getLatestBonusGenerationMonthYear()
    {
        $sql = "SELECT MAX(month) as latest_month, year as latest_year
                    FROM bonus_departments
                    WHERE
                    year = (
                        SELECT MAX(year) FROM bonus_departments
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
    public function payBonusToDepartment(Request $request)
    {
        try {
            DB::beginTransaction();

            $bonusDepartment = BonusDepartment::uuid($request->input("bonus_department_id"))->first();

            $bonusDepartment->update([
                "status" => BonusDepartment::STATUS_PAID,
                "paid_at" => now()
            ]);

            $bonusQuery = UserBonus::where("office_division_id", $bonusDepartment->office_division_id)
                ->where("department_id", $bonusDepartment->department_id)
                ->where("month", $bonusDepartment->month)
                ->where("year", $bonusDepartment->year)
                ->where("bonus_id", $bonusDepartment->bonus_id)
                ->where("bonus_department_id", $bonusDepartment->id);

            $bonusQuery->update([
                "status" => UserBonus::STATUS_PAID,
                "paid_at" => now()
            ]);

            DB::commit();

            session()->flash('message', 'Bonus Paid Successfully');
        } catch (Exception $exception) {
            DB::rollBack();

            $message = "ERROR: Pay Bonus to Department " . $exception->getMessage() . " at line no " . $exception->getLine();
            Log::error($message);

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }

    /**
     * @param BonusDepartment $bonusDepartment
     * @return Factory|\Illuminate\Contracts\View\View
     * @throws Exception
     */
    public function details(BonusDepartment $bonusDepartment)
    {
        $bonusDepartment = $bonusDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $uuid = $bonusDepartment->uuid;

        $bonuses = UserBonus::with("user.employeeStatusJoining", "officeDivision", "department", "designation")
            ->where("office_division_id", $bonusDepartment->office_division_id)
            ->where("department_id", $bonusDepartment->department_id)
            ->where("month", $bonusDepartment->month)
            ->where("year", $bonusDepartment->year)
            ->where("bonus_department_id", $bonusDepartment->id)
            ->get();

        $view = "user-bonus.view-generated-bonus";

        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $departmentIds = $filter_obj->getDepartmentIds();
        $departmentIds = is_object($departmentIds) ? $departmentIds->toArray() : $departmentIds;

        return \view($view, compact('bonuses', 'bonusDepartment', 'uuid', 'divisionIds', 'departmentIds'));
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

            BonusDepartment::uuid($request->input("uuid"))->update([
                "divisional_approval_status" => $request->input("divisional_status") === "approved" ? BonusDepartment::STATUS_APPROVED : BonusDepartment::STATUS_REJECTED,
                "divisional_approval_by" => $userId,
                "divisional_approved_date" => date('Y-m-d H:i:s'),
                "divisional_remarks" => $request->input("reject_reason"),
            ]);

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

            BonusDepartment::uuid($request->input("uuid"))->update([
                "departmental_approval_status" => $request->input("departmental_status") === "approved" ? BonusDepartment::STATUS_APPROVED : BonusDepartment::STATUS_REJECTED,
                "departmental_approval_by" => $userId,
                "departmental_approved_date" => date('Y-m-d H:i:s'),
                "departmental_remarks" => $request->input("reject_reason"),
            ]);

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

            BonusDepartment::uuid($request->input("uuid"))->update([
                "hr_approval_status" => $request->input("hr_status") === "approved" ? BonusDepartment::STATUS_APPROVED : BonusDepartment::STATUS_REJECTED,
                "hr_approval_by" => $userId,
                "hr_approved_date" => date('Y-m-d H:i:s'),
                "hr_remarks" => $request->input("reject_reason"),
            ]);

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

            BonusDepartment::uuid($request->input("uuid"))->update([
                "accounts_approval_status" => $request->input("accounts_status") === "approved" ? BonusDepartment::STATUS_APPROVED : BonusDepartment::STATUS_REJECTED,
                "accounts_approval_by" => $userId,
                "accounts_approved_date" => date('Y-m-d H:i:s'),
                "accounts_remarks" => $request->input("reject_reason"),
            ]);

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

            BonusDepartment::uuid($request->input("uuid"))->update([
                "managerial_approval_status" => $request->input("managerial_status") === "approved" ? BonusDepartment::STATUS_APPROVED : BonusDepartment::STATUS_REJECTED,
                "managerial_approval_by" => $userId,
                "managerial_approved_date" => date('Y-m-d H:i:s'),
                "managerial_remarks" => $request->input("reject_reason"),
            ]);

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
     * @param BonusDepartment $bonusDepartment
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function bonusExport(Request $request, BonusDepartment $bonusDepartment)
    {
        $monthName = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $bonusDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $fileName = 'bonus-sheet-' . $bonusDepartment->load("department")->department->name . '-' . $monthName[$bonusDepartment->month] . '-' . $bonusDepartment->year;

        if ($request->input("type") === "Export CSV" and auth()->user()->can("Export Salary CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new BonusSheetExport([$bonusDepartment->id], $bonusDepartment->month, $bonusDepartment->year), $fileName);
        }

        if ($request->input("type") === "Export PDF" and auth()->user()->can("Export Salary PDF")) {
            $fileName .= '.pdf';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);

            $bonuses = UserBonus::with("user.employeeStatusJoining", "officeDivision", "department", "designation")
                ->where("office_division_id", $bonusDepartment->office_division_id)
                ->where("department_id", $bonusDepartment->department_id)
                ->where("month", $bonusDepartment->month)
                ->where("year", $bonusDepartment->year)
                ->where("bonus_department_id", $bonusDepartment->id)
                ->get();

            //return view('user-bonus.bonus_export_pdf', compact("bonuses", 'bonusDepartment'));
            return PDF::loadView('user-bonus.bonus_export_pdf', compact("bonuses", 'bonusDepartment'))->setPaper('a4', 'landscape')->download($fileName);
        }

        # Bank Statement PDF
        if ($request->input("type") === "Bank Statement PDF" and auth()->user()->can("Export Salary Bank Statement PDF")) {
            $fileName .= '.pdf';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);

            $bonuses = UserBonus::with("user.currentBank")
                ->where("office_division_id", $bonusDepartment->office_division_id)
                ->where("department_id", $bonusDepartment->department_id)
                ->where("month", $bonusDepartment->month)
                ->where("year", $bonusDepartment->year)
                ->where("bonus_department_id", $bonusDepartment->id)
                ->get();

            return PDF::loadView('user-bonus.bonus_export_bank_statement_pdf', compact("bonuses"))->download($fileName);
        }

        # Bank Statement CSV
        if ($request->input("type") === "Bank Statement CSV" and auth()->user()->can("Export Salary Bank Statement CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new BonusSheetBankExport([$bonusDepartment->id], $bonusDepartment->month, $bonusDepartment->year), $fileName);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function regenerate(Request $request)
    {
        try {
            DB::beginTransaction();

            $bonusDepartment = BonusDepartment::uuid($request->input("bonus_department_id"))->first();

            $bonusQuery = UserBonus::where("office_division_id", $bonusDepartment->office_division_id)
                ->where("department_id", $bonusDepartment->department_id)
                ->where("month", $bonusDepartment->month)
                ->where("year", $bonusDepartment->year)
                ->where("bonus_id", $bonusDepartment->bonus_id)
                ->where("bonus_department_id", $bonusDepartment->id);

            $bonusUsers = $bonusQuery->pluck('user_id')->toArray();

            $bonusQuery->delete();

            $generateBonusRequest = [
                "bonus_id" => $bonusDepartment->bonus_id,
                "office_division_id" => $bonusDepartment->office_division_id,
                "department_id" => [$bonusDepartment->department_id],
                "month_and_year" => $bonusDepartment->month . "-" . $bonusDepartment->year,
                "user_id" => $bonusUsers,
            ];

            if ($request->has("tax")) {
                $generateBonusRequest = array_merge($generateBonusRequest, [
                    "tax" => "on"
                ]);
            }

            $generateBonusRequest = new RequestUserBonus($generateBonusRequest);
            $bonusDepartment->delete();

            $this->store($generateBonusRequest, true);

            DB::commit();

            session()->flash('message', 'Bonus Regenerated Successfully!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }

        return redirect()->back();
    }
}
