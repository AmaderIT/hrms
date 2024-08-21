<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\RequestEmployeeLeaveApplication;
use App\Models\DepartmentSupervisor;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveUnpaid;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class EmployeeLeaveApplicationController extends Controller
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
        $items = LeaveRequest::where("applied_by", auth()->user()->id)->orderByDesc("id")->paginate(\Functions::getPaginate());
        return view("employee-leave-application.index", compact("items"));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        if(auth()->user()->isSupervisor() === true) {
            $departmentIds = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->pluck("department_id");
            $employees = User::with("currentPromotion")->whereHas("currentPromotion", function ($query) use ($departmentIds) {
                return $query->whereIn("department_id", $departmentIds);
            })->active()->get();
        } elseif(auth()->user()->isAdmin() === true) {
            $employees = User::select("id", "name", "fingerprint_no")->get();
        }

        $data = array(
            "employees"     => $employees,
            "leaveTypes"    => LeaveType::select("id", "name")->get(),
        );

        return view("employee-leave-application.create", compact("data"));
    }

    /**
     * @param LeaveRequest $employeeLeaveApplication
     * @return Factory|View
     */
    public function edit(LeaveRequest $employeeLeaveApplication)
    {
        if(auth()->user()->isSupervisor() === true) {
            $departmentIds = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->pluck("department_id");
            $employees = User::with("currentPromotion")->whereHas("currentPromotion", function ($query) use ($departmentIds) {
                return $query->whereIn("department_id", $departmentIds);
            })->active()->get();
        } elseif(auth()->user()->isAdmin() === true) {
            $employees = User::select("id", "name", "fingerprint_no")->get();
        }

        $data = array(
            "employees"     => $employees,
            "leaveTypes"    => LeaveType::select("id", "name")->get(),
            "balance"       => $this->currentBalance($employeeLeaveApplication),
        );

        return \view("employee-leave-application.edit", compact("data", "employeeLeaveApplication"));
    }

    /**
     * @param RequestEmployeeLeaveApplication $request
     * @return RedirectResponse|null
     */
    public function store(RequestEmployeeLeaveApplication $request)
    {
        DB::beginTransaction();
        try {
            $redirect = null;

            if($request->input("current_balance") >= $request->input("number_of_days")) {
                # Apply for Leave
                LeaveRequest::create($request->validated());

                # Resolve Unpaid Leave Status
                $fromDate = Carbon::parse($request->input("from_date"));
                $toDate = Carbon::parse($request->input("to_date"));
                $leaveRequests = LeaveUnpaid::whereUserId($request->input("user_id"))->whereDateBetween($fromDate, $toDate)->get();

                foreach ($leaveRequests as $leaveRequest)
                {
                    $leaveRequest->update([
                        "status"    => LeaveUnpaid::STATUS_APPLIED,
                    ]);
                }

                session()->flash("message", "Leave Request Created Successfully");
                $redirect = redirect()->route("employee-leave-application.index");
            } elseif($request->input("current_balance") < $request->input("number_of_days")) {
                session()->flash("type", "error");
                session()->flash("message", "Sorry!! You don't have sufficient balance");
                $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());
            }

            DB::commit();
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry!! You don't have sufficient balance");
            $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());

            DB::rollBack();
        }

        return $redirect;
    }

    /**
     * @param RequestEmployeeLeaveApplication $request
     * @param LeaveRequest $employeeLeaveApplication
     * @return RedirectResponse
     */
    public function update(RequestEmployeeLeaveApplication $request, LeaveRequest $employeeLeaveApplication)
    {
        try {
            $employeeLeaveApplication->update($request->validated());

            session()->flash("message", "Employee Leave Request Updated Successfully");
            $redirect = redirect()->route("employee-leave-application.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param LeaveRequest $employeeLeaveApplication
     * @return mixed
     */
    public function delete(LeaveRequest $employeeLeaveApplication)
    {
        try {
            $feedback['status'] = $employeeLeaveApplication->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param LeaveType $leaveType
     * @param User $employee
     * @return JsonResponse
     */
    public function balance(LeaveType $leaveType, User $employee): JsonResponse
    {
        try {
            $currentPromotion = $employee->currentPromotion;
            $leaveAllocation = LeaveAllocation::where("office_division_id", $currentPromotion->office_division_id)
                ->where("department_id", $currentPromotion->department_id)
                ->where("year", date("Y"))
                ->first();

            $leaveAllocationDetails = $leaveAllocation->leaveAllocationDetails->where("leave_type_id", $leaveType->id)->first();
            $totalLeave             = $leaveAllocationDetails->total_days;
            $leaveAllocationId      = $leaveAllocationDetails->id;

            $spendLeave = LeaveRequest::where("user_id", $employee->id)
                ->where("leave_type_id", $leaveType->id)
                ->where("status", 1)
                ->sum("number_of_days");

            $balance = $totalLeave - $spendLeave;
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
}
