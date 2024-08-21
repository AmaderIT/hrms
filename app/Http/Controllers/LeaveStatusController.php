<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Promotion;
use App\Models\Salary;
use App\Models\User;
use App\Models\UserLeave;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class LeaveStatusController extends Controller
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
        $data["leaveReportsForLate"] = $this->getLeaveReportForLate(auth()->user());
        $data["leaveReportDetails"] = $this->getLeaveReportDetails(auth()->user());
        $data["leaveReports"] = $this->getLeaveReport(auth()->user());
        $data["leaveReportToSupervisor"] = $this->getLeaveReportToSupervisor();
        $data["leaveReportBalance"] = $this->getLeaveReportBalance($data["leaveReports"]);
        $data["lateLeaveDeductions"] = $this->getLateLeaveDeduction(auth()->id());
        $data["leaveTypes"] = LeaveType::pluck('name', 'id')->toArray();

        return view("leave-status.index", compact("data"));
    }

    /**
     * My Leave Status
     *
     * @param User $user
     * @return array
     */
    protected function getLeaveReport(User $user)
    {
        $data = [];
        $userLeave = UserLeave::where("user_id", $user->id)->where("year", date("Y"))->first();
        $initialLeave = collect(json_decode($userLeave->initial_leave));
        $balanceLeave = collect(json_decode($userLeave->leaves));

        $leaveTypes = LeaveType::whereIn("id", $initialLeave->pluck("leave_type_id")->toArray())->get();

        foreach ($initialLeave as $value) {
            $getLeaveReportDetails = $this->getLeaveReportDetails($user);
            $utilized = 0;
            if (array_key_exists($value->leave_type_id, $getLeaveReportDetails->toArray())) {
                $utilized = collect($getLeaveReportDetails[$value->leave_type_id])->sum("number_of_days");
            }

            array_push($data, [
                "leave_type_id" => $value->leave_type_id,
                "name" => $leaveTypes->where("id", $value->leave_type_id)->first()->name,
                "entitled" => $initialLeave->where("leave_type_id", $value->leave_type_id)->first()->total_days,
                "balance" => $balanceLeave->where("leave_type_id", $value->leave_type_id)->first()->total_days,
                "utilized" => $utilized
            ]);
        }

        return $data;
    }

    /**
     * Leave Deduction for Late purpose on Salary
     *
     * @param User $user
     * @return mixed
     */
    protected function getLeaveReportForLate(User $user)
    {
        $lateLeave = Salary::where("user_id", $user->id)
            ->where("year", date('Y'))
            ->where(function ($query) {
                return $query->where("casual_leave", ">", 0)
                    ->orWhere("earn_leave", ">", 0);
            })
            ->where("status", Salary::STATUS_PAID)
            ->select("id", "user_id", "late_leave_deduction", "casual_leave", "earn_leave", "month", "year")
            ->get();

        return $lateLeave;
    }

    /**
     * @param User $user
     * @return mixed
     */
    protected function getLeaveReportDetails(User $user)
    {
        $user_promotion = Promotion::select(['type', 'promoted_date'])
            ->where('user_id', $user->id)
            ->whereIn('type', [Promotion::TYPE_JOIN, Promotion::TYPE_REJOIN])
            ->orderBy('promoted_date', 'DESC')
            ->first();

        $joinDate = $user_promotion->promoted_date ?? date('Y') . "-01-01";

        return $leaveRequests = LeaveRequest::with("leaveType")
            ->where('from_date', '>=', $joinDate)
            ->whereUserId($user->id)
            ->whereYear("from_date", "<=", date('Y'))
            ->whereYear("to_date", ">=", date('Y'))
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->get()
            ->groupBy("leave_type_id");
    }

    /**
     * @return Collection|null
     */
    protected function getLeaveReportToSupervisor()
    {
        $report = null;
        if (auth()->user()->isSupervisor() === true) {
            $getDeptIDs = FilterController::getDepartmentIds();
            $employeeIds = [];
            if (!empty($getDeptIDs) && count($getDeptIDs) > 0) {
                foreach ($getDeptIDs as $getDeptID) {
                    $getEmployeeDeptWise = FilterController::getEmployeeIds(1, 'department', $getDeptID);
                    foreach ($getEmployeeDeptWise as $key => $value) {
                        $employeeIds[] = $value;
                    }
                }
            }
            $getEmpIds = (!empty($employeeIds) && count($employeeIds) > 0) ? array_unique($employeeIds) : [];
            $employees = User::whereIn('id', $getEmpIds)->whereStatus(User::STATUS_ACTIVE)->get();
            $report = collect();
            if (!empty($employees) && count($employeeIds) > 0) {
                foreach ($employees as $employee) {
                    $employeeLeaveReport = $this->getLeaveReport($employee);
                    $data = collect($employeeLeaveReport);
                    $totalEntitled = $data->sum("entitled");
                    $balance = $data->sum("balance");
                    $totalUtilized = $totalEntitled - $balance;
                    $report->push(array(
                        "totalEntitled" => $totalEntitled,
                        "totalUtilized" => $totalUtilized,
                        "balance" => $balance,
                        "employee" => $employee->load("currentPromotion.officeDivision", "currentPromotion.department"),
                    ));
                }
            }
        }
        return $report;
    }

    /**
     * @param $leaveReports
     * @return array
     */
    protected function getLeaveReportBalance($leaveReports)
    {
        $currentLeaveStatus = collect($leaveReports);
        return [
            "casual_leave" => $currentLeaveStatus->where("leave_type_id", 5)->first()['balance'],
            "earn_leave" => $currentLeaveStatus->where("leave_type_id", 3)->first()['balance'],
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    public function leaveToSupervisor(User $user)
    {
        $employee = $user->load("currentPromotion.officeDivision", "currentPromotion.department");

        try {
            $report = array(
                "data" => $this->getLeaveReport($user),
                "leaveReportsForLate" => $this->getLeaveReportForLate($user),
                "leaveReportDetails" => $this->getLeaveReportDetails($user),
                "employee" => $employee,
            );
            $report["leaveReportBalance"] = $this->getLeaveReportBalance($report["data"]);
        } catch (Exception $exception) {
            $report = array(
                "data" => null,
                "leaveReportDetails" => null,
                "employee" => $employee,
            );
        }

        return view("leave-report", compact("report"));
    }

    public function getLateLeaveDeduction($userId)
    {
        $current_year = date('Y');
        $user = User::find($userId);
        $employeeJoiningDate = $user->getLatestJoiningRelatedDateFromPromotion()['joiningDate'] ?? date('Y-m-d');
        $monthCondition = "";
        if ($employeeJoiningDate) {
            $monthCondition = " AND month>= " . date('m', strtotime($employeeJoiningDate));
        }
        $lateLeaveDeductions = DB::select("SELECT uuid, month, year, late_leave_deduction FROM `salaries` WHERE JSON_EXTRACT(`late_leave_deduction`, '$[*].leave_type_id') IS NOT NULL AND year = $current_year $monthCondition  AND user_id = $userId");

        $userLateLeaveDeductions = [];
        foreach ($lateLeaveDeductions as $leaveDeduction) {
            $typeWiseDeductions = json_decode($leaveDeduction->late_leave_deduction, true);
            foreach ($typeWiseDeductions as $typeWiseDeduction) {
                if (empty($userLateLeaveDeductions[$typeWiseDeduction['leave_type_id']])) {
                    $userLateLeaveDeductions[$typeWiseDeduction['leave_type_id']] = 0;
                }
                $userLateLeaveDeductions[$typeWiseDeduction['leave_type_id']] += $typeWiseDeduction['to_be_deducted'];
            }
        }
        return [
            'summery' => $userLateLeaveDeductions,
            'details' => $lateLeaveDeductions,
        ];
    }
}
