<?php

namespace App\Http\Controllers;

use App\Models\Earning;
use App\Models\LeaveType;
use App\Models\PayGradeEarning;
use App\Models\UserLeave;
use App\Models\EmployeeStatus;
use App\Exports\Report\EmployeeWithoutBioMetric;
use App\Exports\Report\MonthlyAttendanceExport;
use App\Exports\Report\MonthlyMealExport;
use App\Exports\Report\MonthlySalaryExport;
use App\Exports\Report\YearlyLeaveExport;
use App\Exports\Report\YearlyLeaveExportNew;
use App\Http\Requests\report\RequestMonthlyAttendanceReport;
use App\Http\Requests\report\RequestMonthlyMealReport;
use App\Http\Requests\report\RequestMonthlySalaryReport;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\LeaveRequest;
use App\Models\LeaveUnpaid;
use App\Models\OfficeDivision;
use App\Models\PublicHoliday;
use App\Models\Roaster;
use App\Models\Salary;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserMeal;
use App\Models\WeeklyHoliday;
use App\Models\ZKTeco\Attendance;
use App\Models\ZKTeco\Attendance as ZKTeco;
use App\Models\ZKTeco\DailyAttendance;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateTime;
use Exception;
use Functions as HRMS;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;
use File;

class ReportController extends Controller
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
    public function attendanceReport()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("report.attendance-monthly", compact("data"));
    }

    /**
     * @return Factory|View
     */
    public function attendanceReportView()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("report.attendance-monthly-view", compact("data"));
    }

    /**
     * @return Factory|View
     */
    public function attendanceReportViewToSupervisor()
    {
        if(auth()->user()->hasRole([User::ROLE_ADMIN]) || auth()->user()->hasRole([User::ROLE_HR_ADMIN_SUPERVISOR])) {
            $data = [
                "officeDivisions" => OfficeDivision::select("id", "name")->get()
            ];
        } elseif(auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $officeDivisionIds = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("office_division_id");

            $data = [
                "officeDivisions" => OfficeDivision::whereIn("id", $officeDivisionIds)->select("id", "name")->get()
            ];
        } else {
            $officeDivisionIds = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("office_division_id");

            $data = [
                "officeDivisions" => OfficeDivision::whereIn("id", $officeDivisionIds)->select("id", "name")->get()
            ];
        }

        return view("report.attendance-monthly-view-supervisor", compact("data"));
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return BinaryFileResponse|null
     */
    public function generateAttendanceReportPdf(RequestMonthlyAttendanceReport $request)
    {
        try {
            $files = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";

                $firstDateOfMonth = $date;

                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

                $employees = $this->filterEmployees($request, $department, $month, $year);
                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = array();
                foreach ($employees as $employee) {
                    $leaveUnpaid = LeaveUnpaid::where("user_id", $employee->id)->whereMonth("leave_date", $month)->pluck("leave_date")->toArray();

                    # Determine all Weekly Holidays to the specific month
                    $roaster = Roaster::where("user_id", $employee->id)->get();

                    $departmentalWeeklyHoliday = WeeklyHoliday::whereDepartmentId($employee->currentPromotion->department_id)->first();

                    $publicHoliday = PublicHoliday::whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", ">=", $year)
                        ->whereYear("to_date", "<=", $year)
                        ->get();

                    # Determine all Public Holidays to the specific month
                    $publicHolidays = [];
                    foreach ($publicHoliday as $value) {
                        if($value->from_date != $value->to_date) {
                            $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                            foreach ($dateRange as $value) array_push($publicHolidays, $value);
                        } elseif ($value->from_date == $value->to_date) {
                            $date = new DateTime($value->from_date);
                            array_push($publicHolidays, $date->format("Y-m-d"));
                        }
                    }

                    # Leave Requests (Approved)
                    $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                        ->where(function ($query) use ($month) {
                            $query->whereMonth("from_date", ">=", $month)->orWhereMonth("to_date", ">=", $month);
                        })
                        ->where(function ($query) use ($year) {
                            $query->whereYear("from_date", ">=", $year)->orWhereYear("to_date", ">=", $year);
                        })
                        ->where("status", LeaveRequest::STATUS_APPROVED)
                        ->get();

                    $leaveRequests = [];
                    foreach ($leaveRequest as $value) {
                        $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                        foreach ($dateRange as $value) array_push($leaveRequests, $value);
                    }

                    $attendance = DailyAttendance::where("user_id", $employee->id)->where("emp_code", $employee->fingerprint_no)
                        ->whereMonth("time_in", (int)$month)
                        ->whereYear("time_in", (int)$year)
                        ->get();

                    $attendanceData = [];
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if ($attendance->count() > 0) {
                                $report = $attendance->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->time_in));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                $dayOfWeek = strtolower(date("D", strtotime($date)));

                                $entry = $report->first() ? date('h:iA', strtotime($report->first()->time_in)) : null;
                                $exit = $report->first() && !is_null($report->first()->time_out) ? date('h:iA', strtotime($report->first()->time_out)) : null;
                                $reason = null;

                                $roasterResult = $roaster->filter(function ($query) use ($date) {
                                    $tempDate = Carbon::createFromFormat('Y-m-d', $date);

                                    if(($tempDate >= $query->active_from) AND ($tempDate->format('Y-m-d') <= $query->end_date)) return $query;
                                });

                                if($roasterResult->count() > 0) {
                                    $weeklyHolidays = $roasterResult->count() > 0 ? $roasterResult->pluck("weekly_holidays")->first() : [];
                                } else {
                                    $weeklyHolidays = json_decode($departmentalWeeklyHoliday->days);
                                }

                                if(!isset($entry)) {
                                    if(isset($publicHolidays) && in_array($date, $publicHolidays)) $reason = "P";
                                    elseif(isset($weeklyHolidays) && in_array($dayOfWeek, $weeklyHolidays)) $reason = "W";
                                    elseif(isset($leaveRequest) && in_array($date, $leaveRequests)) $reason = "L";
                                    elseif(isset($leaveUnpaid) && in_array($date, $leaveUnpaid)) $reason = "A";
                                    else $reason = "---";
                                }

                                array_push($attendanceData, array(
                                    $date => array(
                                        "entry" => $entry,
                                        "exit"  => $exit,
                                        "reason"=> $reason
                                    )
                                ));
                            }
                        }
                    }

                    array_push($attendanceReport, array(
                        "employee"  => $employee,
                        "report"    => $attendanceData
                    ));
                }

                $result = [
                    "attendanceReport"  => $attendanceReport,
                    "department"        => $departmentName,
                    "monthAndYear"      => $monthAndYear,
                    "lastDayOfMonth"    => $lastDayOfMonth
                ];

                $fileName = "{$departmentName}-{$month}-{$year}.pdf";
                $path = "report/attendance/{$fileName}";

                if(count($request->input("department_id")) == 1) {
                    $pdf = PDF::loadView('report.attendance_report_pdf', compact("result"))->setPaper('a4', 'landscape');
                    return $pdf->download("${fileName}");
                }
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Unable to export Attendance Report!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return BinaryFileResponse|null
     */
    public function generateAttendanceReportPdfToSupervisor(RequestMonthlyAttendanceReport $request)
    {
        try {
            $files = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";

                $firstDateOfMonth = $date;

                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

                $employees = $this->filterEmployees($request, $department, $month, $year);
                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = array();
                foreach ($employees as $employee) {
                    $leaveUnpaid = LeaveUnpaid::where("user_id", $employee->id)->whereMonth("leave_date", $month)->pluck("leave_date")->toArray();

                    # Determine all Weekly Holidays to the specific month
                    $roaster = Roaster::where("user_id", $employee->id)->get();

                    $departmentalWeeklyHoliday = WeeklyHoliday::whereDepartmentId($employee->currentPromotion->department_id)->first();

                    $publicHoliday = PublicHoliday::whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", ">=", $year)
                        ->whereYear("to_date", "<=", $year)
                        ->get();

                    # Determine all Public Holidays to the specific month
                    $publicHolidays = [];
                    foreach ($publicHoliday as $value) {
                        if($value->from_date != $value->to_date) {
                            $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                            foreach ($dateRange as $value) array_push($publicHolidays, $value);
                        } elseif ($value->from_date == $value->to_date) {
                            $date = new DateTime($value->from_date);
                            array_push($publicHolidays, $date->format("Y-m-d"));
                        }
                    }

                    # Leave Requests (Approved)
                    $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                        ->where(function ($query) use ($month) {
                            $query->whereMonth("from_date", ">=", $month)->orWhereMonth("to_date", ">=", $month);
                        })
                        ->where(function ($query) use ($year) {
                            $query->whereYear("from_date", ">=", $year)->orWhereYear("to_date", ">=", $year);
                        })
                        ->where("status", LeaveRequest::STATUS_APPROVED)
                        ->get();

                    $leaveRequests = [];
                    foreach ($leaveRequest as $value) {
                        $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                        foreach ($dateRange as $value) array_push($leaveRequests, $value);
                    }

                    # Attendance Data from ZKTeco Devices
                    $attendance = DailyAttendance::where("user_id", $employee->id)->where("emp_code", $employee->fingerprint_no)
                        ->whereMonth("time_in", (int)$month)
                        ->whereYear("time_in", (int)$year)
                        ->get();

                    $attendanceData = array();
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if ($attendance->count() > 0) {
                                $report = $attendance->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->time_in));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                $dayOfWeek = strtolower(date("D", strtotime($date)));

                                $entry = $report->first() ? date('h:iA', strtotime($report->first()->time_in)) : null;
                                $exit = $report->first() && !is_null($report->first()->time_out) ? date('h:iA', strtotime($report->first()->time_out)) : null;
                                $reason = null;

                                $roasterResult = $roaster->filter(function ($query) use ($date) {
                                    $tempDate = Carbon::createFromFormat('Y-m-d', $date);

                                    if(($tempDate >= $query->active_from) AND ($tempDate->format('Y-m-d') <= $query->end_date)) return $query;
                                });

                                if($roasterResult->count() > 0) {
                                    $weeklyHolidays = $roasterResult->count() > 0 ? $roasterResult->pluck("weekly_holidays")->first() : [];
                                } else {
                                    $weeklyHolidays = json_decode($departmentalWeeklyHoliday->days);
                                }

                                if(!isset($entry)) {
                                    if(isset($publicHolidays) && in_array($date, $publicHolidays)) $reason = "P";
                                    elseif(isset($weeklyHolidays) && in_array($dayOfWeek, $weeklyHolidays)) $reason = "W";
                                    elseif(isset($leaveRequests) && in_array($date, $leaveRequests)) $reason = "L";
                                    elseif(isset($leaveUnpaid) && in_array($date, $leaveUnpaid)) $reason = "A";
                                    else $reason = "---";
                                }

                                array_push($attendanceData, array(
                                    $date => array(
                                        "entry" => $entry,
                                        "exit"  => $exit,
                                        "reason"=> $reason
                                    )
                                ));
                            }
                        }
                    }

                    array_push($attendanceReport, array(
                        "employee"  => $employee,
                        "report"    => $attendanceData
                    ));
                }

                $result = [
                    "attendanceReport"  => $attendanceReport,
                    "department"        => $departmentName,
                    "monthAndYear"      => $monthAndYear,
                    "lastDayOfMonth"    => $lastDayOfMonth
                ];

                $fileName = "{$departmentName}-{$month}-{$year}.pdf";
                $path = "report/attendance/{$fileName}";

                if(count($request->input("department_id")) == 1) {
                    $pdf = PDF::loadView('report.attendance_report_pdf', compact("result"))->setPaper('a4', 'landscape');
                    return $pdf->download("${fileName}");
                }
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Unable to export Attendance Report!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return RedirectResponse|BinaryFileResponse
     */
    public function generateAttendanceReportCSV(RequestMonthlyAttendanceReport $request)
    {
        try {
            $files = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";
                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $employees = $this->filterEmployees($request, $department, $month, $year);

                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "Sorry! Data not exists.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = array();
                foreach ($employees as $employee) {
                    $attendance = ZKTeco::with(["attendances" => function ($query) use ($month, $employee) {
                        $query->whereMonth("punch_time", (int)$month);
                    }])
                        ->whereEmpCode($employee->fingerprint_no)
                        ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                        ->first();

                    $attendanceData = array();
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if (!empty($attendance->attendances)) {
                                $report = $attendance->attendances->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->punch_time));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                array_push($attendanceData, array(
                                    $date => array(
                                        "entry" => $report->first(),
                                        "exit" => $report->first() != $report->last() ? $report->last() : null
                                    )
                                ));
                            }
                        }
                    }

                    array_push($attendanceReport, array(
                        "department" => $departmentName,
                        "employee" => $employee,
                        "report" => $attendanceData,
                        "monthAndYear" => $monthAndYear,
                        "lastDayOfMonth" => $lastDayOfMonth
                    ));
                }

                $fileName = "{$departmentName}-{$month}-{$year}.csv";
                $path = "report/attendance/{$fileName}";

                if(count($request->input("department_id")) == 1) {
                    Excel::store(new MonthlyAttendanceExport($attendanceReport), "{$fileName}", "reports", \Maatwebsite\Excel\Excel::CSV);

                    $destinationPath = base_path("reports/{$fileName}");
                    return response()->download($destinationPath);
                }

                # Download files to the storage path
                Excel::store(new MonthlyAttendanceExport($attendanceReport), $path, null, \Maatwebsite\Excel\Excel::CSV);

                # Store file names to download
                array_push($files, $departmentName);
            }

            # Zip Attendance Reports
            $response = $this->zipAttendanceReports($files, "app/report/attendance/");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return Factory|RedirectResponse|View
     */
    public function generateAttendanceReportView(RequestMonthlyAttendanceReport $request)
    {
        try {
            $reports = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";

                $firstDateOfMonth = $date;

                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

                $employees = $this->filterEmployees($request, $department, $month, $year);
                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = [];
                foreach ($employees as $employee) {
                    $leaveUnpaid = LeaveUnpaid::where("user_id", $employee->id)->whereMonth("leave_date", $month)->pluck("leave_date")->toArray();

                    # Determine all Weekly Holidays to the specific month
                    $roaster = Roaster::where("user_id", $employee->id)->get();

                    $departmentalWeeklyHoliday = WeeklyHoliday::whereDepartmentId($employee->currentPromotion->department_id)->first();

                    $publicHoliday = PublicHoliday::whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", ">=", $year)
                        ->whereYear("to_date", "<=", $year)
                        ->get();

                    # Determine all Public Holidays to the specific month
                    $publicHolidays = [];
                    foreach ($publicHoliday as $value) {
                        if($value->from_date != $value->to_date) {
                            $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                            foreach ($dateRange as $value) array_push($publicHolidays, $value);
                        } elseif ($value->from_date == $value->to_date) {
                            $date = new DateTime($value->from_date);
                            array_push($publicHolidays, $date->format("Y-m-d"));
                        }
                    }

                    # Leave Requests (Approved)
                    $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                        ->where(function ($query) use ($month) {
                            $query->whereMonth("from_date", ">=", $month)->orWhereMonth("to_date", ">=", $month);
                        })
                        ->where(function ($query) use ($year) {
                            $query->whereYear("from_date", ">=", $year)->orWhereYear("to_date", ">=", $year);
                        })
                        ->where("status", LeaveRequest::STATUS_APPROVED)
                        ->get();

                    $leaveRequests = [];
                    foreach ($leaveRequest as $value) {
                        $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                        foreach ($dateRange as $value) array_push($leaveRequests, $value);
                    }

                    $attendance = DailyAttendance::where("user_id", $employee->id)->where("emp_code", $employee->fingerprint_no)
                        ->whereMonth("time_in", (int)$month)
                        ->whereYear("time_in", (int)$year)
                        ->get();

                    $attendanceData = [];
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if ($attendance->count() > 0) {
                                $report = $attendance->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->time_in));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                $dayOfWeek = strtolower(date("D", strtotime($date)));

                                $entry = $report->first() ? date('h:iA', strtotime($report->first()->time_in)) : null;
                                $exit = $report->first() && !is_null($report->first()->time_out) ? date('h:iA', strtotime($report->first()->time_out)) : null;
                                $reason = null;

                                $roasterResult = $roaster->filter(function ($query) use ($date) {
                                    $tempDate = Carbon::createFromFormat('Y-m-d', $date);

                                    if(($tempDate >= $query->active_from) AND ($tempDate->format('Y-m-d') <= $query->end_date)) return $query;
                                });

                                if($roasterResult->count() > 0) {
                                    $weeklyHolidays = $roasterResult->count() > 0 ? $roasterResult->pluck("weekly_holidays")->first() : [];
                                } else {
                                    $weeklyHolidays = json_decode($departmentalWeeklyHoliday->days);
                                }

                                if(!isset($entry)) {
                                    if(!empty($publicHolidays) && in_array($date, $publicHolidays)) $reason = "P";
                                    elseif(!empty($weeklyHolidays) && in_array($dayOfWeek, $weeklyHolidays)) $reason = "W";
                                    elseif(!empty($leaveRequests) && in_array($date, $leaveRequests)) $reason = "L";
                                    elseif(!empty($leaveUnpaid) && in_array($date, $leaveUnpaid)) $reason = "A";
                                    else $reason = "---";
                                }

                                array_push($attendanceData, [
                                    $date => [
                                        "entry" => $entry,
                                        "exit"  => $exit,
                                        "reason"=> $reason
                                    ]
                                ]);
                            }
                        }
                    }

                    array_push($attendanceReport, [
                        "department"    => $departmentName,
                        "employee"      => $employee,
                        "report"        => $attendanceData,
                        "monthAndYear"  => $monthAndYear,
                        "lastDayOfMonth"=> $lastDayOfMonth,
                    ]);
                }

                array_push($reports, $attendanceReport);
            }

            $response = \view("report.attendance-monthly-view-result", compact("reports"));
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return Factory|RedirectResponse|View
     */
    public function generateAttendanceReportViewOld(RequestMonthlyAttendanceReport $request)
    {
        try {
            $reports = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";

                $firstDateOfMonth = $date;

                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

                $employees = $this->filterEmployees($request, $department, $month, $year);
                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = [];
                foreach ($employees as $employee) {
                    $leaveUnpaid = LeaveUnpaid::where("user_id", $employee->id)->whereMonth("leave_date", $month)->pluck("leave_date")->toArray();

                    # Determine all Weekly Holidays to the specific month
                    $roaster = Roaster::where("user_id", $employee->id)->get();

                    $departmentalWeeklyHoliday = WeeklyHoliday::whereDepartmentId($employee->currentPromotion->department_id)->first();

                    $publicHoliday = PublicHoliday::whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", ">=", $year)
                        ->whereYear("to_date", "<=", $year)
                        ->get();

                    # Determine all Public Holidays to the specific month
                    $publicHolidays = [];
                    foreach ($publicHoliday as $value) {
                        if($value->from_date != $value->to_date) {
                            $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                            foreach ($dateRange as $value) array_push($publicHolidays, $value);
                        } elseif ($value->from_date == $value->to_date) {
                            $date = new DateTime($value->from_date);
                            array_push($publicHolidays, $date->format("Y-m-d"));
                        }
                    }

                    # Leave Requests (Approved)
                    $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                        ->whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", $year)
                        ->whereYear("to_date", $year)
                        ->where("status", LeaveRequest::STATUS_APPROVED)
                        ->get();

                    $leaveRequests = [];
                    foreach ($leaveRequest as $value) {
                        $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                        foreach ($dateRange as $value) array_push($leaveRequests, $value);
                    }

                    $attendance = ZKTeco::with(["attendances" => function ($query) use ($month, $year, $employee) {
                        $query->whereMonth("punch_time", (int)$month)->whereYear("punch_time", (int)$year);
                    }])
                        ->whereEmpCode($employee->fingerprint_no)
                        ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                        ->first();

                    $attendanceData = [];
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if (!empty($attendance->attendances)) {
                                $report = $attendance->attendances->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->punch_time));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                $dayOfWeek = strtolower(date("D", strtotime($date)));

                                $entry = $report->first();
                                $exit  = $report->first() != $report->last() ? $report->last() : null;
                                $reason = null;

                                $roasterResult = $roaster->filter(function ($query) use ($date) {
                                    $tempDate = Carbon::createFromFormat('Y-m-d', $date);

                                    if(($tempDate >= $query->active_from) AND ($tempDate->format('Y-m-d') <= $query->end_date)) return $query;
                                });

                                if($roasterResult->count() > 0) {
                                    $weeklyHolidays = $roasterResult->count() > 0 ? $roasterResult->pluck("weekly_holidays")->first() : [];
                                } else {
                                    $weeklyHolidays = json_decode($departmentalWeeklyHoliday->days);
                                }

                                if(!isset($entry)) {
                                    if(in_array($date, $publicHolidays)) $reason = "P";
                                    elseif(in_array($dayOfWeek, $weeklyHolidays)) $reason = "W";
                                    elseif(in_array($date, $leaveRequests)) $reason = "L";
                                    elseif(in_array($date, $leaveUnpaid)) $reason = "A";
                                    else $reason = "---";
                                }

                                array_push($attendanceData, [
                                    $date => [
                                        "entry" => $entry,
                                        "exit"  => $exit,
                                        "reason"=> $reason
                                    ]
                                ]);
                            }
                        }
                    }

                    array_push($attendanceReport, [
                        "department"    => $departmentName,
                        "employee"      => $employee,
                        "report"        => $attendanceData,
                        "monthAndYear"  => $monthAndYear,
                        "lastDayOfMonth"=> $lastDayOfMonth
                    ]);
                }

                array_push($reports, $attendanceReport);
            }

            $response = \view("report.attendance-monthly-view-result", compact("reports"));
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlyAttendanceReport $request
     * @return Factory|RedirectResponse|View
     */
    public function generateAttendanceReportViewToSupervisor(RequestMonthlyAttendanceReport $request)
    {
        try {
            $reports = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";

                $firstDateOfMonth = $date;

                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;

                $employees = $this->filterEmployees($request, $department, $month, $year);
                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $attendanceReport = [];
                foreach ($employees as $employee) {
                    $leaveUnpaid = LeaveUnpaid::where("user_id", $employee->id)->whereMonth("leave_date", $month)->pluck("leave_date")->toArray();

                    # Determine all Weekly Holidays to the specific month
                    $roaster = Roaster::where("user_id", $employee->id)->get();

                    $departmentalWeeklyHoliday = WeeklyHoliday::whereDepartmentId($employee->currentPromotion->department_id)->first();

                    $publicHoliday = PublicHoliday::whereMonth("from_date", ">=", $month)
                        ->whereMonth("to_date", "<=", $month)
                        ->whereYear("from_date", ">=", $year)
                        ->whereYear("to_date", "<=", $year)
                        ->get();

                    # Determine all Public Holidays to the specific month
                    $publicHolidays = [];
                    foreach ($publicHoliday as $value) {
                        if($value->from_date != $value->to_date) {
                            $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                            foreach ($dateRange as $value) array_push($publicHolidays, $value);
                        } elseif ($value->from_date == $value->to_date) {
                            $date = new DateTime($value->from_date);
                            array_push($publicHolidays, $date->format("Y-m-d"));
                        }
                    }

                    # Leave Requests (Approved)
                    $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                        ->where(function ($query) use ($month) {
                            $query->whereMonth("from_date", ">=", $month)->orWhereMonth("to_date", ">=", $month);
                        })
                        ->where(function ($query) use ($year) {
                            $query->whereYear("from_date", ">=", $year)->orWhereYear("to_date", ">=", $year);
                        })
                        ->where("status", LeaveRequest::STATUS_APPROVED)
                        ->get();

                    $leaveRequests = [];
                    foreach ($leaveRequest as $value) {
                        $dateRange = HRMS::dateRange($value->from_date, $value->to_date);
                        foreach ($dateRange as $value) array_push($leaveRequests, $value);
                    }

                    $attendance = ZKTeco::with(["attendances" => function ($query) use ($month, $year, $employee) {
                        $query->whereMonth("punch_time", (int)$month)->whereYear("punch_time", (int)$year);
                    }])
                        ->whereEmpCode($employee->fingerprint_no)
                        ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                        ->first();

                    $attendanceData = [];
                    if (isset($attendance)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                            if (!empty($attendance->attendances)) {
                                $report = $attendance->attendances->filter(function ($item) use ($day) {
                                    $punchDate = (int)date("d", strtotime($item->punch_time));

                                    if ($punchDate == $day) return $item;
                                });

                                $date = $year . "-" . $month . "-" . $day;
                                $date = date("Y-m-d", strtotime($date));

                                $dayOfWeek = strtolower(date("D", strtotime($date)));

                                $entry = $report->first();
                                $exit  = $report->first() != $report->last() ? $report->last() : null;
                                $reason = null;

                                $roasterResult = $roaster->filter(function ($query) use ($date) {
                                    $tempDate = Carbon::createFromFormat('Y-m-d', $date);

                                    if(($tempDate >= $query->active_from) AND ($tempDate->format('Y-m-d') <= $query->end_date)) return $query;
                                });

                                if($roasterResult->count() > 0) {
                                    $weeklyHolidays = $roasterResult->count() > 0 ? $roasterResult->pluck("weekly_holidays")->first() : [];
                                } else {
                                    $weeklyHolidays = json_decode($departmentalWeeklyHoliday->days);
                                }

                                if(!isset($entry)) {
                                    if(isset($publicHolidays) && in_array($date, $publicHolidays)) $reason = "P";
                                    elseif(isset($weeklyHolidays) && in_array($dayOfWeek, $weeklyHolidays)) $reason = "W";
                                    elseif(isset($leaveRequests) && in_array($date, $leaveRequests)) $reason = "L";
                                    elseif(isset($leaveUnpaid) && in_array($date, $leaveUnpaid)) $reason = "A";
                                    else $reason = "---";
                                }

                                array_push($attendanceData, [
                                    $date => [
                                        "entry" => $entry,
                                        "exit"  => $exit,
                                        "reason"=> $reason
                                    ]
                                ]);
                            }
                        }
                    }

                    array_push($attendanceReport, [
                        "department"    => $departmentName,
                        "employee"      => $employee,
                        "report"        => $attendanceData,
                        "monthAndYear"  => $monthAndYear,
                        "lastDayOfMonth"=> $lastDayOfMonth
                    ]);
                }

                array_push($reports, $attendanceReport);
            }

            $response = \view("report.attendance-monthly-view-result-supervisor", compact("reports"));
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param $files
     * @param $basePath
     * @return BinaryFileResponse
     */
    protected function zipAttendanceReports($files, $basePath): BinaryFileResponse
    {
        $names = "";
        foreach ($files as $file) {
            $names .= $file . "-";
        }
        $dateAndTime = date('Y-m-d-H:i:sA');

        $zip = new ZipArchive();
        $zipFileName = "{$names}-{$dateAndTime}.zip";
        $destinationPath = base_path("reports/{$zipFileName}");

        if ($zip->open($destinationPath, ZipArchive::CREATE) == TRUE) {
            $files = File::files(storage_path($basePath));

            # Add File to Zip Queue
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }

            # Close the connection to ZIP
            $zip->close();

            # Unlink all generated files
            foreach ($files as $file) unlink($file);
        }

        return response()->download($destinationPath);
    }

    /**
     * @return Factory|View
     */
    public function salaryReport()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("report.salary-monthly", compact("data"));
    }

    /**
     * @return BinaryFileResponse
     */
    public function getIncompleteBiometricEmployee()
    {
        try {
            $employees = User::with(["currentPromotion" => function($query) {
                return $query->with("officeDivision", "department", "designation");
            }])->active()->select("id", "fingerprint_no", "name", "email", "phone")->get();
            $employeeWithoutBiometric = $employees->reject(function ($employee) {
                $imageOnAttendanceServer = env("ZKTECO_SERVER_PORT") . "/files/photo/${employee['fingerprint_no']}.jpg";
                $cURL = curl_init($imageOnAttendanceServer);
                curl_setopt($cURL, CURLOPT_NOBODY, true);
                curl_exec($cURL);
                $statusCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
                $exists = $statusCode === 200 ? true : false;

                if($exists === true) return $employee;
            })->values();

            activity('employee-export')->by(auth()->user())->log('Incomplete Biometric Data for Employee csv has been exported');
            $response = Excel::download(new EmployeeWithoutBioMetric($employeeWithoutBiometric), now()."incomplete-biometric-employees.csv");
        } catch (Exception $exception) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Internal Server Error');
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param RequestMonthlySalaryReport $request
     * @return BinaryFileResponse|null
     */
    protected function generateSalaryReport(RequestMonthlySalaryReport $request)
    {
        try {
            $files = [];
            # Parse month and year from datepicker input string
            $monthAndYear = \Functions::getMonthAndYearFromDatePicker($request->input("datepicker"));

            # Convert date from month and year
            $month = DateTime::createFromFormat('!m', $monthAndYear["month"]);
            $monthName = $month->format('F');

            $salaryForMonth = (string) "{$monthName} {$monthAndYear['year']}";

            $days = new DateTime(date("F", mktime(0, 0, 0, $monthAndYear["month"], 1)));
            $lastDay = $days->format("t");

            $salaries = [];
            $totalWorkingDaysValue = [];
            if(is_null($request->input('user_id'))) {
                foreach($request->input("department_id") as $key => $department_id) {
                    $totalWeeklyHolidays = $this->getAllWeeklyHolidaysByDepartment($department_id, $monthName, $monthAndYear["year"]);
                    $workingDays = $lastDay - count($totalWeeklyHolidays);
                    $totalWorkingDays = "{$workingDays}/{$lastDay}";

                    $salary = Salary::with("user.currentPromotion.designation")->where("month", $monthAndYear["month"])
                        ->where("year", $monthAndYear["year"])
                        ->where("department_id", $department_id)
                        ->get();

                    array_push($salaries, $salary);
                    array_push($totalWorkingDaysValue, $totalWorkingDays);
                }
            } else {
                $department_id = User::findOrFail($request->input('user_id'))->currentPromotion->department->id;

                $totalWeeklyHolidays = $this->getAllWeeklyHolidaysByDepartment($department_id, $monthName, $monthAndYear["year"]);
                $workingDays = $lastDay - count($totalWeeklyHolidays);
                $totalWorkingDays = "{$workingDays}/{$lastDay}";

                $salary = Salary::with("user.currentPromotion.designation")->where("month", $monthAndYear["month"])
                    ->where("user_id", $request->input('user_id'))
                    ->get();

                array_push($salaries, $salary);
                array_push($totalWorkingDaysValue, $totalWorkingDays);
            }

            foreach($salaries as $key => $salary) {
                $salaryReport = [
                    "month"             => $salaryForMonth,
                    "workingDays"       => $totalWorkingDaysValue[$key],
                    "preparation_date"  => today()->format("M d, Y"),
                    "salary"            => $salary
                ];

                if(is_null($request->input('user_id'))) {
                    if($salary->isEmpty()) {
                        continue;
                    }
                }


                $commonFileName = "salary-{$monthName}-{$monthAndYear['year']}-";
                $fileName = $commonFileName."{$salary->first()->department->name}.csv";
                $path = "report/salary/{$fileName}";

                Excel::store(new MonthlySalaryExport($salaryReport), $path, null, \Maatwebsite\Excel\Excel::CSV);

                array_push($files, $salary->first()->department_id);
            }

            $response = $this->zipSalaryReports($files, "app/report/salary/");

        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something happened wrong');
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param $files
     * @param $basePath
     * @return BinaryFileResponse
     */
    protected function zipSalaryReports($files, $basePath): BinaryFileResponse
    {
        $names = "";
        foreach ($files as $file) {
            $names .= $file . "-";
        }
        $dateAndTime = date('Y-m-d-H:i:sA');

        $zip = new ZipArchive();
        $zipFileName = "{$names}-{$dateAndTime}.zip";
        $destinationPath = base_path("reports/{$zipFileName}");

        if ($zip->open($destinationPath, ZipArchive::CREATE) == TRUE) {
            $files = File::files(storage_path($basePath));

            # Add File to Zip Queue
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }

            # Close the connection to ZIP
            $zip->close();

            # Unlink all generated files
            foreach ($files as $file) unlink($file);
        }

        return response()->download($destinationPath);
    }

    /**
     * @param Request $request
     * @param $department
     * @param $month
     * @param $year
     * @return mixed
     */
    protected function filterEmployees(Request $request, $department, $month, $year)
    {
        # Filter employees by date, department, office division
        $startTime = Carbon::createFromDate($year, $month)->startOfMonth()->subYear(50)->format("Y-m-d");
        $endTime = Carbon::createFromDate($year, $month)->lastOfMonth()->format("Y-m-d");

        $users = User::with("currentPromotion")->whereStatus(User::STATUS_ACTIVE)->whereBetween("created_at", array($startTime, $endTime))->select("id", "name", "email", "fingerprint_no")->get();
        if($request->has("user_id") AND !is_null($request->input("user_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request) {
                if(in_array($item->id, $request->input("user_id"))) return $item;
            });
        } elseif($request->has("department_id") AND !is_null($request->input("department_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request, $department) {
                if($item->currentPromotion->department_id == $department) return $item;
            });
        } elseif($request->has("office_division_id") AND !is_null($request->input("office_division_id"))) {
            $filteredUser = $users->filter(function ($item) use ($request) {
                if($item->currentPromotion->office_division_id == $request->input("office_division_id")) return $item;
            });
        }

        $filteredUser = $filteredUser->values()->values();

        return $filteredUser;
    }

    /**
     * @param $department
     * @param $monthName
     * @param $year
     * @return array
     */
    protected function getAllWeeklyHolidaysByDepartment($department, $monthName, $year)
    {
        $weeklyHolidays = WeeklyHoliday::where("department_id", $department)->first();

        $weeklyHolidays = json_decode($weeklyHolidays->days);

        $result = [];
        $allDays = [];
        foreach ($weeklyHolidays as $weeklyHoliday) {
            $startDay = Carbon::parse("First {$weeklyHoliday} of {$monthName} {$year}");
            $endDay = Carbon::parse("Last day of February 2021");

            $holidays = new \DatePeriod($startDay, CarbonInterval::week(), $endDay);
            foreach ($holidays as $day) {
                $allDays[] = $day->format("M d, Y");
            }

            usort($allDays, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });
        }

        foreach ($allDays as $day) {
            array_push($result, $day);
        }

        return $result;
    }

    public function mealReportView()
    {
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("report.meal-monthly-view", compact("data"));
    }

    public function generateMealReportView(RequestMonthlyMealReport $request)
    {
        try {
            $reports = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";
                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $employees = $this->filterEmployees($request, $department, $month, $year);

                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $mealReport = array();

                foreach ($employees as $employee) {
                    $meal = UserMeal::whereMonth("created_at", '=', $month)
                        ->where('user_id', $employee->id)
                        ->get();

                    $mealData = array();
                    if (isset($meal)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $report = $meal->filter(function ($item) use ($day) {
                                $punchDate = (int)date("d", strtotime($item->date));

                                if ($punchDate == $day) return $item;
                            });

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => $report->first(),
                                )
                            ));

                        }

                    } else {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => null
                                )
                            ));
                        }
                    }


                    array_push($mealReport, array(
                        "department" => $departmentName,
                        "employee" => $employee,
                        "report" => $mealData,
                        "monthAndYear" => $monthAndYear,
                        "lastDayOfMonth" => $lastDayOfMonth
                    ));
                }

                array_push($reports, $mealReport);

            }

            $response = \view("report.meal-monthly-view-result", compact("reports"));
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    public function generateMealReportPdf(RequestMonthlyMealReport $request)
    {
        try {
            $files = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";
                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $employees = $this->filterEmployees($request, $department, $month, $year);

                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $mealReport = array();
                foreach ($employees as $employee) {
                    $meal = UserMeal::whereMonth("created_at", '=', $month)
                        ->where('user_id', $employee->id)
                        ->get();

                    $mealData = array();
                    if (isset($meal)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $report = $meal->filter(function ($item) use ($day) {
                                $punchDate = (int)date("d", strtotime($item->date));

                                if ($punchDate == $day) return $item;
                            });

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => $report->first(),
                                )
                            ));

                        }

                    } else {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => null,
                                )
                            ));
                        }
                    }


                    array_push($mealReport, array(
                        "department" => $departmentName,
                        "employee" => $employee,
                        "report" => $mealData,
                        "monthAndYear" => $monthAndYear,
                        "lastDayOfMonth" => $lastDayOfMonth
                    ));
                }

                $result = [
                    "attendanceReport"  => $mealReport,
                    "department"        => $departmentName,
                    "monthAndYear"      => $monthAndYear,
                    "lastDayOfMonth"    => $lastDayOfMonth
                ];

                $fileName = "{$departmentName}-{$month}-{$year}.pdf";
                $path = "report/meal/{$fileName}";

                if(count($request->input("department_id")) == 1) {
                    $pdf = PDF::loadView('report.meal_report_pdf', compact("result"))->setPaper('a4', 'landscape');
                    return $pdf->download("${fileName}");
                }
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Unable to export Attendance Report!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @return Factory|View
     */
    public function leaveReport()
    {
        if(auth()->user()->hasRole([User::ROLE_ADMIN]) || auth()->user()->hasRole([User::ROLE_HR_ADMIN_SUPERVISOR])) {
            $data = [
                "officeDivisions" => OfficeDivision::select("id", "name")->get()
            ];
        } elseif(auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $officeDivisionIds = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("office_division_id");

            $data = [
                "officeDivisions" => OfficeDivision::whereIn("id", $officeDivisionIds)->select("id", "name")->get()
            ];
        } else {
            $officeDivisionIds = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("office_division_id");

            $data = [
                "officeDivisions" => OfficeDivision::whereIn("id", $officeDivisionIds)->select("id", "name")->get()
            ];
        }

        return view("report.leave-monthly", compact("data"));
    }

    //NEW LEAVE REPORT
    public function leaveReportYearly()
    {
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id',$divisionIds)->get();
            $departmentIds=[];
            foreach ($data['officeDepartments'] as $item){
                $departmentIds[] = $item->id;
            }
        }else{
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
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
        }
        $departmentIds_in_string = implode(',',$departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        return view("report.leave_report.leave_report", compact('data'));
    }

    public function generateLeaveReportYearly(Request $request) {
        try{
            ini_set('max_execution_time', '300');
            $office_division_id = $request->office_division_id;
            $year = $request->datepicker;
            $current_year = date('Y');
            if($year>$current_year){
                session()->flash("type", "error");
                session()->flash("message", "Please select current or previous year!!");
                return redirect()->back();
            }
            if(isset($request->type) && ($request->type == 'excel' || $request->type == 'pdf')){
                $department_ids = json_decode($request->department_id);
                $user_ids = json_decode($request->user_id);
            }else{
                $department_ids = $request->department_id;
                $user_ids = $request->user_id;
            }
            $today = date('Y-m-d');
            $filter=[];
            $filter['office_division_id']=$office_division_id;
            $filter['department_id']=$department_ids;
            $filter['user_id']=$user_ids;
            $filter['datepicker']=$year;
            if($office_division_id=='all'){
                $find_division=true;
            }else{
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            }else{
                $find_department=false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee=true;
            }else{
                $find_employee=false;
            }
            if($find_employee){
                if($find_department){
                    if($find_division){
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }else{
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                            $departmentIds=[];
                            foreach ($departments as $item){
                                $departmentIds[] = $item->id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }
                }else{
                    $departmentIds_in_string = implode(',',$department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                $user_ids_in_string = implode(',',$user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }
            if(!isset($user_ids_in_string)){
                $user_ids=[];
                foreach($users as $key=>$user){
                    if($key==0){
                        $user_ids_in_string=$user->id;
                    }else{
                        $user_ids_in_string.=','.$user->id;
                    }
                    $user_ids[]=$user->id;
                }
            }
            $sql_yearly_leave="SELECT users.id, user_leaves.initial_leave, user_leaves.total_initial_leave, user_leaves.`leaves`, user_leaves.total_leaves, user_leaves.`year` FROM users INNER JOIN user_leaves ON user_leaves.user_id = users.id AND user_leaves.id =( SELECT MIN( user_l.id) FROM user_leaves AS user_l WHERE user_l.`year` = '$year' AND user_l.user_id = users.id GROUP BY user_leaves.user_id ) WHERE users.`status` = 1 AND users.id IN( $user_ids_in_string)";
            $sum_results = DB::select($sql_yearly_leave);
            $leave_types = LeaveType::all();
            $summary_data = [];
            $employee_information = [];
            $department_information = [];
            foreach($users as $u){
                $department_information[$u->department_id]['department_name'] = $u->department_name;
                $department_information[$u->department_id]['division_name'] = $u->division_name;
                foreach($leave_types as $type){
                    $summary_data[$u->department_id][$u->id][$type->id]['total_leave'] = 0;
                    $summary_data[$u->department_id][$u->id][$type->id]['total_leave_consume'] = 0;
                    $summary_data[$u->department_id][$u->id][$type->id]['total_leave_balance'] = 0;
                }
                $employee_information[$u->id]=$u;
            }
            foreach ($sum_results as $item){
                $initial_leave = json_decode($item->initial_leave);
                $leave_balance = json_decode($item->leaves);
                foreach($initial_leave as $total_given_leave){
                    $summary_data[$employee_information[$item->id]->department_id][$item->id][$total_given_leave->leave_type_id]['total_leave'] = $total_given_leave->total_days;
                }
                foreach($leave_balance as $total_balance){
                    $summary_data[$employee_information[$item->id]->department_id][$item->id][$total_balance->leave_type_id]['total_leave_balance'] = $total_balance->total_days;
                    $summary_data[$employee_information[$item->id]->department_id][$item->id][$total_balance->leave_type_id]['total_leave_consume'] = $summary_data[$employee_information[$item->id]->department_id][$item->id][$total_balance->leave_type_id]['total_leave'] - $summary_data[$employee_information[$item->id]->department_id][$item->id][$total_balance->leave_type_id]['total_leave_balance'];
                }
            }
            if(isset($request->type)){
                if($request->type=='excel' || $request->type=='Export Excel'){
                    $spreadsheet = new Spreadsheet();
                    $header = array(
                        'Leave Report'
                    );
                    $header2 = array(
                        'ID',
                        'Name',
                        'Joining Date',
                    );
                    $header4 = array(
                        'Total Leave ',
                        'Leave Consume',
                        'Leave Balance',
                    );
                    //FOR HEADER
                    $col = 1;
                    $row = 1;
                    $spreadsheet->getActiveSheet()
                        ->getStyle("A1:I2")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    foreach ($header as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col+8,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor('000')
                            ->setRGB('fbf4f4');
                    }
                    $col = 1;
                    $row = 3;
                    $spreadsheet->getActiveSheet()
                        ->getStyle("A3:C4")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    foreach ($header2 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('EEE2E0');
                        $col++;
                    }
                    $col = 4;
                    $row = 3;
                    $spreadsheet->getActiveSheet()
                        ->getStyle("D3:I3")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    foreach ($leave_types as $type) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col+2,$row,$row))
                            ->setCellValueByColumnAndRow($col, $row, $type->name)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('EEE2E0');
                        $col=$col+3;
                    }
                    $col = 4;
                    $row = 4;
                    $spreadsheet->getActiveSheet()
                        ->getStyle("D4:I4")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    for ($i=0; $i < 2; $i++) {
                        foreach ($header4 as $val) {
                            $spreadsheet->getActiveSheet()
                                ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row))
                                ->setCellValueByColumnAndRow($col, $row, $val)
                                ->getStyleByColumnAndRow($col, $row)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('EEE2E0');
                            $col++;
                        }
                    }
                    $row=5;
                    foreach($summary_data as $department_id=>$department_employees){
                        $col=1;
                        $colspan = 9;
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$colspan,$row,$row))
                            ->setCellValueByColumnAndRow($col, $row, $department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name'])
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('d3d3d3');
                        $row++;
                        foreach($department_employees as $user_id=>$summary_individual){
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->fingerprint_no);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->name);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->action_date);
                            $col++;
                            foreach ($leave_types as $type) {
                                $spreadsheet->getActiveSheet()
                                    ->setCellValueByColumnAndRow($col, $row, $summary_individual[$type->id]['total_leave']);
                                $col++;
                                $spreadsheet->getActiveSheet()
                                    ->setCellValueByColumnAndRow($col, $row, $summary_individual[$type->id]['total_leave_consume']);
                                $col++;
                                $spreadsheet->getActiveSheet()
                                    ->setCellValueByColumnAndRow($col, $row, $summary_individual[$type->id]['total_leave_balance']);
                                $col++;
                            }
                            $row++;
                            $col=1;
                        }
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("D6:I$row")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $writer = new Xlsx($spreadsheet);
                    $response =  new StreamedResponse(
                        function () use ($writer) {
                            $writer->save('php://output');
                        }
                    );
                    $file_name = 'Yearly-Leave'.'-'.$year;
                    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
                    $response->headers->set('Content-Disposition', 'attachment;filename="'.$file_name.'.xlsx"');
                    $response->headers->set('Cache-Control','max-age=0');
                    return $response;
                }elseif($request->type=='pdf' || $request->type=='Export PDF'){
                    $page_name = "report.pdf.yearly-new-leave-report-pdf-view";
                    $pdf = PDF::loadView($page_name, compact("year","summary_data","employee_information","department_information","filter","leave_types"));
                    $file_name = 'Yearly-Leave'.'-'.$year.'.pdf';
                    return $pdf->setPaper('a4', 'portrait')->download($file_name);
                }else{
                    return redirect()->back();
                }
            }else{
                return view("report.leave_report.show_leave_report", compact("year","summary_data","employee_information","department_information","filter","leave_types"));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            return redirect()->back();
        }
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function generateLeaveReport(Request $request)
    {
        try {
            activity('leave-report-export-csv')->by(auth()->user())->log('Leave Report CSV Exported');

            $redirect = Excel::download(new YearlyLeaveExport($request->all()), now()."leave-report.csv");

            session()->flash('message', 'Report Generated Successfully');
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');

            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestMonthlyMealReport $request
     * @return RedirectResponse|BinaryFileResponse
     */
    public function generateMealReportCsv(RequestMonthlyMealReport $request)
    {
        try {
            $files = [];

            foreach ($request->input("department_id") as $department) {
                # Department Name
                $departmentName = Department::find($department)->name;

                $datePicker = explode("-", $request->input("datepicker"));
                $month = $datePicker[0];
                $year = $datePicker[1];

                # Get month and Year
                $dateObj = DateTime::createFromFormat('!m', $month);
                $monthName = $dateObj->format('F');
                $monthAndYear = $monthName . ", " . $year;

                $date = $year . "-" . $month . "-" . "01";
                $date = new DateTime($date);
                $date = $date->format('t');
                $lastDayOfMonth = (int)$date;

                $employees = $this->filterEmployees($request, $department, $month, $year);

                $employees = $employees->sortBy("fingerprint_no")->values();

                # Check employee existence on the specific department
                if (count($employees) == 0) {
                    session()->flash("type", "error");
                    session()->flash("message", "{$departmentName} doesn't have any employees yet.");
                    return redirect()->back();
                }

                # Attendance Report
                $mealReport = array();
                foreach ($employees as $employee) {
                    $meal = UserMeal::whereMonth("created_at", '=', $month)
                        ->where('user_id', $employee->id)
                        ->get();

                    $mealData = array();
                    if (isset($meal)) {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $report = $meal->filter(function ($item) use ($day) {
                                $punchDate = (int)date("d", strtotime($item->date));

                                if ($punchDate == $day) return $item;
                            });

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => $report->first(),
                                )
                            ));

                        }

                    } else {
                        for ($day = 1; $day <= $lastDayOfMonth; $day++) {

                            $date = $year . "-" . $month . "-" . $day;
                            $date = date("Y-m-d", strtotime($date));

                            array_push($mealData, array(
                                $date => array(
                                    "status" => null,
                                )
                            ));
                        }
                    }


                    array_push($mealReport, array(
                        "department" => $departmentName,
                        "employee" => $employee,
                        "report" => $mealData,
                        "monthAndYear" => $monthAndYear,
                        "lastDayOfMonth" => $lastDayOfMonth
                    ));
                }


                $fileName = "{$departmentName}-{$month}-{$year}.csv";
                $path = "report/meal/{$fileName}";

                if(count($request->input("department_id")) == 1) {
                    Excel::store(new MonthlyMealExport($mealReport), "{$fileName}", "reports", \Maatwebsite\Excel\Excel::CSV);

                    $destinationPath = base_path("reports/{$fileName}");
                    return response()->download($destinationPath);
                }

                # Download files to the storage path
                Excel::store(new MonthlyMealExport($mealReport), $path, null, \Maatwebsite\Excel\Excel::CSV);

                # Store file names to download
                array_push($files, $departmentName);
            }

            # Zip Attendance Reports
            $response = $this->zipAttendanceReports($files, "app/report/meal/");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @return Factory|View
     */
    public function departmentOrIndividualMonthlyAttendanceReportView()
    {
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id',$divisionIds)->get();
            $departmentIds=[];
            foreach ($data['officeDepartments'] as $item){
                $departmentIds[] = $item->id;
            }
        }else{
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
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
        }
        $departmentIds_in_string = implode(',',$departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        return view("report.individual-or-department-wise-attendance-monthly-view", compact("data"));
    }

    protected function getDepartmentSupervisorIds()
    {
        $divisionSupervisor = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->orderByDesc("id")->pluck("office_division_id")->toArray();
        $departmentSupervisor = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id")->toArray();

        if(count($divisionSupervisor) > 0) {
            $departmentIds = Department::whereIn("office_division_id", $divisionSupervisor)->pluck("id")->toArray();
        } elseif(count($departmentSupervisor) > 0) {
            $departmentIds = $departmentSupervisor;
        } else {
            $departmentIds = [];
        }

        return $departmentIds;
    }

    public function getDepartmentAndEmployeeByOfficeDivision(Request $request, $forSalary = false){
        $filter_obj = new FilterController();
        if (((!$forSalary) && auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) || ($forSalary && auth()->user()->can('Show All Salary List'))) {
            if($request->office_division_id=='all'){
                $departments = Department::select("id", "name")->get();
                $employees = User::select("id", "name", "fingerprint_no")->active()->get();
            }else{
                $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                $departmentIds=[];
                foreach ($departments as $item){
                    $departmentIds[] = $item->id;
                }
                $departmentIds_in_string = implode(',',$departmentIds);
                $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        }else{
            if($request->office_division_id=='all'){
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
                $departments = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
                $departmentIds_in_string = implode(',',$departmentIds);
                $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }else{
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
                $departments = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
                $departmentIds_in_string = implode(',',$departmentIds);
                $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        }
        return response()->json(["departments" => $departments,"employees" => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmployeesByDepartmentOrDivision(Request $request)
    {
        $departments = $request->department_id;
        $office_division_id = $request->office_division_id;
        $find_department = false;
        $find_division = false;
        foreach($departments as $dept){
            if($dept=="all"){
                $find_department=true;
                if($office_division_id=="all"){
                    $find_division = true;
                }
            }
        }
        if(!$find_department){
            $departmentIds_in_string = implode(',',$departments);
            $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
        }else{
            if($find_division){
                if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                    $employees = User::select("id", "name", "fingerprint_no")->active()->get();
                }else{
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
                    $departmentIds_in_string = implode(',',$departmentIds);
                    $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                    $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                    $departmentIds=[];
                    foreach ($departments as $item){
                        $departmentIds[] = $item->id;
                    }
                    $departmentIds_in_string = implode(',',$departmentIds);
                    $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
                }else{
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
                    $departmentIds_in_string = implode(',',$departmentIds);
                    $employees = getEmployeesByDepartmentIDs($departmentIds_in_string);
                }
            }
        }
        return response()->json(array("data" => $employees));
    }

    public function generateMonthlyAttendanceReportView(Request $request){
        try {
            ini_set('max_execution_time', '300');
            $office_division_id = $request->office_division_id;
            $month_year = $request->datepicker;
            if(isset($request->type) && ($request->type == 'excel' || $request->type == 'pdf')){
                $department_ids = json_decode($request->department_id);
                $user_ids = json_decode($request->user_id);
            }else{
                $department_ids = $request->department_id;
                $user_ids = $request->user_id;
            }
            $filter=[];
            $filter['office_division_id']=$office_division_id;
            $filter['department_id']=$department_ids;
            $filter['user_id']=$user_ids;
            $filter['datepicker']=$month_year;
            $month_year = explode("-", $month_year);
            $month = $month_year[0];
            $year = $month_year[1];
            $dateObj = DateTime::createFromFormat('!m', $month);
            $monthName = $dateObj->format('F');
            $monthAndYear = $monthName . ", " . $year;
            $YearAndmonth = $year."-".$month;
            $date = $year . "-" . $month . "-" . "01";
            $firstDateOfMonth = $date;
            $date = new DateTime($date);
            $date = $date->format('t');
            $lastDayOfMonth = (int)$date;
            $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;
            $today = date('Y-m-d');
            if($office_division_id=='all'){
                $find_division=true;
            }else{
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            }else{
                $find_department=false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee=true;
            }else{
                $find_employee=false;
            }
            if($find_employee){
                if($find_department){
                    if($find_division){
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }else{
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                            $departmentIds=[];
                            foreach ($departments as $item){
                                $departmentIds[] = $item->id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }
                }else{
                    $departmentIds_in_string = implode(',',$department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                $user_ids_in_string = implode(',',$user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }
            if(!isset($user_ids_in_string)){
                $user_ids=[];
                foreach($users as $key=>$user){
                    if($key==0){
                        $user_ids_in_string=$user->id;
                    }else{
                        $user_ids_in_string.=','.$user->id;
                    }
                    $user_ids[]=$user->id;
                }
            }
            $summary_data = [];
            $employee_information = [];
            $department_information = [];
            $emp_codes = [];
            $emp_code_in_string = '';
            foreach($users as $key=>$u){
                if($key==0){
                    $emp_code_in_string=$u->fingerprint_no;
                }else{
                    $emp_code_in_string.=','.$u->fingerprint_no;
                }
                $emp_codes[]=$u->fingerprint_no;
                $department_information[$u->department_id]['department_name']=$u->department_name;
                $department_information[$u->department_id]['division_name']=$u->division_name;
                $summary_data[$u->id]['total_days']=0;
                $summary_data[$u->id]['total_weekly_holidays']=0;
                $summary_data[$u->id]['total_public_holidays']=0;
                $summary_data[$u->id]['total_relax_days']=0;
                $summary_data[$u->id]['total_present']=0;
                $summary_data[$u->id]['total_absent']=0;
                $summary_data[$u->id]['total_leave']=0;
                $summary_data[$u->id]['total_late_days']=0;
                $summary_data[$u->id]['total_late_mins']=0;
                $summary_data[$u->id]['total_regular_working_days']=0;
                $summary_data[$u->id]['total_weekend_working_days']=0;
                $summary_data[$u->id]['total_public_working_days']=0;
                $summary_data[$u->id]['total_working_mins']=0;
                $summary_data[$u->id]['average_working_mins']=0;
                $summary_data[$u->id]['total_overtime_mins']=0;
                $summary_data[$u->id]['total_working_days']=0;
                $employee_information[$u->id]=$u;
            }
            //summary data prepare start
            $summary_sql = "SELECT id, `user_id`, `emp_code`, COUNT( date) AS total_days, SUM( is_public_holiday ) AS total_public_holidays, SUM( is_weekly_holiday ) AS total_weekly_holidays, SUM( is_relax_day ) AS total_relax_days, SUM( present_count ) AS total_present, SUM( absent_count ) AS total_absent, SUM( leave_count ) AS total_leave, SUM( is_late_final ) AS total_late, SUM( late_min_final ) AS total_late_min, SUM( working_min ) AS total_working_min, SUM( overtime_min ) AS total_ot_min,(SELECT SUM( d_a.present_count ) FROM `daily_attendances` AS d_a WHERE d_a.user_id = daily_attendances.user_id AND d_a.`is_weekly_holiday` = '1' AND d_a.`date` LIKE '$YearAndmonth%' ) AS total_weekend_working_days, (SELECT SUM( d_a1.present_count ) FROM `daily_attendances` AS d_a1 WHERE d_a1.user_id = daily_attendances.user_id AND d_a1.`is_public_holiday` = '1' AND d_a1.`date` LIKE '$YearAndmonth%') AS total_public_working_days, (SELECT SUM( d_a2.present_count ) FROM `daily_attendances` AS d_a2 WHERE d_a2.user_id = daily_attendances.user_id AND d_a2.`is_public_holiday` <> '1' AND d_a2.`is_weekly_holiday` <> '1' AND d_a2.`date` LIKE '$YearAndmonth%') AS total_regular_working_days FROM `daily_attendances` WHERE `user_id` IN ( $user_ids_in_string ) AND `date` LIKE '$YearAndmonth%' GROUP BY `user_id`";
            $summary_record = DB::select($summary_sql);
            foreach ($summary_record as $sum_data){
                $summary_data[$sum_data->user_id]['total_days'] = $sum_data->total_days;
                $summary_data[$sum_data->user_id]['total_weekly_holidays']=$sum_data->total_weekly_holidays;
                $summary_data[$sum_data->user_id]['total_public_holidays']=$sum_data->total_public_holidays;
                $summary_data[$sum_data->user_id]['total_relax_days']=$sum_data->total_relax_days;
                $summary_data[$sum_data->user_id]['total_present'] = $sum_data->total_present;
                $summary_data[$sum_data->user_id]['total_absent'] = $sum_data->total_absent;
                $summary_data[$sum_data->user_id]['total_leave'] = $sum_data->total_leave;
                $summary_data[$sum_data->user_id]['total_late_days'] = $sum_data->total_late ? $sum_data->total_late : 0;
                $summary_data[$sum_data->user_id]['total_late_mins'] = $sum_data->total_late_min ? $sum_data->total_late_min : 0;
                $summary_data[$sum_data->user_id]['total_working_mins'] = $sum_data->total_working_min ? $sum_data->total_working_min : 0;
                $summary_data[$sum_data->user_id]['total_overtime_mins'] = $sum_data->total_ot_min ? $sum_data->total_ot_min:0;
                $summary_data[$sum_data->user_id]['total_regular_working_days']= $sum_data->total_regular_working_days ? $sum_data->total_regular_working_days : 0;
                $summary_data[$sum_data->user_id]['total_weekend_working_days']= $sum_data->total_weekend_working_days ? $sum_data->total_weekend_working_days : 0;
                $summary_data[$sum_data->user_id]['total_public_working_days']= $sum_data->total_public_working_days ? $sum_data->total_public_working_days : 0;
                $summary_data[$sum_data->user_id]['total_working_days'] = $sum_data->total_days-($sum_data->total_weekly_holidays+
                        $sum_data->total_public_holidays+$sum_data->total_relax_days);
                $summary_data[$sum_data->user_id]['average_working_mins'] = ($sum_data->total_working_min > 0 && $sum_data->total_present > 0) ? ($sum_data->total_working_min/$sum_data->total_present) : 0;
            }
            //summary data prepare end
            //Get today attendance record from Attendance Database
            $today_emp_attendance = [];
            $current_date = date('Y-m-d');
            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
            $attendanceCountStartTime = date("H:i:s", strtotime($attendanceCountStartTime));
            $startDateTime = $current_date.' '.$attendanceCountStartTime;
            $today_attendance = Attendance::whereDate("punch_time", $current_date)
                ->where("punch_time", '>=', $startDateTime)
                ->whereIn('emp_code',$emp_codes)
                ->orderBy("id")
                ->select("id", "emp_code", "punch_time")
                ->get();
            foreach($today_attendance as $attr){
                if(!isset($today_emp_attendance[$attr->emp_code]['time_in'])){
                    $today_emp_attendance[$attr->emp_code]['time_in'] = date('h:iA',strtotime($attr->punch_time));
                    $today_emp_attendance[$attr->emp_code]['time_out'] = '';
                }else{
                    $today_emp_attendance[$attr->emp_code]['time_out'] = date('h:iA',strtotime($attr->punch_time));
                }
            }

            //End today attendance
            //All attendance start
            $all_attendance_records = DailyAttendance::whereBetween('date',array($firstDateOfMonth,$lastDateOfMonth))
                ->whereIn('user_id',$user_ids)->get();
            $employee_attendance = [];
            foreach($all_attendance_records as $record){
                $employee_attendance[$record->user_id][$record->date] = $record;
            }
            //All attendance end
            $employee_monthly_attendance_summary = [];
            $all_dates = [];
            foreach($employee_information as $emp_info){
                $begin = new DateTime($firstDateOfMonth);
                $end = new DateTime($lastDateOfMonth);
                for($i = $begin; $i <= $end; $i->modify('+1 day')){
                    $all_dates[$i->format('Y-m-d')]=$i->format("D");




                    if(isset($employee_attendance[$emp_info->id][$i->format('Y-m-d')])){ //check daily attendance record
                        //is_weekly_holiday start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'W';
                        }
                        //is_weekly_holiday end
                        //is_public_holiday start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'H';
                        }
                        //is_public_holiday end
                        //present_count start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->present_count>0){
                            if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_late_final){
                                $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - L';
                                if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                                    $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - L - W';
                                }
                                if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                                    $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - L - H';
                                }
                            }else{
                                $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P';
                                if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                                    $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - W';
                                }
                                if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                                    $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - H';
                                }
                            }
                        }else{
                            if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->absent_count){
                                $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A';
                            }
                            if(!($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->holiday_count) && $employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_relax_day){
                                $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'R';
                            }
                        }
                        //present_count end
                        //check leave start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->leave_count>0){
                            if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->leave_count==1){
                                $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'L';
                            }else{
                                if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->present_count>0){
                                    if($employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] == 'P'){
                                        $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - Half';
                                    }elseif($employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] == 'P - L'){
                                        $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - L - Half';
                                    }
                                }else{
                                    if($current_date==$i->format('Y-m-d')){
                                        if(!isset($today_emp_attendance[$emp_info->fingerprint_no]['time_in'])){
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A - Half';
                                        }else{
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P - Half';
                                        }
                                    }else{
                                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'H - Half';
                                        }
                                        elseif($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'W - Half';
                                        }
                                        elseif($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_relax_day){
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'R - Half';
                                        }
                                        else{
                                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A - Half';
                                        }
                                    }
                                }
                            }
                        }
                        //check leave end




                    }else{ //daily attendance record when missing
                        $date_in_loop=date_create($i->format('Y-m-d'));
                        $today=date_create(date('Y-m-d'));
                        $diff=date_diff($date_in_loop,$today);
                        $differ = (int)$diff->format("%R%a");
                        if($differ<0){
                            $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = '--';
                        }else{
                            if($current_date==$i->format('Y-m-d')){
                                $summary_data[$emp_info->id]['total_days']++;
                                if(!isset($employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')])){
                                    if(!isset($today_emp_attendance[$emp_info->fingerprint_no]['time_in'])){
                                        $summary_data[$emp_info->id]['total_absent']++;
                                        $summary_data[$emp_info->id]['total_working_days']--;
                                        $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A';
                                    }else{
                                        $summary_data[$emp_info->id]['total_present']++;
                                        $summary_data[$emp_info->id]['total_working_days']++;
                                        $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'P';
                                    }
                                }
                            }else{
                                if(!isset($employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')])){
                                    $employee_monthly_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = '-';
                                }
                            }
                        }
                    }
                }
            }
            if(isset($request->type)){
                if($request->type=='excel' || $request->type=='Export Excel'){
                    $spreadsheet = new Spreadsheet();
                    $col = 1;
                    $row = 1;
                    $header1 = array(
                        'Employee Name',
                        'Joining date'
                    );
                    foreach ($header1 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    foreach ($all_dates as $key=>$date) {
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, ''.substr($key, -2));
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row+1, $date);
                        $spreadsheet->getActiveSheet()
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $spreadsheet->getActiveSheet()
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $header1_1 = array(
                        'Total Days',
                        'Total Working Days'
                    );
                    foreach ($header1_1 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col+2,$row,$row))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Holidays')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');


                    $header1_1_3 = array(
                        'Weekend Holiday',
                        'Official Holiday',
                        'Relax Day'
                    );
                    foreach ($header1_1_3 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row+1,$row+1))
                            ->setCellValueByColumnAndRow($col, $row+1, $val)
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Leave')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');
                    $col++;

                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col+2,$row,$row))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Attendance (Days)')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');
                    $header2_1_3 = array(
                        'Regualar Duty',
                        'Weekend Holiday',
                        'Official Holiday'
                    );
                    foreach ($header2_1_3 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row+1,$row+1))
                            ->setCellValueByColumnAndRow($col, $row+1, $val)
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $header2 = array(
                        'Total Present',
                        'Total Absent',
                        'Total Late (days)',
                        'Total Late (hours)',
                        'Total Working hours',
                        'Daily Average Working Hours',
                        'Total Overtime (hours)'
                    );

                    foreach ($header2 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("A1:AW2")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $col=1;
                    $row=3;
                    foreach($employee_monthly_attendance_summary as $department_id=>$department_employees){
                        $colspan = count($all_dates)+18;
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$colspan,$row,$row))
                            ->setCellValueByColumnAndRow($col, $row, $department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name'])
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('d3d3d3');
                        $row++;
                        $col=1;
                        foreach($department_employees as $user_id=>$summary_individual){
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->fingerprint_no.' - '.$employee_information[$user_id]->name);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->action_date);
                            $col++;
                            foreach($all_dates as $key=>$date){
                                $spreadsheet->getActiveSheet()
                                    ->setCellValueByColumnAndRow($col, $row, $summary_individual[$key]);
                                $col++;
                            }
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_weekly_holidays']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_public_holidays']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_relax_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_leave']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_regular_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_weekend_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_public_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_present']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_absent']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_late_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_late_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_working_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['average_working_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_overtime_mins']));
                            $row++;
                            $col=1;
                        }
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("AE4:AW$row")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $writer = new Xlsx($spreadsheet);
                    $response =  new StreamedResponse(
                        function () use ($writer) {
                            $writer->save('php://output');
                        }
                    );
                    $file_name = 'Monthly-Attendance-'.$monthName.'-'.$year;
                    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
                    $response->headers->set('Content-Disposition', 'attachment;filename="'.$file_name.'.xlsx"');
                    $response->headers->set('Cache-Control','max-age=0');
                    return $response;
                }elseif($request->type=='pdf' || $request->type=='Export PDF'){
                    $page_name = "report.pdf.individual-or-department-wise-attendance-monthly-pdf-view";
                    $pdf = PDF::loadView($page_name, compact("monthAndYear","all_dates","summary_data","employee_information","employee_monthly_attendance_summary","department_information","filter"));
                    $file_name = 'Monthly-Attendance-'.$monthName.'-'.$year.'.pdf';
                    return $pdf->setPaper('a3', 'landscape')->download($file_name);
                }else{
                    return redirect()->back();
                }
            }else{
                return view("report.individual-or-department-wise-attendance-monthly-view-result", compact("monthAndYear","all_dates","summary_data","employee_information","employee_monthly_attendance_summary","department_information","filter"));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            return redirect()->back();
        }
    }

    function cellsToMergeByColsRow($start = -1, $end = -1, $row1 = -1,$row2 = -1){
        $merge = 'A1:A1';
        if($start>=0 && $end>=0 && $row1>=0 && $row2>=0){
            $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($start);
            $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($end);
            $merge = "$start{$row1}:$end{$row2}";
        }
        return $merge;
    }

    public function departmentOrIndividualYearlyAttendanceReportView(){
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id',$divisionIds)->get();
            $departmentIds=[];
            foreach ($data['officeDepartments'] as $item){
                $departmentIds[] = $item->id;
            }
        }else{
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
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
        }
        $departmentIds_in_string = implode(',',$departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        return view("report.individual-or-department-wise-attendance-yearly-view", compact("data"));
    }

    public function generateYearlyAttendanceReportView(Request $request){
        try {
            ini_set('max_execution_time', '300');
            $office_division_id = $request->office_division_id;
            $year = $request->datepicker;
            $current_year = date('Y');
            if($year>$current_year){
                session()->flash("type", "error");
                session()->flash("message", "Please select current or previous year!!");
                return redirect()->back();
            }
            if(isset($request->type) && ($request->type == 'excel' || $request->type == 'pdf')){
                $department_ids = json_decode($request->department_id);
                $user_ids = json_decode($request->user_id);
            }else{
                $department_ids = $request->department_id;
                $user_ids = $request->user_id;
            }
            $today = date('Y-m-d');
            $filter=[];
            $filter['office_division_id']=$office_division_id;
            $filter['department_id']=$department_ids;
            $filter['user_id']=$user_ids;
            $filter['datepicker']=$year;
            if($office_division_id=='all'){
                $find_division=true;
            }else{
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            }else{
                $find_department=false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee=true;
            }else{
                $find_employee=false;
            }
            if($find_employee){
                if($find_department){
                    if($find_division){
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }else{
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                            $departmentIds=[];
                            foreach ($departments as $item){
                                $departmentIds[] = $item->id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }
                }else{
                    $departmentIds_in_string = implode(',',$department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                $user_ids_in_string = implode(',',$user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }
            if(!isset($user_ids_in_string)){
                $user_ids=[];
                foreach($users as $key=>$user){
                    if($key==0){
                        $user_ids_in_string=$user->id;
                    }else{
                        $user_ids_in_string.=','.$user->id;
                    }
                    $user_ids[]=$user->id;
                }
            }
            $summary_data = [];
            $employee_information = [];
            $department_information = [];
            foreach($users as $u){
                $department_information[$u->department_id]['department_name'] = $u->department_name;
                $department_information[$u->department_id]['division_name'] = $u->division_name;
                $summary_data[$u->department_id][$u->id]['total_days'] = 0;
                $summary_data[$u->department_id][$u->id]['total_working_days'] = 0;
                $summary_data[$u->department_id][$u->id]['total_holidays'] = 0;
                $summary_data[$u->department_id][$u->id]['total_present'] = 0;
                $summary_data[$u->department_id][$u->id]['total_absent'] = 0;
                $summary_data[$u->department_id][$u->id]['total_leave'] = 0;
                $summary_data[$u->department_id][$u->id]['total_late_days'] = 0;
                $summary_data[$u->department_id][$u->id]['total_late_mins'] = 0;
                $summary_data[$u->department_id][$u->id]['total_working_mins'] = 0;
                $summary_data[$u->department_id][$u->id]['total_overtime_mins'] = 0;
                $summary_data[$u->department_id][$u->id]['average_working_mins'] = 0;
                $employee_information[$u->id]=$u;
            }
            $sql_sum="SELECT id, `user_id`, `emp_code`, COUNT( date) AS total_days, SUM( is_public_holiday) AS total_public_holidays, SUM( is_weekly_holiday ) AS total_weekly_holidays, SUM( is_relax_day ) AS total_relax_days, SUM( present_count ) AS total_present, SUM( absent_count ) AS total_absent, SUM( leave_count ) AS total_leave, SUM( is_late_final ) AS total_late, SUM( late_min_final ) AS total_late_min, SUM( working_min ) AS total_working_min, SUM( overtime_min ) AS total_ot_min FROM `daily_attendances` WHERE `user_id` IN( $user_ids_in_string ) AND YEAR(`date`) = '$year' GROUP BY `user_id`";
            $sum_results = DB::select($sql_sum);
            foreach ($sum_results as $item){
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_days'] = $item->total_days;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_working_days'] = $item->total_days-($item->total_weekly_holidays+$item->total_public_holidays+$item->total_relax_days);
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_holidays'] = $item->total_weekly_holidays+$item->total_public_holidays+$item->total_relax_days;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_present'] = $item->total_present;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_absent'] = $item->total_absent;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_leave'] = $item->total_leave;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_late_days'] = $item->total_late ? $item->total_late : 0;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_late_mins'] = convertMinToHrMinSec($item->total_late_min);
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_working_mins'] = convertMinToHrMinSec($item->total_working_min);
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['average_working_mins'] = ($item->total_working_min > 0) ? ($item->total_working_min/$item->total_present) : 0;
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['average_working_mins'] = convertMinToHrMinSec($summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['average_working_mins']);
                $summary_data[$employee_information[$item->user_id]->department_id][$item->user_id]['total_overtime_mins'] = convertMinToHrMinSec($item->total_ot_min);
            }
            if(isset($request->type)){
                if($request->type=='excel' || $request->type=='Export Excel'){
                    $spreadsheet = new Spreadsheet();
                    $col = 1;
                    $row = 1;
                    $header1 = array(
                        'Employee ID',
                        'Employee Name',
                        'Joining date'
                    );
                    foreach ($header1 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $header2 = array(
                        'Total Days',
                        'Total Working Days',
                        'Total Holiday',
                        'Total Present',
                        'Total Absent',
                        'Total Leave',
                        'Total Late (days)',
                        'Total Late (hours)',
                        'Total Working hours',
                        'Average Working Hours',
                        'Total Overtime (hours)'
                    );
                    foreach ($header2 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("A1:N2")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $col=1;
                    $row=3;
                    foreach($summary_data as $department_id=>$department_employees){
                        $colspan = count($header1)+count($header2);
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$colspan,$row,$row))
                            ->setCellValueByColumnAndRow($col, $row, $department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name'])
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('d3d3d3');
                        $row++;
                        $col=1;
                        foreach($department_employees as $user_id=>$summary_individual){
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->fingerprint_no);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->name);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->action_date);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_holidays']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_present']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_absent']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_leave']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_late_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_late_mins']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_working_mins']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_individual['total_overtime_mins']);
                            $row++;
                            $col=1;
                        }
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("D4:N$row")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $writer = new Xlsx($spreadsheet);
                    $response =  new StreamedResponse(
                        function () use ($writer) {
                            $writer->save('php://output');
                        }
                    );
                    $file_name = 'Yearly-Attendance-'.'-'.$year;
                    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
                    $response->headers->set('Content-Disposition', 'attachment;filename="'.$file_name.'.xlsx"');
                    $response->headers->set('Cache-Control','max-age=0');
                    return $response;
                }elseif($request->type=='pdf' || $request->type=='Export PDF'){
                    $page_name = "report.pdf.individual-or-department-wise-attendance-yearly-pdf-view";
                    $pdf = PDF::loadView($page_name, compact("year","summary_data","employee_information","department_information","filter"));
                    $file_name = 'Yearly-Attendance-'.'-'.$year.'.pdf';
                    return $pdf->setPaper('a3', 'landscape')->download($file_name);
                }else{
                    return redirect()->back();
                }
            }else{
                return view("report.individual-or-department-wise-attendance-yearly-view-result", compact("year","summary_data","employee_information","department_information","filter"));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            return redirect()->back();
        }
    }

    public function departmentOrIndividualTimebaseMonthlyAttendanceReportView(){
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id',$divisionIds)->get();
            $departmentIds=[];
            foreach ($data['officeDepartments'] as $item){
                $departmentIds[] = $item->id;
            }
        }else{
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
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
        }
        $departmentIds_in_string = implode(',',$departmentIds);
        $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
        return view("report.individual-or-department-wise-timebase-attendance-monthly-view", compact("data"));
    }

    public function generateMonthlyTimebaseAttendanceReportView(Request $request){
        try {
            ini_set('max_execution_time', '300');
            $office_division_id = $request->office_division_id;
            $month_year = $request->datepicker;
            if(isset($request->type) && ($request->type == 'excel' || $request->type == 'pdf')){
                $department_ids = json_decode($request->department_id);
                $user_ids = json_decode($request->user_id);
            }else{
                $department_ids = $request->department_id;
                $user_ids = $request->user_id;
            }
            $filter=[];
            $filter['office_division_id']=$office_division_id;
            $filter['department_id']=$department_ids;
            $filter['user_id']=$user_ids;
            $filter['datepicker']=$month_year;
            $month_year = explode("-", $month_year);
            $month = $month_year[0];
            $year = $month_year[1];
            $dateObj = DateTime::createFromFormat('!m', $month);
            $monthName = $dateObj->format('F');
            $monthAndYear = $monthName . ", " . $year;
            $YearAndmonth = $year."-".$month;
            $date = $year . "-" . $month . "-" . "01";
            $firstDateOfMonth = $date;
            $date = new DateTime($date);
            $date = $date->format('t');
            $lastDayOfMonth = (int)$date;
            $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;
            $today = date('Y-m-d');
            if($office_division_id=='all'){
                $find_division=true;
            }else{
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            }else{
                $find_department=false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee=true;
            }else{
                $find_employee=false;
            }
            if($find_employee){
                if($find_department){
                    if($find_division){
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id AND `pm`.`promoted_date` <= '$today') INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }else{
                        if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                            $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                            $departmentIds=[];
                            foreach ($departments as $item){
                                $departmentIds[] = $item->id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
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
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }
                }else{
                    $departmentIds_in_string = implode(',',$department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                $user_ids_in_string = implode(',',$user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }
            if(!isset($user_ids_in_string)){
                $user_ids=[];
                foreach($users as $key=>$user){
                    if($key==0){
                        $user_ids_in_string=$user->id;
                    }else{
                        $user_ids_in_string.=','.$user->id;
                    }
                    $user_ids[]=$user->id;
                }
            }
            $summary_data = [];
            $employee_information = [];
            $department_information = [];
            $emp_codes = [];
            $emp_code_in_string = '';
            foreach($users as $key=>$u){
                if($key==0){
                    $emp_code_in_string=$u->fingerprint_no;
                }else{
                    $emp_code_in_string.=','.$u->fingerprint_no;
                }
                $emp_codes[]=$u->fingerprint_no;
                $department_information[$u->department_id]['department_name']=$u->department_name;
                $department_information[$u->department_id]['division_name']=$u->division_name;
                $summary_data[$u->id]['total_days']=0;
                $summary_data[$u->id]['total_weekly_holidays']=0;
                $summary_data[$u->id]['total_public_holidays']=0;
                $summary_data[$u->id]['total_relax_days']=0;
                $summary_data[$u->id]['total_present']=0;
                $summary_data[$u->id]['total_absent']=0;
                $summary_data[$u->id]['total_leave']=0;
                $summary_data[$u->id]['total_late_days']=0;
                $summary_data[$u->id]['total_late_mins']=0;
                $summary_data[$u->id]['total_regular_working_days']=0;
                $summary_data[$u->id]['total_weekend_working_days']=0;
                $summary_data[$u->id]['total_public_working_days']=0;
                $summary_data[$u->id]['total_working_mins']=0;
                $summary_data[$u->id]['average_working_mins']=0;
                $summary_data[$u->id]['total_overtime_mins']=0;
                $summary_data[$u->id]['total_working_days']=0;
                $employee_information[$u->id]=$u;
            }
            //summary data prepare start
            $summary_sql = "SELECT id, `user_id`, `emp_code`, COUNT( date) AS total_days, SUM( is_public_holiday ) AS total_public_holidays, SUM( is_weekly_holiday ) AS total_weekly_holidays, SUM( is_relax_day ) AS total_relax_days, SUM( present_count ) AS total_present, SUM( absent_count ) AS total_absent, SUM( leave_count ) AS total_leave, SUM( is_late_final ) AS total_late, SUM( late_min_final ) AS total_late_min, SUM( working_min ) AS total_working_min, SUM( overtime_min ) AS total_ot_min,(SELECT SUM( d_a.present_count ) FROM `daily_attendances` AS d_a WHERE d_a.user_id = daily_attendances.user_id AND d_a.`is_weekly_holiday` = '1' AND d_a.`date` LIKE '$YearAndmonth%' ) AS total_weekend_working_days, (SELECT SUM( d_a1.present_count ) FROM `daily_attendances` AS d_a1 WHERE d_a1.user_id = daily_attendances.user_id AND d_a1.`is_public_holiday` = '1' AND d_a1.`date` LIKE '$YearAndmonth%') AS total_public_working_days, (SELECT SUM( d_a2.present_count ) FROM `daily_attendances` AS d_a2 WHERE d_a2.user_id = daily_attendances.user_id AND d_a2.`is_public_holiday` <> '1' AND d_a2.`is_weekly_holiday` <> '1' AND d_a2.`date` LIKE '$YearAndmonth%') AS total_regular_working_days FROM `daily_attendances` WHERE `user_id` IN ( $user_ids_in_string ) AND `date` LIKE '$YearAndmonth%' GROUP BY `user_id`";
            $summary_record = DB::select($summary_sql);
            foreach ($summary_record as $sum_data){
                $summary_data[$sum_data->user_id]['total_days'] = $sum_data->total_days;
                $summary_data[$sum_data->user_id]['total_weekly_holidays']=$sum_data->total_weekly_holidays;
                $summary_data[$sum_data->user_id]['total_public_holidays']=$sum_data->total_public_holidays;
                $summary_data[$sum_data->user_id]['total_relax_days']=$sum_data->total_relax_days;
                $summary_data[$sum_data->user_id]['total_present'] = $sum_data->total_present;
                $summary_data[$sum_data->user_id]['total_absent'] = $sum_data->total_absent;
                $summary_data[$sum_data->user_id]['total_leave'] = $sum_data->total_leave;
                $summary_data[$sum_data->user_id]['total_late_days'] = $sum_data->total_late ? $sum_data->total_late : 0;
                $summary_data[$sum_data->user_id]['total_late_mins'] = $sum_data->total_late_min ? $sum_data->total_late_min : 0;
                $summary_data[$sum_data->user_id]['total_working_mins'] = $sum_data->total_working_min ? $sum_data->total_working_min : 0;
                $summary_data[$sum_data->user_id]['total_overtime_mins'] = $sum_data->total_ot_min ? $sum_data->total_ot_min:0;
                $summary_data[$sum_data->user_id]['total_regular_working_days']= $sum_data->total_regular_working_days ? $sum_data->total_regular_working_days : 0;
                $summary_data[$sum_data->user_id]['total_weekend_working_days']= $sum_data->total_weekend_working_days ? $sum_data->total_weekend_working_days : 0;
                $summary_data[$sum_data->user_id]['total_public_working_days']= $sum_data->total_public_working_days ? $sum_data->total_public_working_days : 0;
                $summary_data[$sum_data->user_id]['total_working_days'] = $sum_data->total_days-($sum_data->total_weekly_holidays+
                        $sum_data->total_public_holidays+$sum_data->total_relax_days);
                $summary_data[$sum_data->user_id]['average_working_mins'] = ($sum_data->total_working_min > 0 & $sum_data->total_present > 0) ? ($sum_data->total_working_min/$sum_data->total_present) : 0;
            }
            //summary data prepare end
            //Get today attendance record from Attendance Database
            $today_emp_attendance = [];
            $current_date = date('Y-m-d');
            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
            $attendanceCountStartTime = date("H:i:s", strtotime($attendanceCountStartTime));
            $startDateTime = $current_date.' '.$attendanceCountStartTime;
            $today_attendance = Attendance::whereDate("punch_time", $current_date)
                ->where("punch_time", '>=', $startDateTime)
                ->whereIn('emp_code',$emp_codes)
                ->orderBy("id")
                ->select("id", "emp_code", "punch_time")
                ->get();
            foreach($today_attendance as $attr){
                if(!isset($today_emp_attendance[$attr->emp_code]['time_in'])){
                    $today_emp_attendance[$attr->emp_code]['time_in'] = date('h:iA',strtotime($attr->punch_time));
                    $today_emp_attendance[$attr->emp_code]['time_out'] = '';
                }else{
                    $today_emp_attendance[$attr->emp_code]['time_out'] = date('h:iA',strtotime($attr->punch_time));
                }
            }
            //End today attendance
            //All attendance start
            $all_attendance_records = DailyAttendance::whereBetween('date',array($firstDateOfMonth,$lastDateOfMonth))
                ->whereIn('user_id',$user_ids)->get();
            $employee_attendance = [];
            foreach($all_attendance_records as $record){
                $employee_attendance[$record->user_id][$record->date] = $record;
            }
            //All attendance end
            $employee_monthly_timebase_attendance_summary = [];
            $all_dates = [];
            foreach($employee_information as $emp_info){
                $begin = new DateTime($firstDateOfMonth);
                $end = new DateTime($lastDateOfMonth);
                for($i = $begin; $i <= $end; $i->modify('+1 day')){
                    $all_dates[$i->format('Y-m-d')]=$i->format("D");
                    $time_in='';
                    $time_out='';
                    if(isset($employee_attendance[$emp_info->id][$i->format('Y-m-d')])){ //check daily attendance record
                        //is_weekly_holiday start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'W';
                        }
                        //is_weekly_holiday end
                        //is_public_holiday start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'H';
                        }
                        //is_public_holiday end
                        //present_count start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->present_count>0){
                            if(!empty($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->time_in)){
                                $time_in = date('h:iA', strtotime($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->time_in));
                            }
                            if(!empty($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->time_out)){
                                $time_out = date('h:iA', strtotime($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->time_out));
                            }
                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')]=[];
                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')]['time'] =  $time_in.'-'.$time_out;
                        }else{
                            if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->absent_count){
                                $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A';
                            }
                            if(!($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->holiday_count) && $employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_relax_day){
                                $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'R';
                            }
                        }
                        //present_count end
                        //check leave start
                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->leave_count>0){
                            if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->leave_count==1){
                                $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'L';
                            } else{
                                if($current_date==$i->format('Y-m-d')){
                                    if(!isset($today_emp_attendance[$emp_info->fingerprint_no]['time_in'])){
                                        $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A - Half';
                                    }
                                }else{
                                    if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->present_count==0){
                                        if($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_public_holiday){
                                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'H - Half';
                                        }
                                        elseif($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_weekly_holiday){
                                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'W - Half';
                                        }
                                        elseif($employee_attendance[$emp_info->id][$i->format('Y-m-d')]->is_relax_day){
                                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'R - Half';
                                        }
                                        else{
                                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A - Half';
                                        }
                                    }
                                }
                            }
                        }
                        //check leave end
                    }else{ //daily attendance record when missing
                        $date_in_loop=date_create($i->format('Y-m-d'));
                        $today=date_create(date('Y-m-d'));
                        $diff=date_diff($date_in_loop,$today);
                        $differ = (int)$diff->format("%R%a");
                        if($differ<0){
                            $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = '--';
                        }else{
                            if($current_date==$i->format('Y-m-d')){
                                $summary_data[$emp_info->id]['total_days']++;
                                if(!isset($employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')])){
                                    if(!isset($today_emp_attendance[$emp_info->fingerprint_no]['time_in'])){
                                        $summary_data[$emp_info->id]['total_absent']++;
                                        $summary_data[$emp_info->id]['total_working_days']--;
                                        $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = 'A';
                                    }else{
                                        $summary_data[$emp_info->id]['total_present']++;
                                        $summary_data[$emp_info->id]['total_working_days']++;
                                        $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')]=[];
                                        $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')]['time'] =  $today_emp_attendance[$emp_info->fingerprint_no]['time_in'].'-'.$today_emp_attendance[$emp_info->fingerprint_no]['time_out'];
                                    }
                                }
                            }else{
                                if(!isset($employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')])){
                                    $employee_monthly_timebase_attendance_summary[$emp_info->department_id][$emp_info->id][$i->format('Y-m-d')] = '-';
                                }
                            }
                        }
                    }
                }
            }
            if(isset($request->type)){
                if($request->type=='excel' || $request->type=='Export Excel'){
                    $spreadsheet = new Spreadsheet();
                    $col = 1;
                    $row = 1;
                    $header1 = array(
                        'Employee Name',
                        'Joining date'
                    );
                    foreach ($header1 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    foreach ($all_dates as $key=>$date) {
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, ''.substr($key, -2));
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row+1, $date);
                        $spreadsheet->getActiveSheet()
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $spreadsheet->getActiveSheet()
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $header1_1 = array(
                        'Total Days',
                        'Total Working Days'
                    );
                    foreach ($header1_1 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col+2,$row,$row))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Holidays')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');


                    $header1_1_3 = array(
                        'Weekend Holiday',
                        'Official Holiday',
                        'Relax Day'
                    );
                    foreach ($header1_1_3 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row+1,$row+1))
                            ->setCellValueByColumnAndRow($col, $row+1, $val)
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Leave')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');
                    $col++;

                    $spreadsheet->getActiveSheet()
                        ->mergeCells($this->cellsToMergeByColsRow($col,$col+2,$row,$row))
                        ->setCellValueByColumnAndRow($col, $row, 'Total Attendance (Days)')
                        ->getStyleByColumnAndRow($col, $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('edbf83');
                    $header2_1_3 = array(
                        'Regualar Duty',
                        'Weekend Holiday',
                        'Official Holiday'
                    );
                    foreach ($header2_1_3 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row+1,$row+1))
                            ->setCellValueByColumnAndRow($col, $row+1, $val)
                            ->getStyleByColumnAndRow($col, $row+1)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $header2 = array(
                        'Total Present',
                        'Total Absent',
                        'Total Late (days)',
                        'Total Late (hours)',
                        'Total Working hours',
                        'Daily Average Working Hours',
                        'Total Overtime (hours)'
                    );

                    foreach ($header2 as $val) {
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                            ->setCellValueByColumnAndRow($col, $row, $val)
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('edbf83');
                        $col++;
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("A1:AW2")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $col=1;
                    $row=3;
                    foreach($employee_monthly_timebase_attendance_summary as $department_id=>$department_employees){
                        $colspan = count($all_dates)+18;
                        $spreadsheet->getActiveSheet()
                            ->mergeCells($this->cellsToMergeByColsRow($col,$colspan,$row,$row))
                            ->setCellValueByColumnAndRow($col, $row, $department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name'])
                            ->getStyleByColumnAndRow($col, $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('d3d3d3');
                        $row++;
                        $col=1;
                        foreach($department_employees as $user_id=>$summary_individual){
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->fingerprint_no.' - '.$employee_information[$user_id]->name);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $employee_information[$user_id]->action_date);
                            $col++;
                            foreach($all_dates as $key=>$date){
                                $spreadsheet->getActiveSheet()
                                    ->setCellValueByColumnAndRow($col, $row, $summary_individual[$key]['time'] ?? $summary_individual[$key]);
                                $col++;
                            }
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_weekly_holidays']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_public_holidays']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_relax_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_leave']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_regular_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_weekend_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_public_working_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_present']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_absent']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, $summary_data[$user_id]['total_late_days']);
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_late_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_working_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['average_working_mins']));
                            $col++;
                            $spreadsheet->getActiveSheet()
                                ->setCellValueByColumnAndRow($col, $row, convertMinToHrMinSec($summary_data[$user_id]['total_overtime_mins']));
                            $row++;
                            $col=1;
                        }
                    }
                    $spreadsheet->getActiveSheet()
                        ->getStyle("AH4:AQ$row")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $writer = new Xlsx($spreadsheet);
                    $response =  new StreamedResponse(
                        function () use ($writer) {
                            $writer->save('php://output');
                        }
                    );
                    $file_name = 'Monthly-Attendance-'.$monthName.'-'.$year;
                    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
                    $response->headers->set('Content-Disposition', 'attachment;filename="'.$file_name.'.xlsx"');
                    $response->headers->set('Cache-Control','max-age=0');
                    return $response;
                }elseif($request->type=='pdf' || $request->type=='Export PDF'){
                    $page_name = "report.pdf.individual-or-department-wise-timebase-attendance-monthly-pdf-view";
                    $pdf = PDF::loadView($page_name, compact("monthAndYear","all_dates","summary_data","employee_information","department_information","filter","employee_monthly_timebase_attendance_summary"));
                    $file_name = 'Monthly-Attendance-'.$monthName.'-'.$year.'.pdf';
                    return $pdf->setPaper('a3', 'landscape')->download($file_name);
                }else{
                    return redirect()->back();
                }
            }else{
                return view("report.individual-or-department-wise-timebase-attendance-monthly-view-result", compact("monthAndYear","all_dates","summary_data","employee_information","department_information","filter","employee_monthly_timebase_attendance_summary"));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            return redirect()->back();
        }
    }
}
