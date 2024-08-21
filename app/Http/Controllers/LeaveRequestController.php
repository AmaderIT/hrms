<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Http\Requests\leave\holidays\RequestLeaveRequest;
use App\Http\Requests\leave\holidays\RequestManipulateHoliday;
use App\Models\AssignRelaxDay;
use App\Models\Leave;
use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationDetails;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveUnpaid;
use App\Models\Loan;
use App\Models\Promotion;
use App\Models\Roster;
use App\Models\Salary;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\WorkSlot;
use App\Models\ZKTeco\Attendance as ZKTeco;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Exception;
use DateTime;

class LeaveRequestController extends Controller
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
     * @return Application|Factory|View
     */
    public function index()
    {
        $data['items'] = LeaveRequest::with("employee", "leaveType", "approvedBy")
            ->where("user_id", auth()->user()->id)
            ->orderByDesc("id")
            ->select("uuid", "user_id", "leave_type_id", "half_day", "from_date", "to_date", "number_of_days", "approved_by", "status", "purpose")
            ->paginate(\Functions::getPaginate());

        return view("apply-for-leave.index", $data);
    }

    /**
     * @param null $data
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function create($data = null)
    {
        $logged_user = User::with('currentPromotion')->find(Auth::id());

        $getEmployeeInfos = User::with(["currentPromotion" => function ($query) {
            $query->with("officeDivision", "department");
        }])->select("id", "name", "email", "phone", "fingerprint_no", "status", "photo")
            ->where(['users.id' => $logged_user->id])
            ->orderByDesc("id")
            ->first();
        $requestedApplication = null;

        return view("requested-application.edit", compact("requestedApplication", "data", "getEmployeeInfos"));
    }

    /**
     * @param LeaveRequest $applyForLeave
     * @return Application|Factory|View
     */
    public function edit(LeaveRequest $applyForLeave)
    {
        return RequestedApplicationController::edit($applyForLeave, 'employee');
    }

    /**
     * @param RequestLeaveRequest $request
     * @return RedirectResponse
     */
    public function store(RequestLeaveRequest $request)
    {
        DB::beginTransaction();
        try {
            $redirect = null;

            # Check for duplicate entry
            $fromDate = Carbon::parse($request->input("from_date"));
            $toDate = Carbon::parse($request->input("to_date"));

            $case1 = LeaveRequest::whereUserId(\auth()->user()->id)
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case2 = LeaveRequest::whereUserId(\auth()->user()->id)
                ->where("from_date", ">=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", "<=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case3 = LeaveRequest::whereUserId(\auth()->user()->id)
                ->where("from_date", ">=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->count();

            $case4 = LeaveRequest::whereUserId(\auth()->user()->id)
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", ">=", date('Y-m-d', strtotime($toDate)))
                ->where("from_date", "<=", date('Y-m-d', strtotime($fromDate)))
                ->where("to_date", "<=", date('Y-m-d', strtotime($toDate)))
                ->count();

            if ($case1 > 0 || $case2 > 0 || $case3 > 0 || $case4 > 0) {
                throw new Exception("Leave Application has already been applied for same date range.");
            }

            $total_leave_days = abs((strtotime($request->from_date) - strtotime($request->to_date)) / 86400) + 1;

            $relax_approved_value = AssignRelaxDay::APPROVAL_CONFIRMED;
            $sql_relax_check = "SELECT relax_day.id,relax_day.date,assign_relax_day.user_id FROM relax_day INNER JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id WHERE relax_day.`date` BETWEEN '$request->from_date' AND '$request->to_date' AND assign_relax_day.user_id=$request->user_id AND relax_day.deleted_at IS NULL AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = $relax_approved_value";
            $relax_day_existance = DB::select($sql_relax_check);
            if ($total_leave_days < 2 && $relax_day_existance) {
                $date_relax = $relax_day_existance[0]->date;
                if (($request->from_date == $relax_day_existance[0]->date) || ($request->to_date == $relax_day_existance[0]->date)) {
                    return redirect()->back()->withInput()->withErrors("Leave applied with relax day ($date_relax) ! Please change the date fields and submit again!");
                }
            }


            # Apply for Leave
            if ($request->number_of_days) {
                $request->number_of_days = $toDate->diffInDays($fromDate) + 1;
            }
            LeaveRequest::create($request->toArray());

            # Resolve Unpaid Leave Status
            $leaveRequests = LeaveUnpaid::whereUserId($request->input("user_id"))->whereDateBetween($fromDate, $toDate)->get();


            foreach ($leaveRequests as $leaveRequest) {
                $leaveRequest->update([
                    "status" => LeaveUnpaid::STATUS_APPLIED,
                ]);
            }

            session()->flash("message", "Leave Request Created Successfully");
            $redirect = redirect()->route("apply-for-leave.index");

            DB::commit();
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());

            DB::rollBack();
        }

        return $redirect;
    }

    /**
     * @param RequestLeaveRequest $request
     * @param LeaveRequest $applyForLeave
     * @return RedirectResponse
     */
    public function update(RequestManipulateHoliday $request, LeaveRequest $applyForLeave)
    {
        return RequestedApplicationController::manipulate($request, $applyForLeave, 'employee');
    }

    /**
     * @param LeaveRequest $applyForLeave
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(LeaveRequest $applyForLeave)
    {
        try {
            $feedback['status'] = $applyForLeave->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param LeaveType $leaveType
     * @return JsonResponse
     */
    public function balance(LeaveType $leaveType)
    {
        try {
            $currentPromotion = auth()->user()->currentPromotion;
            $leaveAllocation = LeaveAllocation::where("office_division_id", $currentPromotion->office_division_id)
                ->where("department_id", $currentPromotion->department_id)
                ->where("year", date("Y"))
                ->first();

            $leaveAllocationDetails = $leaveAllocation->leaveAllocationDetails->where("leave_type_id", $leaveType->id)->first();
            $leaveAllocationId = $leaveAllocationDetails->id;

            $userLeave = UserLeave::where("user_id", auth()->user()->id)->where("year", date("Y"))->first();

            $leaveType = collect(json_decode($userLeave->leaves))->where("leave_type_id", $leaveType->id)->first();

            $balanceAccordingToJoiningDate = $leaveType->total_days;

            $balance = $balanceAccordingToJoiningDate;
        } catch (Exception $exception) {
            $balance = 0;
            $leaveAllocationId = 0;
        }

        return response()->json(["balance" => $balance, "leave_allocation_details_id" => $leaveAllocationId]);
    }

    /**
     * @param LeaveRequest $applyForLeave
     * @return mixed
     */
    protected function currentBalance(LeaveRequest $applyForLeave)
    {
        $url = \app("url")->route("apply-for-leave.balance", ["employee" => $applyForLeave->user_id, "leaveType" => $applyForLeave->leave_type_id]);
        $link = action("LeaveRequestController@balance", ["employee" => $applyForLeave->user_id, "leaveType" => $applyForLeave->leave_type_id]);

        $request = Request::create($url, "GET");
        $dispatchRoute = Route::dispatch($request)->getContent();

        $result = json_decode($dispatchRoute);
        return $result->balance;
    }


    /**
     *Get half day according to leave from date.
     *First checking roster work slot then checking default work slot of an employee.
     */
    public function getSlotWiseTimeRange(Request $request)
    {
        $user_id = $request->user_id;
        $leaveRequestType = $request->leave_request_type;
        $fromDate = !empty($request->input('from_date')) ? date('Y-m-d', strtotime($request->input('from_date'))) : "";
        $timeSlots = [];
        if (!empty($fromDate) && !empty($leaveRequestType) && $leaveRequestType == 'half_day') {
            $timeSlots = Common::findOutWorkSlots($fromDate, $leaveRequestType, $user_id);
            return response()->json(['status' => true, 'timeSlots' => $timeSlots]);
        }
        return response()->json(['status' => false, 'timeSlots' => $timeSlots]);
    }


    /**
     * Checking leave balance and get balance with leave type by putting leave request from and to date
     */
    public function dateRangeChecker(Request $request)
    {
        $response = ['type' => 'error', 'data' => [], 'message' => ''];
        try {
            $currentYear = date('Y');
            $no_of_days = 0;

            $user = User::find($request->user_id);

            $user_promotion = Promotion::select(['type', 'promoted_date'])
                ->where('user_id', $request->user_id)
                ->whereIn('type', [Promotion::TYPE_JOIN, Promotion::TYPE_REJOIN])
                ->orderBy('promoted_date', 'DESC')
                ->first();

            $joinDate = $user_promotion->promoted_date ?? $currentYear . "-01-01";


            if ($request->from_date) {

                if ($user_promotion && date("Y-m-d", strtotime($user_promotion->promoted_date)) > date("Y-m-d", strtotime($request->from_date))) {
                    return [
                        'type' => 'error',
                        'data' => [],
                        'message' => "Leave application start date can not be less than employee join or rejoin date!",
                    ];
                }
                if ($request->from_date && $request->to_date && $request->leave_request_type == 'full_day') {
                    $no_of_days = strtotime($request->from_date) - strtotime($request->to_date);
                    $no_of_days = abs($no_of_days / 86400) + 1;

                } else if ($request->leave_request_type == 'half_day') {
                    $no_of_days = 0.5;
                }
            }

            $msg = "";
            $dynamic_leave_types = [];
            $userLeaves = UserLeave::where('user_id', $request->user_id)->where('year', $request->from_date ?? $currentYear)->first();

            if ($userLeaves) {
                $leave_balance = json_decode($userLeaves->leaves ?? []);
                $initial_leaves = json_decode($userLeaves->initial_leave ?? []);
                $f = 0;
                $paidLeaveTypeCounter = 0;

                foreach ($initial_leaves as $k => $leave) {

                    $lock_leave = LeaveRequest::select('leave_type_id', DB::raw("SUM(number_of_days) as total_number_of_days"))
                            ->where('from_date', '>=', $joinDate)
                            ->whereYear("from_date", $request->from_date ?? $currentYear)
                            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_AUTHORIZED])
                            ->where(['user_id' => $request->user_id])->where('leave_type_id', $leave->leave_type_id)
                            ->where('id', '<>', $request->leave_application_id ?? 0)
                            ->first()->total_number_of_days ?? 0;


                    $leaveConsumedByApplication = LeaveRequest::select(DB::raw("SUM(number_of_days) as total_leave"))
                            ->where('from_date', '>=', $joinDate)
                            ->whereYear("from_date", $request->from_date ?? $currentYear)
                            ->whereIn('status', [LeaveRequest::STATUS_APPROVED])
                            ->where('leave_type_id', $leave->leave_type_id)
                            ->where('user_id', $request->user_id)
                            ->first()->total_leave ?? 0;

                    $leaveConsumedByLateDeduction = 0;
                    $leaveLockedByLateDeduction = 0;

                    $joinDate = date("Y-m-d", strtotime($joinDate));

                    $salary = Salary::select(['late_leave_deduction', 'status', 'month'])
                        ->where('user_id', $request->user_id)
                        ->whereRaw("CONCAT(year, '-', LPAD(month, 2, '0'), '-01') >= '$joinDate'")
                        ->where('year', $request->from_date ?? $currentYear)
                        ->get();


                    foreach ($salary as $salary) {
                        $salaryLateLeaveDeduction = json_decode($salary->late_leave_deduction ?? null);
                        foreach ($salaryLateLeaveDeduction ?? [] as $s) {
                            if ($s->leave_type_id == $leave->leave_type_id) {

                                if ($salary->status == Salary::STATUS_PAID) {
                                    $leaveConsumedByLateDeduction += (double)$s->to_be_deducted;
                                } else if ($salary->status == Salary::STATUS_UNPAID) {
                                    $leaveLockedByLateDeduction += (double)$s->to_be_deducted;
                                }
                            }
                        }
                    }


                    $consumed_leave = $leaveConsumedByApplication + $leaveConsumedByLateDeduction;
                    $lock_leave += $leaveLockedByLateDeduction;

                    $is_enable = "";
                    $consumed = $consumed_leave;
                    $usable = $leave->total_days - $consumed_leave - $lock_leave;
                    if ($usable < 0) {
                        $usable = 0;
                    }
                    $leave_type = LeaveType::find($leave->leave_type_id);

                    if ($leave_type && $leave_type->is_paid > 0) {
                        $paidLeaveTypeCounter++;
                    }


                    if ($usable == 0 || ($no_of_days > $usable)) {
                        $is_enable = "disabled";
                        if ($leave_type->is_paid > 0) {
                            $f++;
                        }
                    }


                    $app = LeaveRequest::find($request->leave_application_id);
                    $checked_status = "";
                    if ($app && $app->leave_type_id == $leave->leave_type_id && $is_enable == "") {
                        $checked_status = "checked";
                    }

                    $dynamic_leave_types[] = [
                        'name' => $leave_type->name ?? '',
                        'is_paid' => $leave_type->is_paid ?? 0,
                        'leave_type_id' => $leave->leave_type_id,
                        'entitled' => $leave->total_days,
                        'consumed' => $consumed,
                        'lock' => $lock_leave,
                        'usable' => $usable,
                        'enable_status' => $is_enable,
                        'checked_status' => $checked_status,
                    ];

                }


                if ($f == $paidLeaveTypeCounter) {

                    $msg = "Your paid leave is insufficient! Either you have to change your leave duration or apply on un-paid leave type if available.";
                }
                return [
                    'type' => 'success',
                    'data' => $dynamic_leave_types,
                    'message' => $msg,
                    'leave_allocation_details_id' => [$f, $paidLeaveTypeCounter]
                ];
            } else {
                return [
                    'type' => 'error',
                    'data' => [],
                    'message' => "Leave information was not found!Please contact with administration.Thank you.",
                ];
            }


        } catch (Exception $exception) {
            return ['type' => 'error', 'message' => $exception->getMessage()];
        }
        return $response;
    }

    public function getEmployeeLeaveGraph(Request $request)
    {
        $response = response()->json(['type' => 'error', 'data' => [], 'message' => '']);
        try {
            $currentYear = date('Y');
            $user = User::find($request->user_id);

            $user_promotion = Promotion::select(['type', 'promoted_date'])
                ->where('user_id', $request->user_id)
                ->whereIn('type', [Promotion::TYPE_JOIN, Promotion::TYPE_REJOIN])
                ->orderBy('promoted_date', 'DESC')
                ->first();

            $joinDate = $user_promotion->promoted_date ?? $currentYear . "-01-01";

            $leaveTypes = LeaveType::orderBy('priority', 'ASC')->get();
            $graphData = [];
            foreach ($leaveTypes as $leaveType) {
                $data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                foreach ($data as $month => $d) {
                    $leaveConsumedByApplication = LeaveRequest::select(DB::raw("SUM(number_of_days) as total_leave"), DB::raw(' MONTH(from_date) month'))
                            ->where('from_date', '>=', $joinDate)
                            ->whereYear("from_date", $currentYear)
                            ->whereMonth("from_date", $month + 1)
                            ->whereIn('status', [LeaveRequest::STATUS_APPROVED])
                            ->where('leave_type_id', $leaveType->id)
                            ->where('user_id', $request->user_id)
                            ->first()->total_leave ?? 0;

                    $leaveConsumedByLateDeduction = 0;

                    $targetDate = date("Y-m-d", strtotime($currentYear . "-" . ($month + 1) . "-01"));
                    $joinDate = date("Y-m-d", strtotime($joinDate));

                    if ($targetDate >= $joinDate) {
                        $salary = Salary::select('late_leave_deduction')
                            ->where('user_id', $request->user_id)
                            ->where('year', $currentYear)
                            ->where('month', $month + 1)
                            ->where('status', Salary::STATUS_PAID)
                            ->first();
                        $salaryLateLeaveDeduction = json_decode($salary->late_leave_deduction ?? null);
                        foreach ($salaryLateLeaveDeduction ?? [] as $s) {
                            if ($s->leave_type_id == $leaveType->id) {
                                $leaveConsumedByLateDeduction = $s->to_be_deducted;
                            }
                        }
                    }

                    $data[$month] = $leaveConsumedByApplication + $leaveConsumedByLateDeduction;
                }
                $graphData [] = [
                    'name' => $leaveType->name,
                    'data' => $data
                ];
            }

            return response()->json([
                'type' => 'success',
                'data' => $graphData,
            ]);
        } catch (Exception $exception) {
            return response()->json(['type' => 'error', 'message' => $exception->getMessage()]);
        }
        return $response;
    }
}
