<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Bank;
use App\Models\Branch;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\Institute;
use App\Models\LeaveRequest;
use App\Models\OfficeDivision;
use App\Models\Roster;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class DashboardSupervisorController extends Controller
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
     * Employee(s) belongs to department under the supervisor
     *
     * @return Factory|View
     */
    public function employees()
    {
        $data = $this->data();
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $data['filterToEmployee'] = 'supervisor';
        return view("employee.index", compact("supervisorDepartmentIds", "data"));
    }

    /**
     * In Leave (Today)
     *
     * @return Factory|View
     */
    public function inLeaveToday()
    {
        /*$data = $this->data();
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->pluck("id");

        $today = date('Y-m-d', strtotime("today"));
        $inLeaveToday = LeaveRequest::whereDate("from_date", "<=", $today)
            ->whereDate("to_date", ">=", $today)
            ->whereIn("user_id", $employeesInThisDepartment)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->orderByDesc("id")
            ->distinct()
            ->pluck("user_id");

        $items = User::active()->whereIn("id", $inLeaveToday)->orderBydesc("id")->paginate(\Functions::getPaginate());*/
        $routeUrl = 'dashboard-supervisor.datatableInLeaveToday';
        return view("dashboard-details.in-leave-today", compact('routeUrl'));
    }

    public function getDatatableInLeaveToday()
    {
        $today = date('Y-m-d', strtotime("today"));
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$today'";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->pluck("id");
        $inLeaveToday = LeaveRequest::whereDate("from_date", "<=", $today)
            ->whereDate("to_date", ">=", $today)
            ->whereIn("user_id", $employeesInThisDepartment)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->orderByDesc("id")
            ->distinct()
            ->pluck("user_id");
        $items = User::with([
            "currentPromotion" => function ($query) {
                $query->with("department", "officeDivision", "designation");
            },
            "currentPromotion.officeDivision",
            "currentPromotion.department",
            "currentPromotion.designation",
            "currentStatus",
        ])->join("promotions", function ($join) {
            $join->on('promotions.user_id', 'users.id');
            $join->on('promotions.id', DB::raw("(select max(id) from promotions p where p.user_id = users.id limit 1)"));
        })->active()->whereIn("users.id", $inLeaveToday)->orderBydesc("users.id")->groupBy('users.id')->select([
            "users.id",
            "name",
            "email",
            "phone",
            "fingerprint_no",
            "status",
            "photo",
            "office_division_id",
            "department_id",
            "designation_id",
        ]);
        return DataTables::eloquent($items)
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists(asset("photo/" . $item->fingerprint_no . ".jpg")) ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo'])
            ->toJson();
    }

    /**
     * In Leave (Tomorrow)
     *
     * @return Factory|View
     */
    public function inLeaveTomorrow()
    {
        /*$data = $this->data();
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds,$sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->pluck("id");

        $tomorrow = date('Y-m-d', strtotime("tomorrow"));
        $inLeaveTomorrow = LeaveRequest::whereDate("from_date", "<=", $tomorrow)
            ->whereDate("to_date", ">=", $tomorrow)
            ->whereIn("user_id", $employeesInThisDepartment)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->orderByDesc("id")
            ->distinct()
            ->pluck("user_id");

        $items = User::active()->whereIn("id", $inLeaveTomorrow)->orderBydesc("id")->paginate(\Functions::getPaginate());
        return view("dashboard-details.in-leave-tomorrow", compact("items", "data"));*/

        $routeUrl = 'dashboard-supervisor.datatableInLeaveTomorrow';
        return view("dashboard-details.in-leave-tomorrow", compact('routeUrl'));
    }

    public function getDatatableInLeaveTomorrow()
    {
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $tomorrow = date('Y-m-d', strtotime("tomorrow"));
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$tomorrow'";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->pluck("id");
        $inLeaveTomorrow = LeaveRequest::whereDate("from_date", "<=", $tomorrow)
            ->whereDate("to_date", ">=", $tomorrow)
            ->whereIn("user_id", $employeesInThisDepartment)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->orderByDesc("id")
            ->distinct()
            ->pluck("user_id");

        $items = User::with([
            "currentPromotion" => function ($query) {
                $query->with("department", "officeDivision", "designation");
            },
            "currentPromotion.officeDivision",
            "currentPromotion.department",
            "currentPromotion.designation",
            "currentStatus",
        ])->join("promotions", function ($join) {
            $join->on('promotions.user_id', 'users.id');
            $join->on('promotions.id', DB::raw("(select max(id) from promotions p where p.user_id = users.id limit 1)"));
        })->active()->whereIn("users.id", $inLeaveTomorrow)->orderBydesc("users.id")->groupBy('users.id')->select([
            "users.id",
            "name",
            "email",
            "phone",
            "fingerprint_no",
            "status",
            "photo",
            "office_division_id",
            "department_id",
            "designation_id",
        ]);
        return DataTables::eloquent($items)
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists(asset("photo/" . $item->fingerprint_no . ".jpg")) ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo'])
            ->toJson();
    }

    /**
     * @return Factory|Builder[]|Collection|View
     */
    public function todayPresent()
    {
        /*$supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds,$sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->get();

        $todayAttendance = $employeesInThisDepartment->filter(function ($item) {
            if (!is_null($item->timeInToday)) return $item;
        })->values();
        $attendances = $this->getSortedAttendances($todayAttendance, $employeesInThisDepartment, route("dashboard-supervisor.todayPresent"));
        return view("dashboard-details.today-present-late", compact("attendances"));*/

        $currentState = 'present';
        $routeUrl = 'dashboard-supervisor.datatableTodayPresentLate';
        return view("dashboard-details.today-present-late", compact('currentState', 'routeUrl'));
    }

    public function getDatatableTodayPresentLate()
    {
        $currentState = \request('currentState');
        if (!empty($currentState) && $currentState == 'present') {
            return $this->___getDatatableTodayPresent();
        } elseif (!empty($currentState) && $currentState == 'absent') {
            return $this->___getDatatableTodayLate();
        }
    }

    private function ___getDatatableTodayPresent()
    {
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $today = date('Y-m-d');
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$today'";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->get();

        $todayAttendance = $employeesInThisDepartment->filter(function ($item) {
            if (!is_null($item->timeInToday)) return $item;
        })->values();
        $items = $this->getSortedAttendancesYajra($todayAttendance, $employeesInThisDepartment);
        return Datatables::of($items)
            ->addColumn('time_in', function ($item) {
                $timeIn = '---';
                if (!is_null($item->timeInToday->punch_time)) {
                    $timeIn = date('h:i:s a', strtotime($item->timeInToday->punch_time));
                }
                return $timeIn;
            })
            ->addColumn('time_out', function ($item) {
                $timeOut = '---';
                if ($item->timeInToday->punch_time != $item->timeOutToday->punch_time) {
                    $timeOut = date('h:i:s a', strtotime($item->timeOutToday->punch_time));
                }
                return $timeOut;
            })
            ->addColumn('get_date', function ($item) {
                $date = '---';
                if (!is_null($item->timeInToday->punch_time)) {
                    $date = date('M d, Y', strtotime($item->timeInToday->punch_time));
                }
                return $date;
            })
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists(asset("photo/" . $item->fingerprint_no . ".jpg")) ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo', 'time_in', 'time_out', 'get_date'])
            ->make(true);
    }


    /**
     * @return Factory|Builder[]|Collection|View
     */
    public function todayAbsent()
    {
        /*$supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })
            ->active()
            ->get();
        $todayAbsent = $employeesInThisDepartment->filter(function ($item) use ($employeesInThisDepartment) {
            if (is_null($item->timeInToday)) return $item;
        })->values();
        $attendances = \Functions::customPaginate($todayAbsent, route("dashboard-supervisor.todayAbsent"));
        return view("dashboard-details.today-absent", compact("attendances"));*/
        $routeUrl = 'dashboard-supervisor.datatableTodayAbsent';
        return view("dashboard-details.today-absent", compact('routeUrl'));
    }

    public function getDatatableTodayAbsent()
    {
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $today = date('Y-m-d');
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$today'";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })
            ->active()
            ->get();
        $items = $employeesInThisDepartment->filter(function ($item) use ($employeesInThisDepartment) {
            if (is_null($item->timeInToday)) return $item;
        })->values();
        return Datatables::of($items)
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists(asset("photo/" . $item->fingerprint_no . ".jpg")) ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo'])
            ->make(true);
    }


    /**
     * @return Factory|View
     */
    public function todayLate()
    {
        /*$supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds,$sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->get();

        $lateToday = $employeesInThisDepartment->filter(function ($item) {
            if (!is_null($item->timeInToday)) {
                $lateCountTime = date("H:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("H:i:s", strtotime($item->timeInToday->punch_time));

                if (strtotime($todayTimeIn) > strtotime($lateCountTime)) return $item;
            }
        })->values();
        $attendances = $this->getSortedAttendances($lateToday, $employeesInThisDepartment, route("dashboard-admin.todayLate"));
        return view("dashboard-details.today-present-late", compact("attendances"));*/

        $currentState = 'absent';
        $routeUrl = 'dashboard-supervisor.datatableTodayPresentLate';
        return view("dashboard-details.today-present-late", compact('currentState', 'routeUrl'));
    }


    private function ___getDatatableTodayLate()
    {
        $supervisorDepartmentIds = $this->getDepartmentSupervisorIds();
        $today = date('Y-m-d');
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$today'";
        $employeesInThisDepartment = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($supervisorDepartmentIds, $sub) {
                return $query->where('id', '=', DB::raw("({$sub})"))->whereIn("department_id", $supervisorDepartmentIds);
            })->active()->get();

        $lateToday = $employeesInThisDepartment->filter(function ($item) {
            if (!is_null($item->timeInToday)) {
                $lateCountTime = date("H:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("H:i:s", strtotime($item->timeInToday->punch_time));

                $punchDate = date("Y-m-d", strtotime($item->timeInToday->punch_time));
                return Common::getEmployeeLateConsideredWithRosterHalfDays($todayTimeIn,$lateCountTime,$punchDate,$item);
            }
        })->values();
        $items = $this->getSortedAttendancesYajra($lateToday, $employeesInThisDepartment);
        return Datatables::of($items)
            ->addColumn('time_in', function ($item) {
                $timeIn = '---';
                if (!is_null($item->timeInToday->punch_time)) {
                    $timeIn = date('h:i:s a', strtotime($item->timeInToday->punch_time));
                }
                return $timeIn;
            })
            ->addColumn('time_out', function ($item) {
                $timeOut = '---';
                if ($item->timeInToday->punch_time != $item->timeOutToday->punch_time) {
                    $timeOut = date('h:i:s a', strtotime($item->timeOutToday->punch_time));
                }
                return $timeOut;
            })
            ->addColumn('get_date', function ($item) {
                $date = '---';
                if (!is_null($item->timeInToday->punch_time)) {
                    $date = date('M d, Y', strtotime($item->timeInToday->punch_time));
                }
                return $date;
            })
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists(asset("photo/" . $item->fingerprint_no . ".jpg")) ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo', 'time_in', 'time_out', 'get_date'])
            ->make(true);
    }


    /**
     * @param $attendances
     * @param $employees
     * @param $route
     * @return LengthAwarePaginator
     */
    protected function getSortedAttendances($attendances, $employees, $route)
    {
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
        $result = \Functions::customPaginate($result, $route);

        return $result;
    }

    protected function getSortedAttendancesYajra($attendances, $employees)
    {
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
        return $result;
    }

    /**
     * @return array
     */
    protected function data()
    {
        return array(
            "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
            "officeDivisions" => OfficeDivision::whereIn('id', FilterController::getDivisionIds())->get(),
            "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
            "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
        );
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
