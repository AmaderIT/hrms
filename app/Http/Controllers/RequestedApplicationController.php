<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\holidays\RequestManipulateHoliday;
use App\Models\AssignRelaxDay;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveUnpaid;
use App\Models\OfficeDivision;
use App\Models\PublicHoliday;
use App\Models\Roaster;
use App\Models\Roster;
use App\Models\Salary;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\WorkSlot;
use App\Models\ZKTeco\Attendance;
use App\Models\ZKTeco\DailyAttendance;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class RequestedApplicationController extends Controller
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

            $items = LeaveRequest::with(["employee.currentPromotion" => function ($query) {
                $query->with("officeDivision", "department");
            }, "leaveType", "authorizedBy", "appliedTo", "approvedBy"])
                ->select(
                    [
                        "id",
                        "uuid",
                        "user_id",
                        "leave_type_id",
                        "half_day",
                        "from_date",
                        "to_date",
                        "number_of_days",
                        "number_of_paid_days",
                        "number_of_unpaid_days",
                        "authorized_by",
                        "approved_by",
                        "status",
                        "purpose",
                        "created_at",
                        "authorized_date",
                        "approved_date"
                    ])
                ->addSelect(DB::raw('(CASE
                        WHEN status = "0" THEN "1"
                        WHEN status = "1" THEN "3"
                        WHEN status = "2" THEN "4"
                        ELSE "2"
                        END) AS status_value'));

            $items->having('status_value', '>', 0)->orderBy("status_value")->orderBy("id", "desc");

            $items = $items->whereIn('user_id', FilterController::getEmployeeIds());


            if ($request->division_id && $request->division_id > 0) {

                $items->whereIn('user_id', FilterController::getEmployeeIds(1, "division", $request->division_id));
            }
            if ($request->department_id && $request->department_id > 0) {
                $items->whereIn('user_id', FilterController::getEmployeeIds(1, "department", $request->department_id));
            }

            if (isset($request->status) && $request->status != 'all') {

                $items->where('leave_requests.status', $request->status);
            }
            if ($request->employee_id && $request->employee_id > 0) {
                $items->where('user_id', $request->employee_id);
            }
            if ($request->employee_id_name && !empty($request->employee_id_name)) {

                $userId = User::where('status', User::STATUS_ACTIVE)
                    ->where('fingerprint_no', $request->employee_id_name)
                    ->orWhere('name', 'LIKE', '%' . $request->employee_id_name . '%')
                    ->pluck('id');

                $items->whereIn('user_id', $userId);
            }

            return datatables($items)
                ->addColumn('fingerprint_no', function ($item) {
                    return '<i id="row-' . $item->id . '"></i>' . $item->employee->fingerprint_no;
                })
                ->addColumn('employee_name', function ($item) {
                    return $item->employee->name;
                })
                ->addColumn('division_name', function ($item) {
                    return $item->employee->currentPromotion->officeDivision->name;
                })
                ->addColumn('department_name', function ($item) {
                    return $item->employee->currentPromotion->department->name;
                })
                ->addColumn('leave_type_name', function ($item) {
                    return $item->leaveType->name;
                })
                ->addColumn('from_to_date', function ($item) {
                    return $item->from_date->format('d M,Y') . ' to ' . $item->to_date->format('d M,Y');
                })
                ->addColumn('applied_date', function ($item) {
                    return date('d M,Y', strtotime($item->created_at));
                })
                ->editColumn('number_of_days', function ($item) {
                    return $item->number_of_days;
                })
                ->editColumn('number_of_paid_days', function ($item) {


                    if (($item->status != LeaveRequest::STATUS_PENDING) && ($item->status != LeaveRequest::STATUS_REJECTED)) {
                        return $item->number_of_days - ($item->number_of_unpaid_days ?? 0);
                    } else {
                        return '-';
                    }
                })
                ->editColumn('number_of_unpaid_days', function ($item) {
                    if (($item->status != LeaveRequest::STATUS_PENDING) && ($item->status != LeaveRequest::STATUS_REJECTED)) {
                        return $item->number_of_unpaid_days ?? 0;
                    } else {
                        return '-';
                    }

                })
                ->editColumn('authorized_by', function ($item) {
                    $name = "";
                    if (isset($item->authorizedBy)) {
                        $name = $item->authorizedBy->fingerprint_no ?? "";
                        $name .= ' - ' . $item->authorizedBy->name ?? "";
                        if ($item->authorized_date) {
                            $name .= ' at ' . date("d-m-Y h:i A", strtotime($item->authorized_date));
                        }
                    }
                    return $name;
                })
                ->editColumn('approved_by', function ($item) {
                    $name = "";
                    if (isset($item->approvedBy)) {
                        $name = $item->approvedBy->fingerprint_no ?? "";
                        $name .= ' - ' . $item->approvedBy->name ?? "";
                        if ($item->approved_date) {
                            $name .= ' at ' . date("d-m-Y h:i A", strtotime($item->approved_date));
                        }
                    }
                    return $name;
                })
                ->editColumn('status', function ($item) {
                    if ($item->status == LeaveRequest::STATUS_APPROVED) {
                        return '<span class="badge badge-success">Approved</span>';
                    } elseif ($item->status == LeaveRequest::STATUS_AUTHORIZED) {
                        return '<span class="badge badge-info">Authorized</span>';
                    } elseif ($item->status == LeaveRequest::STATUS_REJECTED) {
                        return '<span class="badge badge-danger">Cancelled</span>';
                    } elseif ($item->status == LeaveRequest::STATUS_PENDING) {
                        return '<span class="badge badge-primary">Pending</span>';
                    }
                })
                ->addColumn('action', function ($item) use ($request) {
                    $html = '';
                    if (auth()->user()->can("Authorize Leave Requests")) {
                        if (Auth::id() != $item->user_id) {
                            if ($item->status === 0) {
                                $html .= '<a data-rowid="' . $item->uuid . '" onclick="setListScroll(this)" href="' . route('requested-application.edit', ['requestedApplication' => $item->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                            } elseif ($item->status === LeaveRequest::STATUS_AUTHORIZED) {
                                if (Auth::id() != $item->user_id) {
                                    $html .= '<a data-rowid="' . $item->uuid . '" onclick="setListScroll(this)" href="' . route('requested-application.edit', ['requestedApplication' => $item->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                                }
                            }
                        } else {
                            if ($item->status === 0) {
                                $html .= '<a data-rowid="' . $item->uuid . '" onclick="setListScroll(this)" href="' . route("requested-application.edit", ["requestedApplication" => $item->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                            }
                        }
                    }

                    if (auth()->user()->can('Delete Leave Application')) {
                        if (auth()->user()->can("Authorize Leave Requests")) {
                            if (Auth::id() != $item->user_id) {
                                $html .= '<a href="#" class="delete_link" data-id="' . $item->uuid . '" onclick="setListScroll(this)" data-href="' . route('requested-application.delete', ['requestedApplication' => $item->uuid]) . '">
                                <i class="fa fa-trash" style="color: red"></i>
                            </a>';
                            } else {
                                if ($item->status === 0 || $item->status == 2) {
                                    $html .= '<a href="#" class="delete_link" data-id="' . $item->uuid . '" onclick="setListScroll(this)" data-href="' . route('requested-application.delete', ['requestedApplication' => $item->uuid]) . '">
                                <i class="fa fa-trash" style="color: red"></i>
                            </a>';
                                }
                            }
                        }
                    }

                    // Special permission for HR to approve self leave application
                    if (auth()->user()->id == $item->user_id && $item->status === LeaveRequest::STATUS_AUTHORIZED && auth()->user()->can("Approve Leave Requests Self")) {
                        $html .= '<a data-rowid="' . $item->uuid . '" onclick="setListScroll(this)"  href="' . route('requested-application.edit', ['requestedApplication' => $item->uuid]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                    }

                    if ($item->status === LeaveRequest::STATUS_APPROVED && auth()->user()->can("Reverse Approved Leave")) {

                        $html .= '<a href="#" onclick="setListScroll(this)" title="Roll Back" class="rollback_link" data-rowid="' . $item->uuid . '"  data-href="' . route('requested-application.rollback', ['requestedApplication' => $item->uuid]) . '"><i class="fa fa-undo ml-1" style="color: #0a53be"></i></a>';
                    }

                    return $html;
                })
                ->rawColumns(['fingerprint_no', 'employee_name', 'division_name', 'department_name', 'leave_type_name', 'from_to_date', 'applied_date', 'number_of_days', 'authorized_by', 'approved_by', 'status', 'action'])
                ->make(true);
        }
        return view("requested-application.index", compact('data'));
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return JsonResponse
     */
    public function getDepartmentsByDivisionId(OfficeDivision $officeDivision)
    {
        if (auth()->user()->hasRole([User::ROLE_SUPERVISOR])) {
            $departments = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->where('office_division_id', '=', $officeDivision->id)->active()->get();
            $department_ids = [];
            foreach ($departments as $info) {
                $department_ids[] = $info->department_id;
            }
            $data = Department::select("id", "name", "office_division_id")->whereIn('id', $department_ids)->get();
        } elseif (auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $departmentIds = $this->getDepartmentSupervisorIds();
            $data = Department::select("id", "name", "office_division_id")->whereIn('id', $departmentIds)->get();
        } else {
            $data = Department::select("id", "name", "office_division_id")->where('office_division_id', '=', $officeDivision->id)->get();
        }
        return response()->json($data);
    }

    /**
     * @param LeaveRequest $requestedApplication
     * @return Factory|View
     */
    public function edit(LeaveRequest $requestedApplication, $room = null)
    {
        $data['requestedApplication'] = $requestedApplication;

        $getEmployeeInfos = User::with(["currentPromotion" => function ($query) {
            $query->with("officeDivision", "department");
        }])
            ->orderByDesc("id")
            ->select("id", "name", "email", "phone", "fingerprint_no", "status", "photo")
            ->where(['users.id' => $requestedApplication->user_id])
            ->first();

        return view("requested-application.edit", compact("requestedApplication", "data", "getEmployeeInfos", "room"));
    }

    /**
     * @param RequestManipulateHoliday $request
     * @param LeaveRequest $requestedApplication
     * @return RedirectResponse
     */
    public function manipulate(RequestManipulateHoliday $request, LeaveRequest $requestedApplication, $room = null)
    {
        try {
            DB::beginTransaction();
            # Check for duplicate entry
            $fromDate = Carbon::parse($request->input("from_date"));
            $toDate = Carbon::parse($request->input("to_date"));

            $case1 = LeaveRequest::whereUserId($requestedApplication->user_id)
                ->where('id', '<>', $requestedApplication->id)
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case2 = LeaveRequest::whereUserId($requestedApplication->user_id)
                ->where('id', '<>', $requestedApplication->id)
                ->where("from_date", ">=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", "<=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case3 = LeaveRequest::whereUserId($requestedApplication->user_id)
                ->where('id', '<>', $requestedApplication->id)
                ->where("from_date", ">=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case4 = LeaveRequest::whereUserId($requestedApplication->user_id)
                ->where('id', '<>', $requestedApplication->id)
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", "<=", date('Y-m-d', strtotime($toDate)))
                ->count();

            if ($case1 > 0 || $case2 > 0 || $case3 > 0 || $case4 > 0) {
                return redirect()->back()->withInput()->withErrors("Leave Application has already been applied for same date range.");

            }

            $total_leave_days = abs((strtotime($request->from_date) - strtotime($request->to_date)) / 86400) + 1;

            $relax_approved_value = AssignRelaxDay::APPROVAL_CONFIRMED;
            $sql_relax_check = "SELECT relax_day.id,relax_day.date,assign_relax_day.user_id FROM relax_day INNER JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id WHERE relax_day.`date` BETWEEN '$request->from_date' AND '$request->to_date' AND assign_relax_day.user_id=$requestedApplication->user_id AND relax_day.deleted_at IS NULL AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = $relax_approved_value";
            $relax_day_existance = DB::select($sql_relax_check);
            if ($total_leave_days < 2 && $relax_day_existance) {
                $date_relax = $relax_day_existance[0]->date;
                if (($request->from_date == $relax_day_existance[0]->date) || ($request->to_date == $relax_day_existance[0]->date)) {
                    return redirect()->back()->withInput()->withErrors("Leave applied with relax day ($date_relax) ! Please change the date fields and submit again!");
                }
            }


            if ($room == 'employee' && ($requestedApplication->status == LeaveRequest::STATUS_PENDING || $requestedApplication->status == LeaveRequest::STATUS_CANCEL)) {

                $request_data = $request->toArray();

                if ($requestedApplication->status == LeaveRequest::STATUS_CANCEL) {
                    $request_data['status'] = 0;
                    $request_data["authorized_by"] = null;
                    $request_data["authorized_date"] = null;
                    $request_data["approved_by"] = null;
                    $request_data["approved_date"] = null;
                    $request_data["number_of_paid_days"] = null;
                }
                $requestedApplication->update($request_data);

            } else {

                if ($requestedApplication->status == LeaveRequest::STATUS_PENDING && ($request->status == LeaveRequest::STATUS_APPROVED || $request->status == LeaveRequest::STATUS_PENDING)) {
                    DB::rollBack();
                    session()->flash("type", "error");
                    session()->flash("message", "Invalid Try!!");
                    return redirect()->back()->withInput();
                }
                if ($requestedApplication->status == LeaveRequest::STATUS_AUTHORIZED && ($request->status == LeaveRequest::STATUS_AUTHORIZED || $request->status == LeaveRequest::STATUS_PENDING)) {
                    DB::rollBack();
                    session()->flash("type", "error");
                    session()->flash("message", "Invalid Try!!");
                    return redirect()->back()->withInput();
                }
                $total_available_leave_balance = 0;
                $available_leaves = UserLeave::where('user_id', '=', $requestedApplication->user_id)->where('year', '=', date("Y", strtotime($requestedApplication->from_date)))->first();
                $leave_balance = json_decode($available_leaves->leaves);
                foreach ($leave_balance as $leave_type_balance) {
                    if ($leave_type_balance->leave_type_id == $request->leave_type_id) {
                        $total_available_leave_balance = $leave_type_balance->total_days;
                    }
                }
                $update_arr = $request->validated();
                if (isset($update_arr['authorized_by'])) {
                    $update_arr['authorized_by'] = auth()->user()->id;
                }
                if (isset($update_arr['approved_by'])) {
                    $update_arr['approved_by'] = auth()->user()->id;
                    $update_arr['approved_date'] = date('Y-m-d H:i:s');
                }
                if ($update_arr['status'] == LeaveRequest::STATUS_AUTHORIZED) {
                    if (!auth()->user()->can("Authorize Leave Requests")) {
                        return redirect()->back()->withInput()->withErrors("Unauthorized Activity");
                    }
                    $update_arr['authorized_date'] = date('Y-m-d H:i:s');
                }
                if ($update_arr['status'] == LeaveRequest::STATUS_APPROVED) {
                    if (!auth()->user()->can("Approve Leave Requests")) {
                        return redirect()->back()->withInput()->withErrors("Unauthorized Activity");
                    }
                    $update_arr['approved_date'] = date('Y-m-d H:i:s');
                }


                $relax_approved_value = AssignRelaxDay::APPROVAL_CONFIRMED;
                $sql_relax_check = "SELECT relax_day.id,relax_day.date,assign_relax_day.user_id FROM relax_day INNER JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id WHERE relax_day.`date` BETWEEN '$update_arr[from_date]' AND '$update_arr[to_date]' AND assign_relax_day.user_id=$requestedApplication->user_id AND relax_day.deleted_at IS NULL AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = $relax_approved_value";
                $relax_day_existance = DB::select($sql_relax_check);
                if ($total_leave_days < 2 && $relax_day_existance) {
                    $date_relax = $relax_day_existance[0]->date;
                    if (($update_arr['from_date'] == $relax_day_existance[0]->date) || ($update_arr['to_date'] == $relax_day_existance[0]->date)) {
                        return redirect()->back()->withInput()->withErrors("Leave applied with relax day ($date_relax) ! Please change the date fields and submit again!");
                    }
                }


                if (isset($request->half_day) && $request->half_day == 1) {
                    $update_arr['half_day'] = 1;
                    $update_arr['leave_start_time'] = $request->leave_start_time;
                    $update_arr['leave_end_time'] = $request->leave_end_time;
                    $update_arr['half_day_slot'] = $request->half_day_slot;
                    $total_leave_days = 0.5;
                } else {
                    $update_arr['half_day'] = 0;
                    $update_arr['leave_start_time'] = null;
                    $update_arr['leave_end_time'] = null;
                    $update_arr['half_day_slot'] = 0;
                }

                /**
                 * get total no of days by calculating from and to date
                 * set paid and unpaid days by checking leave type payment mode
                 * appending remarks with user id and date time
                 * when cancel then application is being tagged as re apply
                 */


                $leaveType = LeaveType::find($request->leave_type_id);
                if ($leaveType && $leaveType->is_paid == 0) {
                    $update_arr['number_of_paid_days'] = 0;
                    $update_arr['number_of_unpaid_days'] = $total_leave_days;
                } else {
                    $update_arr['number_of_paid_days'] = $total_leave_days;
                    $update_arr['number_of_unpaid_days'] = 0;
                }
                if ($request->remarks) {
                    $tag = " [Remarks By: " . \auth()->user()->name . "-" . \auth()->user()->fingerprint_no . ", " . date("d-m-Y") . "]";
                    $update_arr['remarks'] = $requestedApplication->remarks . "<br>" . $request->remarks . $tag;
                }
                if ($request->status == LeaveRequest::STATUS_CANCEL) {
                    $update_arr['is_reapply'] = 1;
                }


                $requestedApplication->update($update_arr);
                # Resolve unpaid leave
                $fromDate = Carbon::parse($request->input("from_date"));
                $toDate = Carbon::parse($request->input("to_date"));
                $leaveRequests = LeaveUnpaid::whereUserId($requestedApplication->user_id)->whereDateBetween($fromDate, $toDate)->get();
                if ($request->input("status") == LeaveRequest::STATUS_APPROVED) {
                    # Remove all data from Leave Unpaid which is generated by CRON JOB
                    $leaveRequests->each(function ($data) {
                        $data->delete();
                    });
                    # Sync User Leave Balance
                    $userLeave = UserLeave::where("user_id", $requestedApplication->user_id)
                        ->where("year", date("Y", strtotime($request->input("from_date"))))
                        ->first();
                    if (isset($userLeave)) {
                        $item = collect(json_decode($userLeave->leaves))->where("leave_type_id", $request->input("leave_type_id"))->first();
                        $totalDays = $item->total_days;
                        $balance = $totalDays - $request->input("number_of_days");
                        $userLeaveBalance = [];
                        foreach (json_decode($userLeave->leaves) as $leaves) {
                            if ($leaves->leave_type_id == $request->input("leave_type_id")) {
                                array_push($userLeaveBalance, [
                                    "leave_type_id" => $leaves->leave_type_id,
                                    "total_days" => ($balance < 0) ? 0 : $balance
                                ]);
                            } else {
                                array_push($userLeaveBalance, [
                                    "leave_type_id" => $leaves->leave_type_id,
                                    "total_days" => $leaves->total_days
                                ]);
                            }
                        }
                        $userLeaveData = [
                            "id" => $userLeave->id,
                            "user_id" => $userLeave->user_id,
                            "leaves" => json_encode($userLeaveBalance),
                            "total_leaves" => collect($userLeaveBalance)->sum("total_days"),
                            "year" => $userLeave->year,
                        ];

                        $userLeave->update($userLeaveData);
                    }
                    if (isset($update_arr['number_of_unpaid_days'])) {
                        $total_approved_days = $update_arr['number_of_days'] - $update_arr['number_of_unpaid_days'];
                    } else {
                        $total_approved_days = $update_arr['number_of_days'];
                    }
                    $total_unpaid_days = $update_arr['number_of_days'] - $total_approved_days;
                    $total_fetched_days = $total_approved_days + $total_unpaid_days;
                    $total_fetched_days = ceil($total_fetched_days);
                    $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.department_id, promotions.workslot_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$update_arr[from_date]' ) WHERE users.`status` = 1 AND users.`id` = $requestedApplication->user_id";
                    $promotion_record = DB::select($sql_promotion);
                    $attendance_record = DailyAttendance::whereBetween('date', array($update_arr['from_date'], $update_arr['to_date']))
                        ->where('user_id', '=', $requestedApplication->user_id)
                        ->limit($total_fetched_days)->orderBy('date')->get();
                    $formatted_attendance_record = [];
                    foreach ($attendance_record as $record) {
                        $formatted_attendance_record[$record->date] = $record;
                    }
                    $to_date = new DateTime($update_arr['to_date']);
                    $to_date->modify('+1 day');
                    $period = new DatePeriod(
                        new DateTime($update_arr['from_date']),
                        new DateInterval('P1D'),
                        new DateTime($to_date->format('Y-m-d'))
                    );
                    $update_daily_attendance = [];
                    foreach ($period as $key => $value) {
                        if ($total_fetched_days >= ($key + 1)) {
                            if (isset($formatted_attendance_record[$value->format('Y-m-d')])) {
                                $new_arr = [];
                                $new_arr['id'] = $formatted_attendance_record[$value->format('Y-m-d')]->id;
                                if ($total_approved_days >= 1) {
                                    $total_approved_days--;
                                    $new_arr['present_count'] = 0;
                                    $new_arr['is_late_final'] = 0;
                                    $new_arr['late_min_final'] = 0;
                                    $new_arr['absent_count'] = 0;
                                    $new_arr['leave_count'] = 1;
                                } else {
                                    if ($total_approved_days == 0.5) {
                                        if (is_null($formatted_attendance_record[$value->format('Y-m-d')]->time_in)) {
                                            $new_arr['present_count'] = 0;
                                            if ($formatted_attendance_record[$value->format('Y-m-d')]->is_relax_day || $formatted_attendance_record[$value->format('Y-m-d')]->is_public_holiday || $formatted_attendance_record[$value->format('Y-m-d')]->is_weekly_holiday) {
                                                $new_arr['absent_count'] = 0;
                                            } else {
                                                $new_arr['absent_count'] = 0.5;
                                            }
                                            $new_arr['leave_count'] = 0.5;
                                        } else {
                                            $new_arr['present_count'] = 0.5;
                                            $new_arr['absent_count'] = 0;
                                            $new_arr['leave_count'] = 0.5;
                                        }
                                        $total_approved_days = $total_approved_days - 0.5;
                                        if ($total_unpaid_days > 0) {
                                            $new_arr['present_count'] = 0;
                                            $new_arr['absent_count'] = 0.5;
                                            $total_unpaid_days = $total_unpaid_days - 0.5;
                                        }
                                        if ($new_arr['present_count'] == 0) {
                                            $new_arr['is_late_final'] = 0;
                                            $new_arr['late_min_final'] = 0;
                                        } else {
                                            $date_query = $formatted_attendance_record[$value->format('Y-m-d')]->date;
                                            $user_id = $formatted_attendance_record[$value->format('Y-m-d')]->user_id;
                                            $approved_value = Roster::STATUS_APPROVED;
                                            $department_id = $promotion_record[0]->department_id;
                                            $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date_query' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $user_id OR `department_id` = $department_id)";
                                            $check_roaster = DB::select($sql_roster);
                                            $roster_department = [];
                                            $roster_user = [];
                                            foreach ($check_roaster as $each_roster) {
                                                if ($each_roster->user_id) {
                                                    $roster_user['work_slot_id'] = $each_roster->work_slot_id;
                                                } else {
                                                    $roster_department['work_slot_id'] = $each_roster->work_slot_id;
                                                }
                                            }
                                            if ($roster_user) {
                                                $work_slot_id = $roster_user['work_slot_id'];
                                            } else {
                                                if ($roster_department) {
                                                    $work_slot_id = $roster_department['work_slot_id'];
                                                } else {
                                                    $work_slot_id = $promotion_record[0]->workslot_id;
                                                }
                                            }
                                            $work_slot = WorkSlot::find($work_slot_id);
                                            if ($formatted_attendance_record[$value->format('Y-m-d')]->is_late_day && !($work_slot->is_flexible)) {
                                                if (!is_null($update_arr['leave_start_time'])) {
                                                    if($update_arr['half_day_slot']==1){
                                                        $entry_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->time_in);
                                                        $day_start_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date." ".$work_slot->start_time);
                                                        $late_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date . ' ' . $work_slot->late_count_time);
                                                        $half_leave_end = date("H:i", strtotime($update_arr['leave_end_time']));
                                                        $half_leave_end_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date.' '.$half_leave_end.':00');
                                                        $buffer = ($late_time_in_sec-$day_start_time_in_sec);
                                                        if($buffer>0){
                                                            $buffer = (int) $buffer/2;
                                                        }
                                                        if(($half_leave_end_in_sec+$buffer)<$entry_time_in_sec){
                                                            $new_arr['is_late_final'] = true;
                                                            $late_mins = $entry_time_in_sec-($half_leave_end_in_sec+$buffer);
                                                            $new_arr['late_min_final'] = round(abs($late_mins) / 60,2);
                                                        }else{
                                                            $new_arr['is_late_final'] = false;
                                                            $new_arr['late_min_final'] = 0;
                                                        }
                                                    }else{
                                                        $new_arr['is_late_final'] = $formatted_attendance_record[$value->format('Y-m-d')]->is_late_day;
                                                        $new_arr['late_min_final'] = $formatted_attendance_record[$value->format('Y-m-d')]->late_in_min;
                                                    }
                                                } else {
                                                    $new_arr['is_late_final'] = false;
                                                    $new_arr['late_min_final'] = 0;
                                                }
                                            } else {
                                                $new_arr['is_late_final'] = false;
                                                $new_arr['late_min_final'] = 0;
                                            }
                                        }
                                    } else {
                                        if ($total_unpaid_days > 0) {
                                            if ($total_unpaid_days >= 1) {
                                                $new_arr['present_count'] = 0;
                                                $new_arr['is_late_final'] = 0;
                                                $new_arr['late_min_final'] = 0;
                                                $new_arr['absent_count'] = 1;
                                                $new_arr['leave_count'] = 0;
                                                $total_unpaid_days--;
                                            } else {
                                                if (is_null($formatted_attendance_record[$value->format('Y-m-d')]->time_in)) {
                                                    $new_arr['present_count'] = 0;
                                                    $new_arr['leave_count'] = 0;
                                                    if ($formatted_attendance_record[$value->format('Y-m-d')]->is_relax_day || $formatted_attendance_record[$value->format('Y-m-d')]->is_public_holiday || $formatted_attendance_record[$value->format('Y-m-d')]->is_weekly_holiday) {
                                                        $new_arr['absent_count'] = 0.5;
                                                    } else {
                                                        $new_arr['absent_count'] = 1;
                                                    }
                                                } else {
                                                    $new_arr['present_count'] = 0.5;
                                                    $new_arr['absent_count'] = 0.5;
                                                    $new_arr['leave_count'] = 0;
                                                    $date_query = $formatted_attendance_record[$value->format('Y-m-d')]->date;
                                                    $user_id = $formatted_attendance_record[$value->format('Y-m-d')]->user_id;
                                                    $approved_value = Roster::STATUS_APPROVED;
                                                    $department_id = $promotion_record[0]->department_id;
                                                    $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date_query' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $user_id OR `department_id` = $department_id)";
                                                    $check_roaster = DB::select($sql_roster);
                                                    $roster_department = [];
                                                    $roster_user = [];
                                                    foreach ($check_roaster as $each_roster) {
                                                        if ($each_roster->user_id) {
                                                            $roster_user['work_slot_id'] = $each_roster->work_slot_id;
                                                        } else {
                                                            $roster_department['work_slot_id'] = $each_roster->work_slot_id;
                                                        }
                                                    }
                                                    if ($roster_user) {
                                                        $work_slot_id = $roster_user['work_slot_id'];
                                                    } else {
                                                        if ($roster_department) {
                                                            $work_slot_id = $roster_department['work_slot_id'];
                                                        } else {
                                                            $work_slot_id = $promotion_record[0]->workslot_id;
                                                        }
                                                    }
                                                    $work_slot = WorkSlot::find($work_slot_id);
                                                    if ($formatted_attendance_record[$value->format('Y-m-d')]->is_late_day && !($work_slot->is_flexible)) {
                                                        if (!is_null($update_arr['leave_start_time'])) {
                                                            if($update_arr['half_day_slot']==1){
                                                                $entry_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->time_in);
                                                                $day_start_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date." ".$work_slot->start_time);
                                                                $late_time_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date . ' ' . $work_slot->late_count_time);
                                                                $half_leave_end = date("H:i", strtotime($update_arr['leave_end_time']));
                                                                $half_leave_end_in_sec = strtotime($formatted_attendance_record[$value->format('Y-m-d')]->date.' '.$half_leave_end.':00');
                                                                $buffer = ($late_time_in_sec-$day_start_time_in_sec);
                                                                if($buffer>0){
                                                                    $buffer = (int) $buffer/2;
                                                                }
                                                                if(($half_leave_end_in_sec+$buffer)<$entry_time_in_sec){
                                                                    $new_arr['is_late_final'] = true;
                                                                    $late_mins = $entry_time_in_sec-($half_leave_end_in_sec+$buffer);
                                                                    $new_arr['late_min_final'] = round(abs($late_mins) / 60,2);
                                                                }else{
                                                                    $new_arr['is_late_final'] = false;
                                                                    $new_arr['late_min_final'] = 0;
                                                                }
                                                            }
                                                        } else {
                                                            $new_arr['is_late_final'] = false;
                                                            $new_arr['late_min_final'] = 0;
                                                        }
                                                    } else {
                                                        $new_arr['is_late_final'] = false;
                                                        $new_arr['late_min_final'] = 0;
                                                    }
                                                }
                                                $total_unpaid_days = $total_unpaid_days - 0.5;
                                            }
                                        }
                                    }
                                }
                                $update_daily_attendance[] = $new_arr;
                            } else {
                                if ($total_approved_days >= 1) {
                                    $total_approved_days--;
                                } else {
                                    if ($total_approved_days = 0.5) {
                                        $total_approved_days = $total_approved_days - 0.5;
                                        if ($total_unpaid_days > 0) {
                                            $total_unpaid_days = $total_unpaid_days - 0.5;
                                        }
                                    } else {
                                        if ($total_unpaid_days > 0) {
                                            if ($total_unpaid_days >= 1) {
                                                $total_unpaid_days--;
                                            } else {
                                                $total_unpaid_days = $total_unpaid_days - 0.5;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($update_daily_attendance) {
                        $daily_attendance = new DailyAttendance();
                        $index = 'id';
                        batch()->update($daily_attendance, $update_daily_attendance, $index);
                    }
                }
            }
            DB::commit();
            $msg = $room == 'employee' ? "updated" : "manipulated";
            session()->flash("message", "Leave Request " . $msg . " successfully");
            $redirect = redirect()->route($room == 'employee' ? "apply-for-leave.index" : "requested-application.index");
        } catch (Exception $exception) {
            DB::rollBack();
            $redirect = redirect()->back()->withInput();
        }

        return $redirect->withInput();
    }

    /**
     * @param LeaveRequest $requestedApplication
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(LeaveRequest $requestedApplication)
    {
        try {
            $feedback["status"] = $requestedApplication->delete();
        } catch (Exception $exception) {
            $feedback["status"] = false;
        }

        return $feedback;
    }

    /**
     *
     * @return RedirectResponse
     */
    public function syncBalance()
    {
        try {
            DB::beginTransaction();
            $current_date = date('Y-m-d');
            $current_year = date("Y");
            $users = DB::select("SELECT users.id, users.`name`, users.email, users.fingerprint_no, employee_status.action_date, prm.department_id, prm.office_division_id FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id =( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$current_date' ) WHERE 1=1");

            # Leave Allocations for Current Year on the associated Department
            $leaveAllocation = LeaveAllocation::join('leave_allocation_details', 'leave_allocation_details.leave_allocation_id', '=', 'leave_allocations.id')
                ->where("year", $current_year)
                ->select('leave_allocations.*', 'leave_allocation_details.*')
                ->get();

            $department_wise_leave = [];
            foreach ($leaveAllocation as $leave) {
                $department_wise_leave[$leave->department_id][$leave->leave_type_id] = $leave->total_days;
            }

            foreach ($users as $user) {
                $initialLeave = [];
                $initialLeaveBalance = [];
                $currentLeave = [];
                $currentLeaveBalance = [];
                $joiningDate = $user->action_date;
                $year_month_date = explode('-', $joiningDate);
                $totalInitialLeave = 0;
                $totalCurrentLeave = 0;
                foreach ($department_wise_leave[$user->department_id] ?? [] as $leave_type_id => $balance) {
                    $initialLeave['leave_type_id'] = $leave_type_id;
                    if ($year_month_date[0] < $current_year) {
                        $initialLeave['total_days'] = $balance;
                    } else {
                        if ($year_month_date[1] < 12) {
                            $calculate_month = 12 - $year_month_date[1];
                            $leave_amount_for_month = ($balance * $calculate_month) / 12;
                        } else {
                            $leave_amount_for_month = 0;
                        }
                        $calculate_day = (30 - $year_month_date[2]) + 1;
                        if ($calculate_day >= 15) {
                            $per_month_avg_leave = $balance / 12;
                            $leave_amount_for_day = ($per_month_avg_leave * $calculate_day) / 30;
                        } else {
                            $leave_amount_for_day = 0;
                        }
                        $total_leave = $leave_amount_for_month + $leave_amount_for_day;
                        $integer_leave = floor($total_leave);
                        $fraction_leave = $total_leave - $integer_leave;
                        if ($fraction_leave > .5) {
                            $fraction_leave = 1;
                        } else {
                            if ($fraction_leave > 0) {
                                $fraction_leave = 0.5;
                            }
                        }
                        $initialLeave['total_days'] = $integer_leave + $fraction_leave;
                    }
                    $currentLeave['leave_type_id'] = $leave_type_id;


                    $leaveConsumedByApplication = LeaveRequest::select(DB::raw("SUM(number_of_days) as total_leave"))
                            ->where('from_date', '>=', $joiningDate)
                            ->whereYear("from_date", $current_year)
                            ->whereIn('status', [LeaveRequest::STATUS_APPROVED])
                            ->where('leave_type_id', $leave_type_id)
                            ->where('user_id', $user->id)
                            ->first()->total_leave ?? 0;

                    $leaveConsumedByLateDeduction = Salary::getLeaveDeductionOnSalary($user->id, $joiningDate,Salary::STATUS_PAID)[$user->id][$leave_type_id] ?? 0;

                    $used = $leaveConsumedByApplication + $leaveConsumedByLateDeduction;

                    $currentLeave['total_days'] = ($initialLeave['total_days'] - $used) < 0 ? 0 : ($initialLeave['total_days'] - $used);
                    $totalInitialLeave = $totalInitialLeave + $initialLeave['total_days'];
                    $totalCurrentLeave = $totalCurrentLeave + $currentLeave['total_days'];
                    $initialLeaveBalance[] = $initialLeave;
                    $currentLeaveBalance[] = $currentLeave;
                }
                UserLeave::updateOrCreate([
                    'user_id' => $user->id,
                    'year' => $current_year
                ], [
                    'user_id' => $user->id,
                    'initial_leave' => json_encode($initialLeaveBalance),
                    'total_initial_leave' => $totalInitialLeave,
                    'leaves' => json_encode($currentLeaveBalance),
                    'total_leaves' => $totalCurrentLeave,
                    'year' => $current_year
                ]);
            }
            session()->flash('message', "Sync Successfully");
            DB::commit();
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', "Sorry! Something went wrong!!");
            DB::rollBack();
        }
        return redirect()->route("home");
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

    /**
     * @param LeaveType $leaveType
     * @return JsonResponse
     */
    public function availableBalance(LeaveType $leaveType, LeaveRequest $requestedApplication)
    {
        try {
            $applied_user = User::with('currentPromotion')->find($requestedApplication->user_id);
            $leaveAllocation = LeaveAllocation::where("office_division_id", $applied_user->currentPromotion->office_division_id)
                ->where("department_id", $applied_user->currentPromotion->department_id)
                ->where("year", date("Y"))
                ->first();
            $leaveAllocationDetails = $leaveAllocation->leaveAllocationDetails->where("leave_type_id", $leaveType->id)->first();
            $leaveAllocationId = $leaveAllocationDetails->id;
            $available_leaves = UserLeave::where('user_id', '=', $requestedApplication->user_id)->where('year', '=', date("Y", strtotime($requestedApplication->from_date)))->first();
            $leave_balance = json_decode($available_leaves->leaves);
            foreach ($leave_balance as $leave_type_balance) {
                if ($leave_type_balance->leave_type_id == $leaveType->id) {
                    $balance = $leave_type_balance->total_days;
                }
            }
        } catch (Exception $exception) {
            $balance = 0;
            $leaveAllocationId = 0;
        }
        return response()->json(["balance" => $balance, "leave_allocation_details_id" => $leaveAllocationId]);
    }

    public function rollback(LeaveRequest $requestedApplication)
    {
        try {
            DB::beginTransaction();
            $request_id = $requestedApplication->id;
            $today_in_sec = strtotime(date('Y-m-d'));
            $leave_request_details = LeaveRequest::find($request_id);
            $unpaid_days = $leave_request_details->number_of_unpaid_days ?? 0;
            $paid_days = $leave_request_details->number_of_paid_days ?? 0;
            $deducted_leave_balance = $leave_request_details->number_of_days - ($paid_days + $unpaid_days);
            $leave_from_in_sec = strtotime($leave_request_details->from_date);
            $leave_year_month_date = explode('-', $leave_request_details->from_date);
            if ($deducted_leave_balance > 0) {
                $leave_balance = UserLeave::where('user_id', '=', $leave_request_details->user_id)->where('year', '=', $leave_year_month_date[0])->first();
                $type_wise_balance = json_decode($leave_balance->leaves);
                $userLeaveBalance = [];
                foreach ($type_wise_balance as $leave_by_type) {
                    if ($leave_by_type->leave_type_id == $leave_request_details->leave_type_id) {
                        array_push($userLeaveBalance, [
                            "leave_type_id" => $leave_by_type->leave_type_id,
                            "total_days" => $leave_by_type->total_days + $deducted_leave_balance
                        ]);
                    } else {
                        array_push($userLeaveBalance, [
                            "leave_type_id" => $leave_by_type->leave_type_id,
                            "total_days" => $leave_by_type->total_days
                        ]);
                    }
                }
                $leave_balance->leaves = json_encode($userLeaveBalance);
                $leave_balance->total_leaves = $leave_balance->total_leaves + $deducted_leave_balance;
                $leave_balance->save();
            }
            $leave_request_details->authorized_by = null;
            $leave_request_details->approved_by = null;
            $leave_request_details->authorized_date = null;
            $leave_request_details->approved_date = null;
            $leave_request_details->number_of_unpaid_days = null;
            $leave_request_details->number_of_paid_days = null;
            $leave_request_details->status = LeaveRequest::STATUS_CANCEL;
            $leave_request_details->save();
            $attendance_record = DailyAttendance::whereBetween('date', array($leave_request_details->from_date, $leave_request_details->to_date))
                ->where('user_id', '=', $leave_request_details->user_id)
                ->orderBy('date')->get();
            if ($attendance_record->count() > 0) {
                $attendanceCountStartHour = Setting::where("name", "attendance_count_start_hour")->select("id", "value")->first()->value;
                foreach ($attendance_record as $record) {
                    $startDate = $record->date;
                    $endDate = date('Y-m-d', strtotime('+1 day', strtotime($startDate)));
                    $text_represent_of_this_day = strtolower(date('D', strtotime($startDate)));
                    $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.user_id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.workslot_id, promotions.pay_grade_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$startDate' ) WHERE users.`id` = $leave_request_details->user_id";
                    $employee_information = DB::select($sql_promotion);
                    $is_public_holiday = false;
                    $is_weekly_holiday = false;
                    $all_public_holiday_records = PublicHoliday::where("from_date", "<=", $startDate)
                        ->where("to_date", ">=", $startDate)
                        ->get();
                    if ($all_public_holiday_records->count() > 0) {
                        $is_public_holiday = true;
                    }
                    $holiday_count = $is_public_holiday;
                    $department_id = $employee_information[0]->department_id;
                    $sql_weekly_holiday = "SELECT * FROM `weekly_holidays` WHERE `effective_date` <= '$startDate' AND (`end_date` >= '$startDate' OR `end_date` IS NULL) AND department_id = $department_id";
                    $result = DB::select($sql_weekly_holiday);
                    $weekly_holidays = $result[0]->days;
                    $approved_value = Roster::STATUS_APPROVED;
                    $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$startDate' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $leave_request_details->user_id OR `department_id` = $department_id)";
                    $roster_records = DB::select($sql_roster);
                    $roster_department = [];
                    $roster_user = [];
                    foreach ($roster_records as $each_roster) {
                        if ($each_roster->user_id) {
                            $roster_user['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                            $roster_user['work_slot_id'] = $each_roster->work_slot_id;
                        } else {
                            $roster_department['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                            $roster_department['work_slot_id'] = $each_roster->work_slot_id;
                        }
                    }
                    $employee_weekend = [];
                    if ($roster_user) {
                        $work_slot_id = $roster_user['work_slot_id'];
                        if ($roster_user['is_weekly_holiday']) {
                            $employee_weekend = [$text_represent_of_this_day];
                        }
                    } else {
                        if ($roster_department) {
                            $work_slot_id = $roster_department['work_slot_id'];
                            if ($roster_department['is_weekly_holiday']) {
                                $employee_weekend = [$text_represent_of_this_day];
                            }
                        } else {
                            $work_slot_id = $employee_information[0]->workslot_id;
                            $weekend_days = $weekly_holidays ?? '';
                            $weekend_days = substr($weekend_days, 2);
                            $weekend_days = substr($weekend_days, 0, -2);
                            $employee_weekend = explode('","', $weekend_days);
                        }
                    }
                    if (in_array($text_represent_of_this_day, $employee_weekend)) {
                        $is_weekly_holiday = true;
                        $holiday_count = true;
                    }
                    $work_slots = WorkSlot::find($work_slot_id);
                    $relax_approved_value = AssignRelaxDay::APPROVAL_CONFIRMED;
                    $sql_relax_day = "SELECT assign_relax_day.user_id FROM assign_relax_day INNER JOIN relax_day ON relax_day.id=assign_relax_day.relax_day_id WHERE relax_day.deleted_at IS NULL AND relax_day.date='$startDate' AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.user_id = $leave_request_details->user_id AND assign_relax_day.approval_status = $relax_approved_value";
                    $relax_users = DB::select($sql_relax_day);
                    $relax_day_users = [];
                    foreach ($relax_users as $u_relax) {
                        $relax_day_users[] = $u_relax->user_id;
                    }
                    $is_relax_day = false;
                    if (in_array($employee_information[0]->id, $relax_day_users)) {
                        $is_relax_day = true;
                    }
                    $working_min = 0;
                    $overtime_min = null;
                    $present_count = 0;
                    $is_late = false;
                    $late_in_min = 0;
                    $is_late_final = false;
                    $late_min_final = 0;
                    $leave_count = 0;
                    $attendanceCountStartHour_in_sec = $attendanceCountStartHour * 60 * 60;
                    $startDateTime_in_sec = strtotime($startDate . " " . $work_slots->start_time);
                    $startDateTime_in_sec = $startDateTime_in_sec - $attendanceCountStartHour_in_sec;
                    $endDateTime_in_sec = $startDateTime_in_sec + 86399;
                    $startDateTime = date('Y-m-d H:i:s', $startDateTime_in_sec);
                    $endDateTime = date('Y-m-d H:i:s', $endDateTime_in_sec);
                    $timeIn = Attendance::whereDate("punch_time", $startDate)
                        ->where("punch_time", '>=', $startDateTime)
                        ->orderBy("punch_time")
                        ->where("emp_code", $employee_information[0]->fingerprint_no)
                        ->select("id", "emp_code", "punch_time")
                        ->first();
                    if ($timeIn) {
                        $timeOut = Attendance::where("emp_code", $employee_information[0]->fingerprint_no)
                            ->where("punch_time", '>=', $startDateTime)
                            ->where("punch_time", '<=', $endDateTime)
                            ->orderByDesc("punch_time")
                            ->select("id", "emp_code", "punch_time")
                            ->first();
                        if (!isset($timeOut->punch_time)) {
                            $timeOut = Attendance::whereDate("punch_time", $startDate)
                                ->orderByDesc("punch_time")
                                ->where("emp_code", $employee_information[0]->fingerprint_no)
                                ->select("id", "emp_code", "punch_time")
                                ->first();
                        }
                    } else {
                        $timeOut = null;
                    }
                    if (!is_null($timeIn)) {
                        $present_count = 1;
                        if ($timeIn->punch_time === $timeOut->punch_time) $timeOut = null;
                        $date_time = date_create($timeIn->punch_time);
                        $entry_time_in_sec = strtotime(date_format($date_time, "Y-m-d H:i:s"));
                        if ($work_slots->is_flexible) {
                            $end_time_in_sec = $entry_time_in_sec + ($work_slots->total_work_hour * 60 * 60);
                            $late_time_in_sec = $entry_time_in_sec + 1;
                            $start_time_in_sec = $entry_time_in_sec;
                        } else {
                            $start_time_in_sec = strtotime($startDate . " " . $work_slots->start_time);
                            $end_time_in_sec = strtotime($startDate . " " . $work_slots->end_time);
                            if ($start_time_in_sec >= $end_time_in_sec) {
                                $end_time_in_sec = strtotime($endDate . " " . $work_slots->end_time);
                            }
                            $late_time_in_sec = strtotime($startDate . " " . $work_slots->late_count_time);
                            if ($start_time_in_sec > $late_time_in_sec) {
                                $late_time_in_sec = strtotime($endDate . " " . $work_slots->late_count_time);
                            }
                        }
                        if ($late_time_in_sec < $entry_time_in_sec) {
                            $is_late = true;
                            $late_in_min = round(abs($entry_time_in_sec - $late_time_in_sec) / 60, 2);
                        }
                        $is_late_final = $is_late;
                        $late_min_final = $late_in_min;
                        if (isset($timeOut) && isset($timeOut->punch_time)) {
                            $date_time_out = date_create($timeOut->punch_time);
                            $exit_time_in_sec = strtotime(date_format($date_time_out, "Y-m-d H:i:s"));
                            $working_min = round(abs($exit_time_in_sec - $entry_time_in_sec) / 60, 2);
                        } else {
                            $exit_time_in_sec = 0;
                        }
                        if ($work_slots->over_time == 'Yes' && $exit_time_in_sec > 0) {
                            $actual_work_start_time = $start_time_in_sec;
                            if ($work_slots->is_flexible) {
                                $actual_work_end_time = $end_time_in_sec;
                            } else {
                                $actual_work_end_time = strtotime($startDate . " " . $work_slots->overtime_count);
                                if ($end_time_in_sec > $actual_work_end_time) {
                                    $actual_work_end_time = strtotime($endDate . " " . $work_slots->overtime_count);
                                }
                            }
                            $actual_work_time_in_sec = $actual_work_end_time - $actual_work_start_time;
                            if ($exit_time_in_sec > $actual_work_end_time) {
                                $working_sec = $working_min * 60;
                                if ($working_sec > $actual_work_time_in_sec) {
                                    $extra_sec = $working_sec - $actual_work_time_in_sec;
                                    if (!$work_slots->is_flexible) {
                                        if ($actual_work_start_time > $entry_time_in_sec) {
                                            $extra_sec = $extra_sec - ($actual_work_start_time - $entry_time_in_sec);
                                        }
                                    }
                                    if ($extra_sec > 0) {
                                        $overtime_min = round(abs($extra_sec) / 60, 2);
//                                        $ot_rule = (int)($overtime_min / 30);
//                                        $overtime_min = $ot_rule * 30;
                                    }
                                }
                            }
                        }
                        $attendance_summary = [
                            "time_in" => $timeIn ? $timeIn->punch_time : null,
                            "time_out" => $timeOut ? $timeOut->punch_time : null,
                            "roaster_start_time" => $work_slots->start_time,
                            "roaster_end_time" => $work_slots->end_time ?? '',
                            "late_count_time" => $work_slots->late_count_time ?? '',
                            "is_late_day" => $is_late,
                            "late_in_min" => $late_in_min,
                            "working_min" => $working_min,
                            "is_ot_available" => ($work_slots->over_time == 'Yes') ? 1 : 0,
                            "overtime_min" => $overtime_min,
                            "present_count" => $present_count,
                            "is_late_final" => $is_late_final,
                            "late_min_final" => $late_min_final,
                            'holiday_count' => $holiday_count,
                            'absent_count' => 0,
                            'leave_count' => $leave_count,
                            'is_public_holiday' => $is_public_holiday,
                            'is_weekly_holiday' => $is_weekly_holiday,
                            'is_relax_day' => false
                        ];
                    } else {
                        if ($holiday_count) {
                            $absent_count = 0;
                        } else {
                            if ($is_relax_day) {
                                $absent_count = 0;
                            } else {
                                $absent_count = 1 - $leave_count;
                            }
                        }
                        $attendance_summary = [
                            "time_in" => $timeIn ? $timeIn->punch_time : null,
                            "time_out" => $timeOut ? $timeOut->punch_time : null,
                            "roaster_start_time" => $work_slots->start_time,
                            "roaster_end_time" => $work_slots->end_time ?? '',
                            "late_count_time" => $work_slots->late_count_time ?? '',
                            "is_late_day" => $is_late,
                            "late_in_min" => $late_in_min,
                            "working_min" => $working_min,
                            "is_ot_available" => ($work_slots->over_time == 'Yes') ? 1 : 0,
                            "overtime_min" => $overtime_min,
                            "present_count" => $present_count,
                            "is_late_final" => $is_late_final,
                            "late_min_final" => $late_min_final,
                            'holiday_count' => $holiday_count,
                            'absent_count' => $absent_count,
                            'leave_count' => $leave_count,
                            'is_public_holiday' => $is_public_holiday,
                            'is_weekly_holiday' => $is_weekly_holiday,
                            'is_relax_day' => $holiday_count ? false : $is_relax_day
                        ];
                    }
                    DailyAttendance::where('id', '=', $record->id)->update($attendance_summary);
                }
            }
            DB::commit();
            $feedback["status"] = true;
            $feedback["message"] = "Leave rolled back successfully!!";
        } catch (Exception $exception) {
            DB::rollBack();
            $feedback["status"] = false;
            $feedback["message"] = "Sorry! Something went wrong!!";
            Log::info('Exception Message : ' . $exception->getMessage());
            Log::info('Line No. : ' . $exception->getLine());
        }
        return $feedback;
    }
}
