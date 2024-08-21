<?php

namespace App\Http\Controllers;

use App\Http\Requests\attendance\RequestAttendance;
use App\Models\OnlineAttendance;
use App\Models\OfficeDivision;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Exception;

class OnlineAttendancesController extends Controller
{
    protected $viewPath = 'online-attendances';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * @return Factory|View
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")
                ->whereIn('id', FilterController::getDivisionIds())
                ->get()
        );

        if (request()->ajax()) {

            $attendances = OnlineAttendance::with(["employee.currentPromotion" => function ($query) {
                $query->with("officeDivision", "department");
            }, "appliedBy", "authorizedBy", "approvedBy"])
                ->select(
                    [
                        "id",
                        "uuid",
                        "user_id",
                        "date",
                        "time_in",
                        "time_out",
                        "status",
                        "applied_by",
                        "authorized_by",
                        "approved_by",
                        "created_at",
                        "authorized_date",
                        "approved_date"
                    ])
                ->addSelect(DB::raw('(CASE
                        WHEN status = "2" THEN "1"
                        WHEN status = "3" THEN "2"
                        WHEN status = "1" THEN "3"
                        ELSE "4"
                        END) AS status_value'));

            $attendances->orderBy('status_value')->orderBy("id", "desc");

            $attendances = $attendances->whereIn('user_id', FilterController::getEmployeeIds());

            if ($request->division_id && $request->division_id > 0) {

                $attendances->whereIn('user_id', FilterController::getEmployeeIds(1, "division", $request->division_id));
            }
            if ($request->department_id && $request->department_id > 0) {
                $attendances->whereIn('user_id', FilterController::getEmployeeIds(1, "department", $request->department_id));
            }

            if (isset($request->status) && $request->status != 'all') {

                $attendances->where('online_attendances.status', $request->status);
            }
            if ($request->employee_id && $request->employee_id > 0) {
                $attendances->where('user_id', $request->employee_id);
            }
            if ($request->employee_id_name && !empty($request->employee_id_name)) {

                $userId = User::where('status', User::STATUS_ACTIVE)
                    ->where('fingerprint_no', $request->employee_id_name)
                    ->orWhere('name', 'LIKE', '%' . $request->employee_id_name . '%')
                    ->pluck('id');

                $attendances->whereIn('user_id', $userId);
            }

            return datatables($attendances)
                ->addColumn('fingerprint_no', function ($attendance) {
                    return '<i id="row-' . $attendance->id . '"></i>' . $attendance->employee->fingerprint_no ?? '';
                })
                ->addColumn('employee_name', function ($attendance) {
                    return $attendance->employee->name ?? '';
                })
                ->addColumn('division_name', function ($attendance) {
                    return $attendance->employee->currentPromotion->officeDivision->name ?? '';
                })
                ->addColumn('department_name', function ($attendance) {
                    return $attendance->employee->currentPromotion->department->name ?? '';
                })
                ->addColumn('date', function ($attendance) {
                    return date('d-m-y', strtotime($attendance->date));
                })
                ->editColumn('time_id', function ($attendance) {
                    return date('d-m-y H:i:s', strtotime($attendance->time_in));
                })
                ->addColumn('time_out', function ($attendance) {
                    $timeOut = '';
                    if ($attendance->time_out) {
                        $timeOut = date('d-m-y H:i:s', strtotime($attendance->time_out));
                    }
                    return $timeOut;
                })
                ->editColumn('authorized_by', function ($attendance) {
                    $name = "";
                    if (isset($attendance->authorizedBy)) {
                        $name = $attendance->authorizedBy->fingerprint_no ?? "";
                        $name .= ' - ' . $attendance->authorizedBy->name ?? "";
                        if ($attendance->authorized_date) {
                            $name .= ' at ' . date("d-m-Y h:i A", strtotime($attendance->authorized_date));
                        }
                    }
                    return $name;
                })
                ->editColumn('approved_by', function ($attendance) {
                    $name = "";
                    if (isset($attendance->approvedBy)) {
                        $name = $attendance->approvedBy->fingerprint_no ?? "";
                        $name .= ' - ' . $attendance->approvedBy->name ?? "";
                        if ($attendance->approved_date) {
                            $name .= ' at ' . date("d-m-Y h:i A", strtotime($attendance->approved_date));
                        }
                    }
                    return $name;
                })
                ->editColumn('status', function ($attendance) {
                    if ($attendance->status == OnlineAttendance::APPROVED) {
                        return '<span class="badge badge-success">Approved</span>';
                    } elseif ($attendance->status == OnlineAttendance::AUTHORIZED) {
                        return '<span class="badge badge-info">Authorized</span>';
                    } elseif ($attendance->status == OnlineAttendance::REJECTED) {
                        return '<span class="badge badge-danger">Cancelled</span>';
                    } elseif ($attendance->status == OnlineAttendance::PENDING) {
                        return '<span class="badge badge-primary">Pending</span>';
                    }
                })
                ->addColumn('action', function ($attendance) use ($request) {
                    $html = '';

                    if (Auth::id() != $attendance->user_id) {
                        if ($attendance->status === OnlineAttendance::PENDING && auth()->user()->can("Online Attendance Authorized")) {
                            $html .= '<a data-rowid="' . $attendance->uuid . '" onclick="setCookieFilter(this)" title="Authorize" href="' . route('attendance.requested_online_attendances.edit', ['onlineAttendance' => $attendance->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                        } elseif ($attendance->status === OnlineAttendance::AUTHORIZED && auth()->user()->can("Online Attendance Approved")) {
                            if (Auth::id() != $attendance->user_id) {
                                $html .= '<a data-rowid="' . $attendance->uuid . '" onclick="setCookieFilter(this)" title="Approve" href="' . route('attendance.requested_online_attendances.edit', ['onlineAttendance' => $attendance->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                            }
                        }
                    }

                    return $html;
                })
                ->rawColumns(['fingerprint_no', 'employee_name', 'division_name', 'department_name', 'leave_type_name', 'from_to_date', 'applied_date', 'number_of_days', 'authorized_by', 'approved_by', 'status', 'action'])
                ->make(true);
        }
        return view($this->viewPath . ".index", compact('data'));
    }

    /**
     * @param OnlineAttendance $onlineAttendance
     * @return Factory|View
     */
    public function edit(OnlineAttendance $onlineAttendance)
    {
        $departmentIds = FilterController::getDepartmentIds();

        if (count($departmentIds) > 0) {
            if (!in_array($onlineAttendance->department_id, $departmentIds)) {
                session()->flash("type", "error");
                session()->flash("message", 'Permission Denied!');
                return \Redirect::route('attendance.requested_online_attendances.index');
            }
        }

        $data = array(
            "employees" => User::select("id", "name", "email")->get(),
            'input_filters' => request()->input('input_filters')
        );
        $getEmployeeInfos = User::with(["currentPromotion" => function ($query) {
            $query->with("officeDivision", "department");
        }])
            ->orderByDesc("id")
            ->select("id", "name", "email", "phone", "fingerprint_no", "status", "photo")
            ->where(['users.id' => $onlineAttendance->user_id])
            ->first();

        if($onlineAttendance->status == OnlineAttendance::APPROVED || $onlineAttendance->status == OnlineAttendance::REJECTED){
            session()->flash("type", "error");
            session()->flash("message", 'Permission Denied!');
            return \Redirect::route('attendance.requested_online_attendances.index');
        }

        return view($this->viewPath . ".edit", compact("onlineAttendance", "data", "getEmployeeInfos"));
    }

    public function storeOnlineAttendance(Request $request)
    {
        $today = date('Y-m-d');
        $dateNow = date('Y-m-d H:i:s');
        $userId = Auth::id();

        $onlineAttendanceInfo = \Functions::getOnlineAttendanceInfo();

        $checkedInToday = null;
        if ($onlineAttendanceInfo['late_checkout']) {
            $checkedInToday = OnlineAttendance::where(['user_id' => $userId, 'date' => date('Y-m-d', strtotime('-1 days')), ['status', '!=', OnlineAttendance::REJECTED]])->first();
        } else {
            $checkedInToday = OnlineAttendance::where(['user_id' => $userId, 'date' => $today, ['status', '!=', OnlineAttendance::REJECTED]])->first();
        }

        $currentPromotion = User::with("currentPromotion")->where("id", $userId)->first()->currentPromotion;

        DB::beginTransaction();
        try {

            /** Insert CheckIn Time **/
            if (!$checkedInToday) {

                if(!$onlineAttendanceInfo['checkin_time']){
                    session()->flash("type", "error");
                    session()->flash("message", "Check In Time has been expired!");
                    return redirect()->back();
                }

                $data = [
                    "uuid" => \Functions::getNewUuid(),
                    'user_id' => $userId,
                    'office_division_id' => $currentPromotion->office_division_id ?? null,
                    'department_id' => $currentPromotion->department_id ?? null,
                    'date' => $today,
                    'time_in' => $dateNow,
                    'status' => OnlineAttendance::PENDING,
                    'applied_by' => $userId,
                ];
                OnlineAttendance::create($data);
            }

            /** Insert/Update Checkout Time **/
            if ($checkedInToday) {
                $checkedInToday->update([
                    "time_out" => $dateNow,
                    "updated_by" => $userId
                ]);
            }

            session()->flash("message", "Online Attendance Submitted Successfully");

            DB::commit();
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());

            DB::rollBack();
        }

        return redirect()->back();
    }

    public function approve(Request $request, OnlineAttendance $onlineAttendance)
    {
        $error = false;
        $errorMsg = '';
        /** Check Appropriate Permissions **/
        $permissionDenied = false;
        if($onlineAttendance->status === OnlineAttendance::PENDING && (!auth()->user()->can("Online Attendance Authorized"))){
            $permissionDenied = true;
        }

        if($onlineAttendance->status === OnlineAttendance::AUTHORIZED && (!auth()->user()->can("Online Attendance Approved"))){
            $permissionDenied = true;
        }

        if ($onlineAttendance->status == OnlineAttendance::APPROVED || $onlineAttendance->status == OnlineAttendance::REJECTED) {
            $permissionDenied = true;
        }

        $departmentIds = FilterController::getDepartmentIds();

        if (count($departmentIds) > 0) {
            if (!in_array($onlineAttendance->department_id, $departmentIds)) {
                $permissionDenied = true;
            }
        }

        if($permissionDenied){
            session()->flash("type", "error");
            session()->flash("message", 'Permission Denied!');
            return redirect()->back();
        }

        /** Validate CheckIn and CheckOut Time **/
        $timeIn = date('Y-m-d H:i:s', strtotime($request->time_in));
        $timeOut = date('Y-m-d H:i:s', strtotime($request->time_out));

        $onlineAttendanceInfo = \Functions::getOnlineAttendanceInfo($timeIn);//dd($onlineAttendanceInfo['checkin_start_time']);

        if(date('Y-m-d', strtotime($timeIn)) != date('Y-m-d', strtotime($onlineAttendance->time_in))){
            $errorMsg = 'Check In Date must be '. date('Y-m-d', strtotime($onlineAttendance->time_in));
            session()->flash("type", "error");
            session()->flash("message", $errorMsg);
            return redirect()->back();
        }

        if(!$onlineAttendanceInfo['checkin_time']){
            $errorMsg = ' Check In Dae/Time must be between '. $onlineAttendanceInfo['checkin_start_time']. ' and '. $onlineAttendanceInfo['checkin_end_time'];
            session()->flash("type", "error");
            session()->flash("message", $errorMsg);
            return redirect()->back();
        }

        if(!($timeOut >= $onlineAttendanceInfo['checkin_start_time'] && $timeOut <= $onlineAttendanceInfo['late_checkout_end_time'])){
            $errorMsg .= ' Check Out Date/Time must be between '. $onlineAttendanceInfo['checkin_start_time']. ' and '. $onlineAttendanceInfo['late_checkout_end_time'];
            session()->flash("type", "error");
            session()->flash("message", $errorMsg);
            return redirect()->back();
        }

        if($timeOut < $timeIn){
            session()->flash("type", "error");
            session()->flash("message", "Check Out Time must be grater then Check In Time!");
            return redirect()->back();
        }


        $dateNow = date('Y-m-d H:i:s');
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $successMsg = '';
            $data = [
                "time_in" => $timeIn,
                "time_out" => $timeOut,
                "status" => $request->status
            ];
            if ($request->status == OnlineAttendance::AUTHORIZED) {
                $data['authorized_by'] = $userId;
                $data['authorized_date'] = $dateNow;
                $successMsg = 'Authorized';
            }

            if ($request->status == OnlineAttendance::APPROVED) {
                $data['approved_by'] = $userId;
                $data['approved_date'] = $dateNow;
                $successMsg = 'Approved';
            }

            if ($request->status == OnlineAttendance::REJECTED) {
                $data['updated_by'] = $userId;
                $successMsg = 'Rejected';
            }

            $onlineAttendance->update($data);

            if ($request->status == OnlineAttendance::APPROVED) {
                $requestAttendanceObj = new RequestAttendance();

                $requestAttendanceObj->merge(['time_in' => $data['time_in'], 'time_out' => $data['time_out'], 'user_id' => $onlineAttendance->user_id, 'emp_code' => $onlineAttendance->user->fingerprint_no]);

                $attendanceControllerObj = new AttendanceController();
                $attendanceControllerObj->storeDailyAttendance($requestAttendanceObj);
            }

            session()->flash("message", "Online Attendance " . $successMsg . " Successfully");

            DB::commit();
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());

            DB::rollBack();
        }

        return redirect()->route('attendance.requested_online_attendances.index');
    }
}
