<?php

namespace App\Http\Controllers;

use App\Exports\Report\LeaveHistoryExport;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\LeaveRequest;
use App\Models\OfficeDivision;
use Barryvdh\DomPDF\Facade as PDF;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LeaveHistoryReportsController extends Controller
{
    public function viewLeaveHistory()
    {
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisionIds)->get();
        $departmentIds = [];
        $data['officeDepartments'] = [];
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id', $divisionIds)->get();
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
            if (!empty($departmentIds)) {
                $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $departmentIds)->get();
            }
        }
        $departmentIds_in_string = implode(',', $departmentIds);
        if(!empty($departmentIds_in_string)){
            $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        }
        return view("leave-history-reports.view-leave-history", compact("data"));
    }

    public function generateLeaveHistory(Request $request)
    {
        $officeDivisionID = $request->office_division_id;
        $month_year = explode("-", $request->datepicker);

        if (isset($request->type) && ($request->type == 'excel' || $request->type == 'pdf')) {
            $departmentIDs = json_decode($request->department_id);
            $userIDs = json_decode($request->user_id);
        } else {
            $departmentIDs = $request->department_id;
            $userIDs = $request->user_id;
        }

        $filter = [];
        $filter['office_division_id'] = $officeDivisionID;
        $filter['department_id'] = $departmentIDs;
        $filter['user_id'] = $userIDs;
        $filter['datepicker'] = $request->datepicker;
        $filter['status'] = $request->status;

        $month = $month_year[0];
        $year = $month_year[1];
        $firstDateOfMonth = $year . "-" . $month . "-" . "01";
        $lastDateOfMonth = date("Y-m-t", strtotime($firstDateOfMonth));
        $dateObj = DateTime::createFromFormat('!m', $month);
        $monthAndYear = $dateObj->format('F') . ", " . $year;
        $datas = LeaveRequest::with(["employee.currentPromotion" => function ($query) {
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
                ])->whereDate("from_date", ">=", $firstDateOfMonth)->whereDate("to_date", "<=", $lastDateOfMonth)->orderBy("id", "desc");

        if ($request->status != 'all') {
            $datas->where('status', $request->status);
        }else{
            $datas->whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED]);
        }

        if ($officeDivisionID == 'all') {
            $find_division = true;
        } else {
            $find_division = false;
        }
        if (in_array("all", $departmentIDs)) {
            $find_department = true;
        } else {
            $find_department = false;
        }
        if (in_array("all", $userIDs)) {
            $find_employee = true;
        } else {
            $find_employee = false;
        }
        if ($find_employee) {
            if ($find_department) {
                if ($find_division) {
                    $datas->whereIn('user_id', FilterController::getEmployeeIds());
                } else {
                    $datas->whereIn('user_id', FilterController::getEmployeeIds(1, "division", $officeDivisionID));
                }
            } else {
                $datas->whereIn('user_id', FilterController::getEmployeeIds(1, "department", $departmentIDs));
            }
        } else {
            $datas->whereIn('user_id', $userIDs);
        }
        $items = $datas->get();
        $employeeLeaveHistoryDeptWise = [];
        $departmentInformations = [];
        if ($items->count() > 0) {
            foreach ($items as $item) {
                $departmentInformations[$item->employee->currentPromotion->department->id]['department_name'] = $item->employee->currentPromotion->department->name;
                $departmentInformations[$item->employee->currentPromotion->department->id]['division_name'] = $item->employee->currentPromotion->officeDivision->name;
                $leaveArr['fingerprint_no'] = $item->employee->fingerprint_no;
                $leaveArr['employee_name'] = $item->employee->name;
                $leaveArr['office_division_name'] = $item->employee->currentPromotion->officeDivision->name;
                $leaveArr['department_name'] = $item->employee->currentPromotion->department->name;
                $leaveArr['leave_type'] = $item->leaveType->name;
                $leaveArr['request_duration'] = $item->from_date->format('Y-m-d') . ' to ' . $item->to_date->format('Y-m-d');
                $leaveArr['applied_date'] = date('Y-m-d', strtotime($item->created_at));
                $leaveArr['number_of_days'] = $item->number_of_days;
                $leaveArr['authorized_by'] = !empty($item->authorizedBy->name) ? $item->authorizedBy->name : "";
                $leaveArr['approved_by'] = !empty($item->approvedBy->name) ? $item->approvedBy->name : "";
                $leaveArr['purpose'] = !empty($item->purpose) ? $item->purpose : "";
                $leaveArr['status'] = !empty($item->status) ? $item->status : "";
                $employeeLeaveHistoryDeptWise[$item->employee->currentPromotion->department->id][] = $leaveArr;
            }
        }

        if ($request->type == 'excel' || $request->type == 'Export Excel') {
            $fileName = 'leave-history-' . $dateObj->format('F') . '-' . $year;
            $fileName = preg_replace('/[^A-Za-z0-9\-]/', '-', $fileName);
            $fileName .= '.xlsx';
            return Excel::download(new LeaveHistoryExport($employeeLeaveHistoryDeptWise, $departmentInformations, $monthAndYear), $fileName);

        } elseif ($request->type == 'pdf' || $request->type == 'Export PDF') {
            $page_name = "leave-history-reports.pdf.view-leave-history-listing-pdf";
            $pdf = PDF::loadView($page_name, compact("employeeLeaveHistoryDeptWise", "departmentInformations", "monthAndYear"));
            $file_name = 'leave-history-' . $dateObj->format('F') . '-' . $year . '.pdf';
            return $pdf->setPaper('a4', 'landscape')->download($file_name);
        }
        return view("leave-history-reports.view-leave-history-listing", compact("employeeLeaveHistoryDeptWise", "departmentInformations", "monthAndYear", "filter"));
    }
}
