<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveUnpaid;
use App\Models\Meal;
use App\Models\OfficeDivision;
use App\Models\OnlineAttendance;
use App\Models\Promotion;
use App\Models\PublicHoliday;
use App\Models\Roster;
use App\Models\Salary;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\UserLeave;
use App\Models\UserMeal;
use App\Models\WorkSlot;
use App\Models\ZKTeco\Attendance as ZKTeco;
use App\Models\ZKTeco\DailyAttendance;
use App\Models\ZKTeco\Employee as ZKTecoEmployee;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use function PHPUnit\Framework\isNull;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth", [
            'except' => [
                'dailyMealReport',
            ]
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $data = array(
            "reportToAdmin" => null,
            "reportToSupervisor" => null,
            "reportToEmployee" => null,
            "departments" => null,
            "onlineAttendanceInfo" => null,
        );

        try {
            $data["departments"] = Department::all();

            # Reports to Admin
            if (auth()->user()->can("Show Admin Dashboard")) {
                $data["reportToAdmin"] = $this->reportToAdmin($request);
            }

            # Reports to Supervisor
            if ((auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR]) || auth()->user()->hasRole([User::ROLE_SUPERVISOR])) && auth()->user()->can("Show Supervisor Dashboard")) {
                $data["reportToSupervisor"] = $this->reportToSupervisor();
            }

            # Employee Dashboard
            if (auth()->user()->can("Show Employee Dashboard")) {
                $data["reportToEmployee"] = $this->reportToEmployee();
                $data["onlineAttendanceInfo"] = $this->getOnlineAttendanceInfo();
            }

            $data["mealRequestEndTime"] = $this->getMealEndTime();
        } catch (Exception $exception) {
            $data["error"] = "Cannot connect to Attendance Server";
        }

        return view("home", compact("data"));
    }

    protected function getMealEndTime()
    {
        return optional(\App\Models\Setting::whereName('meal_request_end_time')->first())->value;
    }

    /**
     * @return array
     */
    protected function reportToAdmin($request)
    {
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        # Fetch all employees with necessary data
        $employees = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($sub) {
                return $query->where('id', '=', DB::raw("({$sub})"));
            })
            ->active()
            ->get();

        $totalEmployees = $employees->count();
        $ids = $employees->pluck("id");

        # In Leave (Today)
        $today = date('Y-m-d', strtotime("today"));
        $inLeaveToday = LeaveRequest::whereIn("user_id", $ids)
            ->whereDate("from_date", "<=", $today)
            ->whereDate("to_date", ">=", $today)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->count();

        # In Leave (Tomorrow)
        $tomorrow = date('Y-m-d', strtotime("tomorrow"));
        $inLeaveTomorrow = LeaveRequest::whereIn("user_id", $ids)
            ->whereDate("from_date", "<=", $tomorrow)
            ->whereDate("to_date", ">=", $tomorrow)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->count();

        # Present Today
        $todayAttendance = $employees->filter(function ($item) use ($employees) {
            if (!is_null($item->timeInToday)) return $item;
        })->values();
        $presentToday = $todayAttendance->count();

        # Absent Today
        $absentToday = $totalEmployees - $presentToday;

        # Late Today
        $lateToday = $employees->filter(function ($item) use ($employees) {
            if (!is_null($item->timeInToday)) {
                $lateCountTime = date("H:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("H:i:s", strtotime($item->timeInToday->punch_time));

                $punchDate = date("Y-m-d", strtotime($item->timeInToday->punch_time));
                return Common::getEmployeeLateConsideredWithRosterHalfDays($todayTimeIn,$lateCountTime,$punchDate,$item);
            }
        })->values()->count();

        # Leave Requests
        $leaveRequests = LeaveRequest::with("employee.currentPromotion.officeDivision", "employee.currentPromotion.department", "employee.currentPromotion.designation")
            ->whereStatus(LeaveRequest::STATUS_PENDING)
            ->orderByDesc("from_date")
            ->orderByDesc("id")
            ->get();

        # Attendances
        $attendances = $this->getSortedAttendances($todayAttendance, $employees, $request);

        return [
            "totalEmployees" => $totalEmployees,
            "inLeaveToday" => $inLeaveToday,
            "inLeaveTomorrow" => $inLeaveTomorrow,
            "presentToday" => $presentToday,
            "absentToday" => $absentToday,
            "lateToday" => $lateToday,
            "leaveRequests" => $leaveRequests,
            "attendances" => $attendances
        ];
    }

    /**
     * @param $attendances
     * @param $employees
     * @return LengthAwarePaginator
     */
    protected function getSortedAttendances($attendances, $employees, $request)
    {
        if ($request->has('department_id')) {
            $attendances = $attendances->filter(function ($query) use ($request) {
                return $query->currentPromotion->department_id == $request->input('department_id');
            });
        }

        $sorted = [];
        $attendances->map(function ($item, $key) use (&$sorted) {
            array_push($sorted, [
                "id" => $item->id,
                "emp_code" => $item->timeInToday->emp_code,
                "punch_time" => strtotime($item->timeInToday->punch_time)
            ]);
        });
        $data = collect($sorted);

        $sortedIds = $data->sortByDesc("punch_time")->pluck("emp_code")->values()->all();

        $result = [];
        foreach ($sortedIds as $id) {
            $employee = $employees->where("fingerprint_no", $id)->first();
            array_push($result, $employee);
        }
        $result = collect($result);
        $result = \Functions::customPaginate($result, route("home"));

        return $result;
    }

    /**
     * @return array
     */
    protected function reportToSupervisor()
    {
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        //dd($supervisorDepartmentIds);

        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";

        # Employee(s) belongs to department under the supervisor
        $employeesInDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })
            ->whereStatus(User::STATUS_ACTIVE)
            ->get();

        # Total Employees on this Department
        $totalEmployeesInDepartment = $employeesInDepartment->count();

        $employeeIdsInDepartment = $employeesInDepartment->pluck("id");
        $employeeFingerprintsInDepartment = $employeesInDepartment->pluck("fingerprint_no");

        # In Leave (Today)
        $today = date('Y-m-d', strtotime("today"));
        $inLeaveToday = LeaveRequest::whereIn("user_id", $employeeIdsInDepartment)
            ->whereDate("from_date", "<=", $today)
            ->whereDate("to_date", ">=", $today)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->count();

        # In Leave (Tomorrow)
        $tomorrow = date('Y-m-d', strtotime("tomorrow"));
        $inLeaveTomorrow = LeaveRequest::whereIn("user_id", $employeeIdsInDepartment)
            ->whereDate("from_date", "<=", $tomorrow)
            ->whereDate("to_date", ">=", $tomorrow)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->count();

        # Present Today
        $todayAttendance = $employeesInDepartment->filter(function ($item) use ($employeesInDepartment) {
            if (!is_null($item->timeInToday)) return $item;
        })->values();
        $presentToday = $todayAttendance->count();

        # Absent Today
        $absentToday = $totalEmployeesInDepartment - $presentToday;

        # Late Today
        $lateToday = $employeesInDepartment->filter(function ($item) use ($employeesInDepartment) {
            if (!is_null($item->timeInToday)) {

                $lateCountTime = date("H:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("H:i:s", strtotime($item->timeInToday->punch_time));

                $punchDate = date("Y-m-d", strtotime($item->timeInToday->punch_time));
                return Common::getEmployeeLateConsideredWithRosterHalfDays($todayTimeIn,$lateCountTime,$punchDate,$item);

            }
        })->values()->count();

        # Leave Requests

        $leaveRequests = LeaveRequest::with("employee.currentPromotion.officeDivision", "employee.currentPromotion.department", "employee.currentPromotion.designation")
            ->whereHas("employee.currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })
            ->whereStatus(LeaveRequest::STATUS_PENDING)
            ->orderByDesc("from_date")
            ->orderByDesc("id")
            ->get();

        # Attendances
        $attendances = $todayAttendance;

        return [
            "totalEmployeesInDepartment" => $totalEmployeesInDepartment,
            "inLeaveToday" => $inLeaveToday,
            "inLeaveTomorrow" => $inLeaveTomorrow,
            "presentToday" => $presentToday,
            "absentToday" => $absentToday,
            "lateToday" => $lateToday,
            "leaveRequests" => $leaveRequests,
            "attendances" => $attendances
        ];
    }


    public function getWorkSlot($today,$user_id,$currentPromotion){
        $approved_value = Roster::STATUS_APPROVED;
        $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$today' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $user_id OR `department_id` = $currentPromotion->department_id)";
        $roster_records = DB::select($sql_roster);
        $roster_department = [];
        $roster_user = [];
        foreach($roster_records as $each_roster){
            if($each_roster->user_id){
                $roster_user['work_slot_id'] = $each_roster->work_slot_id;
            }else{
                $roster_department['work_slot_id'] = $each_roster->work_slot_id;
            }
        }
        if($roster_user){
            $work_slot_id = $roster_user['work_slot_id'];
        }else{
            if($roster_department){
                $work_slot_id = $roster_department['work_slot_id'];
            }else{
                $work_slot_id = $currentPromotion->workslot_id;
            }
        }
        return WorkSlot::find($work_slot_id);
    }

    /**
     * @return array
     */
    protected function reportToEmployee()
    {
        # Report to Employee
        $currentPromotion = auth()->user()->currentPromotion;
        $totalLateThisMonth = 0;
        $today = date('Y-m-d');
        $today_arr = explode('-',$today);
        $firstDateOfMonth = $today_arr[0].'-'.$today_arr[1].'-'.'01';
        $daily_attendance_records = DailyAttendance::whereBetween('date',array($firstDateOfMonth,$today))
            ->where('user_id',auth()->user()->id)->get();
        $today_emp_attendance=[];
        foreach($daily_attendance_records as $daily_record){
            $totalLateThisMonth += $daily_record->is_late_final;
            $today_emp_attendance[$daily_record->date]['date'] = date("M jS, Y",strtotime($daily_record->date));
            $today_emp_attendance[$daily_record->date]['time_in'] = !empty($daily_record->time_in) ? date('h:i:s A',strtotime($daily_record->time_in)) : '';
            $today_emp_attendance[$daily_record->date]['time_out'] = !empty($daily_record->time_out) ? date('h:i:s A',strtotime($daily_record->time_out)) : '';
        }
        if(empty($today_emp_attendance[$today]['time_in'])){
            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
            $attendanceCountStartTime = date("H:i:s", strtotime($attendanceCountStartTime));
            $startDateTime = $today.' '.$attendanceCountStartTime;
            $today_attendance = ZKTeco::whereDate("punch_time", $today)
                ->where("punch_time", '>=', $startDateTime)
                ->where('emp_code','=',auth()->user()->fingerprint_no)
                ->orderBy("id")
                ->select("id", "emp_code", "punch_time")
                ->get();
            $today_emp_attendance[$today]['date'] = date("M jS, Y",strtotime($today));
            $today_emp_attendance[$today]['time_in'] = '';
            $today_emp_attendance[$today]['time_out'] = '';
            foreach($today_attendance as $attr){
                if(empty($today_emp_attendance[$today]['time_in'])){
                    $today_emp_attendance[$today]['punch_time'] = $attr->punch_time;
                    $today_emp_attendance[$today]['time_in'] = date('h:i:s A',strtotime($attr->punch_time));
                }else{
                    $today_emp_attendance[$today]['time_out'] = date('h:i:s A',strtotime($attr->punch_time));
                }
            }
            if(!empty($today_emp_attendance[$today]['time_in'])){
                $user_id = auth()->user()->id;
                $leave_record = LeaveRequest::where('status',LeaveRequest::STATUS_APPROVED)
                    ->where("user_id", "=", $user_id)
                    ->where("from_date", "<=", $today)
                    ->where("to_date", ">=", $today)
                    ->first();
                if($leave_record){
                    if($leave_record->half_day){
                        $work_slot = $this->getWorkSlot($today,$user_id,$currentPromotion);
                        $date_time=date_create($today_emp_attendance[$today]['time_in']);
                        $entry_time_in_sec = strtotime(date_format($date_time,"Y-m-d H:i:s"));
                        if(!$work_slot->is_flexible){
                            $start_time_in_sec = strtotime($today." ".$work_slot->start_time);
                            $late_time_in_sec = strtotime($today." ".$work_slot->late_count_time);
                            if($start_time_in_sec>$late_time_in_sec){
                                $endDate = date('Y-m-d', strtotime('+1 day', strtotime($today)));
                                $late_time_in_sec = strtotime($endDate." ".$work_slot->late_count_time);
                            }
                            if($leave_record->half_day_slot==1){
                                $half_leave_end = date("H:i", strtotime($leave_record->leave_end_time));
                                $half_leave_end_in_sec = strtotime($today.' '.$half_leave_end.':00');
                                $day_start_time_in_sec = $start_time_in_sec;
                                $buffer = ($late_time_in_sec-$day_start_time_in_sec);
                                if($buffer>0){
                                    $buffer = $buffer/2;
                                }
                                if(($half_leave_end_in_sec+$buffer)<$entry_time_in_sec){
                                    $totalLateThisMonth++;
                                }
                            }else{
                                if($late_time_in_sec<$entry_time_in_sec){
                                    $totalLateThisMonth++;
                                }
                            }
                        }
                    }
                }else{
                    $work_slot = $this->getWorkSlot($today,$user_id,$currentPromotion);
                    $date_time=date_create($today_emp_attendance[$today]['time_in']);
                    $entry_time_in_sec = strtotime(date_format($date_time,"Y-m-d H:i:s"));
                    if(!$work_slot->is_flexible){
                        $start_time_in_sec = strtotime($today." ".$work_slot->start_time);
                        $late_time_in_sec = strtotime($today." ".$work_slot->late_count_time);
                        if($start_time_in_sec>$late_time_in_sec){
                            $endDate = date('Y-m-d', strtotime('+1 day', strtotime($today)));
                            $late_time_in_sec = strtotime($endDate." ".$work_slot->late_count_time);
                        }
                        if($late_time_in_sec<$entry_time_in_sec){
                            $totalLateThisMonth++;
                        }
                    }
                }
            }
        }
        $upcomingHolidays = PublicHoliday::with("holiday")
            ->whereDate("from_date", ">=", today())
            ->orderBy("from_date", "asc")
            ->take(2)
            ->get();

        $leaveTotal = LeaveAllocation::with("leaveAllocationDetails")
            ->whereOfficeDivisionId($currentPromotion->office_division_id)
            ->whereDepartmentId($currentPromotion->department_id)
            ->where("year", date("Y"))
            ->orderByDesc("id")
            ->first();

        // $leaveTotal = !is_null($leaveTotal) ? $leaveTotal->leaveAllocationDetails->pluck("total_days")->sum() : 0;
        $leaveRequests = LeaveRequest::with("leaveType")->whereUserId(auth()->user()->id)->orderByDesc("from_date")->get();
        /*$leaveConsumed = (int)$leaveRequests->filter(function ($item) {
            if ($item->status == LeaveRequest::STATUS_APPROVED) return $item;
        })->pluck("number_of_days")->sum();*/

        // TODO: Through an Error while having no data on User Leave
        $userLeave = UserLeave::where("user_id", auth()->user()->id)->where("year", date("Y"))->first();
        $leaveTotal = $userLeave->total_initial_leave ?? 0;
        $leaveLeft = $userLeave->total_leaves ?? 0;

        $leaveConsumed = $leaveTotal - $leaveLeft;

        $salary = Salary::with("user", "officeDivision", "department")
            ->whereUserId(auth()->user()->id)
            // ->where("status", Salary::STATUS_PAID)
            ->orderByDesc("id")
            ->take(3)
            ->get();

        $bonus = UserBonus::where("user_id", auth()->user()->id)/*->where("status", Bonus::STATUS_PAID)*/->orderByDesc("id")->take(2)->get();

        return [
            "totalLateThisMonth" => $totalLateThisMonth,
            "upcomingHolidays" => $upcomingHolidays,
            "leaveTotal" => $leaveTotal,
            "leaveConsumed" => $leaveConsumed,
            "leaveLeft" => $leaveLeft,
            "salary" => $salary,
            "bonus" => $bonus,
            "today_emp_attendance" => $today_emp_attendance,
            "today" => $today,
            "leaveRequests" => $leaveRequests,
            "unpaidLeaves" => $this->unpaidLeaveTotal(),
        ];
    }

    /**
     * @return array
     */
    protected function getOnlineAttendanceInfo()
    {
        # Report to Employee
        $currentPromotion = auth()->user()->currentPromotion;

        $lateCountTime = date("H:i:s", strtotime($currentPromotion->workSlot->late_count_time));

        $attendance = OnlineAttendance::with("todayTimeIn", "todayTimeOut", "timeInThisMonth", "timeOutThisMonth")
            ->whereUserId(auth()->id())
            ->select("id", "user_id", "time_in", "time_out")
            ->first();

        /*$totalLateThisMonth = 0;
        if (!is_null($attendance)) {
            $totalLateThisMonth = $attendance->timeInThisMonth->filter(function ($item) use ($lateCountTime) {
                $punchTime = date("H:i:s", strtotime($item->punch_time));
                if ($punchTime > $lateCountTime) return $item;
            })->count();
        }

        $upcomingHolidays = PublicHoliday::with("holiday")
            ->whereDate("from_date", ">=", today())
            ->orderBy("from_date", "asc")
            ->take(2)
            ->get();

        $leaveTotal = LeaveAllocation::with("leaveAllocationDetails")
            ->whereOfficeDivisionId($currentPromotion->office_division_id)
            ->whereDepartmentId($currentPromotion->department_id)
            ->where("year", date("Y"))
            ->orderByDesc("id")
            ->first();

        // TODO<S Ahmed Naim>: Remove this while not necessary
        $leaveRequests = LeaveRequest::with("leaveType")->whereUserId(auth()->user()->id)->get();

        // TODO: Through an Error while having no data on User Leave
        $userLeave = UserLeave::where("user_id", auth()->user()->id)->where("year", date("Y"))->first();
        $leaveTotal = $userLeave->total_initial_leave ?? 0;
        $leaveLeft = $userLeave->total_leaves ?? 0;

        $leaveConsumed = $leaveTotal - $leaveLeft;

        $salary = Salary::with("user", "officeDivision", "department")
            ->whereUserId(auth()->user()->id)
            ->orderByDesc("id")
            ->take(3)
            ->get();
        $bonus = UserBonus::where("user_id", auth()->user()->id)->where("status", Bonus::STATUS_PAID)->orderByDesc("id")->take(2)->get();*/

        return [
            "timeInToday" => $attendance->todayTimeIn->time_in ?? null,
            "timeOutToday" => !is_null($attendance) && !is_null($attendance->todayTimeOut) ? $attendance->todayTimeOut->time_out : null,
            /*"totalLateThisMonth" => $totalLateThisMonth,
            "upcomingHolidays" => $upcomingHolidays,
            "leaveTotal" => $leaveTotal,
            "leaveConsumed" => $leaveConsumed,
            "leaveLeft" => $leaveLeft,
            "salary" => $salary,
            "attendances" => $attendance,
            "leaveRequests" => $leaveRequests,
            "unpaidLeaves" => $this->unpaidLeaveTotal(),*/
        ];
    }

    /**
     *
     *
     * @return Renderable
     */
    public function temporary()
    {
        $totalEmployee = User::whereStatus(User::STATUS_ACTIVE)->count();
        $presentToday = ZKTeco::whereDate("punch_time", today())->count();
        $absentToday = $totalEmployee - $presentToday;

        $data = array(
            "totalEmployee" => $totalEmployee,
            "departments" => Department::count(),
            "attendances" => $this->getTodayAttendance(),
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get(),
            "presentToday" => $presentToday,
            "absentToday" => $absentToday,
            "unpaidLeaves" => $this->unpaidLeaveTotal(),
        );

        return view('home-temporary', compact('data'));
    }

    /**
     * @return mixed
     */
    protected function unpaidLeaveTotal()
    {
        $authUser = auth()->user();
        $last_salary_date = "1947-01-01";
        $last_salary = Salary::where('user_id', $authUser->id)->select(['month', 'year'])->orderBy('month', 'DESC')->orderBy('year', 'DESC')->first();
        if ($last_salary) {
            $last_salary_date = $last_salary->year . "-" . $last_salary->month . "-01";
            $last_salary_date = date("Y-m-t", strtotime($last_salary_date));
        }
        return LeaveUnpaid::whereUserId($authUser->id)
            ->where("status", LeaveUnpaid::STATUS_ACTIVE)
            ->where('leave_date', '>', $last_salary_date)
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * @return Builder[]|Collection
     */
    protected function getAttendanceData()
    {
        $query = Attendance::with("user", "department")
            ->whereDay('created_at', now()->day)
            ->orderBy("id", "desc")
            ->groupBy('attendances.user_id')
            ->groupBy('attendances.created_at')
            ->select("id", "user_id", "department_id", "log_time", "device_id", "created_at");

        if (auth()->user()->isSupervisor() === true) {
            $query = $query->where("department_id", auth()->user()->load("currentPromotion")->department_id);
        } elseif (auth()->user()->isOrdinaryEmployee() === true) {
            $query = $query->where("user_id", auth()->user()->id);
        }

        return $query->distinct('created_at')->orderBy('created_at', 'desc')->take(10)->get();
    }

    /**
     * @return array
     */
    public function getTodayAttendance()
    {
        $employees = ZKTeco::whereDate("punch_time", today())->orderByDesc("id")->distinct()->pluck("emp_code")->take(10);

        $attendances = array();
        foreach ($employees as $employee) {
            $data = ZKTecoEmployee::with("timeIn", "timeOut")
                ->where("emp_code", $employee)
                ->select("id", "emp_code", "first_name", "last_name")
                ->first();

            $fingerPrint = User::where("fingerprint_no", $employee)->pluck("id")->first();
            $promotion = Promotion::with("user", "officeDivision", "department", "designation")
                ->where("user_id", $fingerPrint)
                ->orderByDesc("id")
                ->first();

            array_push($attendances, array(
                "data" => $data,
                "promotion" => $promotion
            ));
        }

        return $attendances;
    }

    /**
     * @return array
     */
    public function filterAttendanceData($data)
    {
        $employees = User::with("currentPromotion");

        if (isset($data["department_id"])) {
            $employees->whereHas("currentPromotion", function ($query) use ($data) {
                return $query->where("department_id", $data["department_id"]);
            });
        } elseif (isset($data["office_division_id"])) {
            $employees->whereHas("currentPromotion", function ($query) use ($data) {
                return $query->where("office_division_id", $data["office_division_id"]);
            });
        }
        $employeeIds = $employees->pluck("fingerprint_no");

        # Get data from ZKTeco
        $employees = ZKTeco::orderByDesc("id");
        if (isset($data["date"])) {
            $employees->whereDate("punch_time", $data["date"]);
        } else {
            $employees->whereDate("punch_time", today());
        }
        $employees = $employees->whereIn("emp_code", $employeeIds)->distinct()->pluck("emp_code");


        $attendances = array();
        foreach ($employees as $employee) {
            $data = ZKTecoEmployee::with("timeIn", "timeOut")
                ->where("emp_code", $employee)
                ->select("id", "emp_code", "first_name", "last_name")
                ->first();

            if (!isset($data)) continue;

            $fingerPrint = User::where("fingerprint_no", $employee)->pluck("id")->first();
            $promotion = Promotion::with("user", "officeDivision", "department", "designation")
                ->where("user_id", $fingerPrint)
                ->orderByDesc("id")
                ->first();

            array_push($attendances, array(
                "data" => $data,
                "promotion" => $promotion
            ));
        }

        return $attendances;
    }

    /**
     * @param Request $request
     * @return Factory|View
     */
    public function filterAttendance(Request $request)
    {
        $totalEmployee = User::count();
        $presentToday = ZKTeco::whereDate("punch_time", today())->count();
        $absentToday = $totalEmployee - $presentToday;

        $data = array(
            "totalEmployee" => $totalEmployee,
            "departments" => Department::count(),
            "attendances" => $this->getTodayAttendance(),
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get(),
            "presentToday" => $presentToday,
            "absentToday" => $absentToday,
            "unpaidLeaves" => $this->unpaidLeaveTotal(),
        );

        return view('home', compact('data'));
    }

    /**
     * @return Factory|View
     */
    public function getActivityLog()
    {
        $activities = Activity::with('causer')->orderByDesc("id")->paginate(\Functions::getPaginate());
        return view('activity', compact('activities'));
    }

    /**
     * TODO: Remove this on production
     *
     * @return Factory|View
     */
    public function readme()
    {
        $tax = \App\Models\Tax::with("rules")->where("status", \App\Models\Tax::STATUS_ACTIVE)->first();
        return view("readme", compact("tax"));
    }

    /**
     * TODO: REMOVE THIS ON PRODUCTION AFTER BEING SYNC
     *
     * @return string
     */
    public function syncSupervisor()
    {
        try {
            $supervisors = \App\Models\Supervisor::get();
            foreach ($supervisors as $supervisor) {
                Promotion::whereUserId($supervisor->user_id)->update(array("supervised_by" => $supervisor->supervised_by));
            }
        } catch (\Exception $exception) {
            //
        }

        return 0;
    }

    public function dailyMealReport()
    {
        $today = Carbon::today()->toDateString();

        $totalMealConsumerToday = UserMeal::whereStatus(1)->whereDate('date', '=', $today)->get()->count();

        $mealNotConsumerToday = UserMeal::whereStatus(0)->whereDate('date', '=', $today)->pluck('user_id')->toArray();

        $activeMealConsumersNotTakingMeal = Meal::active()->whereIn('user_id', $mealNotConsumerToday)->get()->load('employee');

        return view('daily-user-meal-report', compact('totalMealConsumerToday', 'activeMealConsumersNotTakingMeal'));
    }

    /**
     * @return array
     */
    protected function getDepartmentSupervisorIds()
    {
        $divisionSupervisor = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->orderByDesc("id")->pluck("office_division_id")->toArray();
        $departmentSupervisor = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id")->toArray();
        $departmentIds = [];
        $getDepartmentIds = [];
        if (count($divisionSupervisor) > 0) {
            $departmentIds = Department::whereIn("office_division_id", $divisionSupervisor)->pluck("id")->toArray();
        }
        if (count($departmentSupervisor) > 0) {
            $getDepartmentIds = $departmentSupervisor;
        }
        $departmentIds = array_unique(array_merge($departmentIds, $getDepartmentIds));
        return $departmentIds;
    }
}
