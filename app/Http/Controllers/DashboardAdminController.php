<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Bank;
use App\Models\Branch;
use App\Models\Designation;
use App\Models\Institute;
use App\Models\LeaveRequest;
use App\Models\OfficeDivision;
use App\Models\Roster;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class DashboardAdminController extends Controller
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
        $data = array();

        # Fetch all employees with necessary data
        $employees = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
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
                $lateCountTime = date("h:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("h:i:s", strtotime($item->timeInToday->punch_time));

                if ($todayTimeIn > $lateCountTime) return $item;
            }
        })->values()->count();

        # Leave Requests
        $leaveRequests = LeaveRequest::with("employee.currentPromotion.officeDivision", "employee.currentPromotion.department", "employee.currentPromotion.designation")
            ->whereStatus(LeaveRequest::STATUS_PENDING)
            ->orderByDesc("id")
            ->get();

        # Attendances
        $attendances = $todayAttendance;

        $data["reportToAdmin"] = [
            "totalEmployees" => $totalEmployees,
            "inLeaveToday" => $inLeaveToday,
            "inLeaveTomorrow" => $inLeaveTomorrow,
            "presentToday" => $presentToday,
            "absentToday" => $absentToday,
            "lateToday" => $lateToday,
            "leaveRequests" => $leaveRequests,
            "attendances" => $attendances
        ];

        return view("dashboard.admin", compact("data"));
    }

    /**
     * Employee(s) belongs to department under the supervisor
     *
     * @return Factory|View
     */
    public function employees()
    {
        $data = $this->data();
        $data['status'] = 1;
        $data['filterToEmployee'] = 'admin';
        return view("employee.index", compact("data"));
    }


    /**
     * @return Factory|View
     */
    public function inLeaveToday()
    {
        $routeUrl = 'dashboard-admin.datatableInLeaveToday';
        return view("dashboard-details.in-leave-today", compact('routeUrl'));
    }

    public function getDatatableInLeaveToday()
    {
        $currentDate = date('Y-m-d', strtotime("today"));
        $inLeaveToday = LeaveRequest::whereDate("from_date", "<=", $currentDate)
            ->whereDate("to_date", ">=", $currentDate)
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
     * @return Factory|View
     */
    public function inLeaveTomorrow()
    {
        $routeUrl = 'dashboard-admin.datatableInLeaveTomorrow';
        return view("dashboard-details.in-leave-tomorrow", compact('routeUrl'));
    }

    public function getDatatableInLeaveTomorrow()
    {
        $data = $this->data();
        $tomorrow = date('Y-m-d', strtotime("tomorrow"));
        $inLeaveTomorrow = LeaveRequest::whereDate("from_date", "<=", $tomorrow)
            ->whereDate("to_date", ">=", $tomorrow)
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
     * @return Factory|View
     */
    public function todayPresent()
    {
        $currentState = 'present';
        $routeUrl = 'dashboard-admin.datatableTodayPresentLate';
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
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employees = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($sub) {
                return $query->where('id', '=', DB::raw("({$sub})"));
            })
            ->active()
            ->get();
        $todayAttendance = $employees->filter(function ($item) use ($employees) {
            if (!is_null($item->timeInToday)) return $item;
        })->values();

        $items = $this->getSortedAttendancesYajra($todayAttendance, $employees);
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
     * @return Factory|View
     */
    public function todayAbsent()
    {
        $routeUrl = 'dashboard-admin.datatableTodayAbsent';
        return view("dashboard-details.today-absent", compact('routeUrl'));
    }

    public function getDatatableTodayAbsent()
    {
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employees = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($sub) {
                return $query->where('id', '=', DB::raw("({$sub})"));
            })
            ->active()
            ->get();
        $items = $employees->filter(function ($item) use ($employees) {
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
        $currentState = 'absent';
        $routeUrl = 'dashboard-admin.datatableTodayPresentLate';
        return view("dashboard-details.today-present-late", compact('currentState', 'routeUrl'));
    }

    private function ___getDatatableTodayLate()
    {
        $sub = "SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id";
        $employees = User::with("currentPromotion.workSlot", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation", "timeInToday", "timeOutToday")
            ->whereHas("currentPromotion", function ($query) use ($sub) {
                return $query->where('id', '=', DB::raw("({$sub})"));
            })
            ->active()
            ->get();
        $lateToday = $employees->filter(function ($item) use ($employees) {
            if (!is_null($item->timeInToday)) {
                $lateCountTime = date("H:i:s", strtotime($item->currentPromotion->workSlot->late_count_time));
                $todayTimeIn = date("H:i:s", strtotime($item->timeInToday->punch_time));

                $punchDate = date("Y-m-d", strtotime($item->timeInToday->punch_time));
                return Common::getEmployeeLateConsideredWithRosterHalfDays($todayTimeIn,$lateCountTime,$punchDate,$item);

            }
        })->values();
        $items = $this->getSortedAttendancesYajra($lateToday, $employees);
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
     * @return array
     */
    protected function data()
    {
        return array(
            "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
            "officeDivisions" => OfficeDivision::get(),
            "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
            "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
            "designations" => Designation::orderBy("title", "asc")->select("id", "title")->limit(30)->get()
        );
    }
}
