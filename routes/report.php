<?php

use App\Http\Controllers\LeaveHistoryReportsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

# Report to Attendance
## (Admin)
Route::get("/attendance", [ReportController::class, "attendanceReport"])->name("attendanceReport")->middleware(['can:Generate Attendance Report']);
Route::get("/attendance-view", [ReportController::class, "attendanceReportView"])->name("attendanceReportView")->middleware(['can:Generate Attendance Report']);

Route::get("/attendance/generate/pdf", [ReportController::class, "generateAttendanceReportPdf"])->name("generateAttendanceReportPdf")->middleware(['can:Generate Attendance Report']);
Route::get("/attendance/generate/csv", [ReportController::class, "generateAttendanceReportCSV"])->name("generateAttendanceReportCsv")->middleware(['can:Generate Attendance Report']);
Route::get("/attendance/generate/view", [ReportController::class, "generateAttendanceReportView"])->name("generateAttendanceReportView")->middleware(['can:Generate Attendance Report']);

## Supervisor
Route::get("/attendance-view/supervisor", [ReportController::class, "attendanceReportViewToSupervisor"])->name("attendanceReportViewToSupervisor")->middleware(['can:Generate Attendance Report to Supervisor']);

Route::get("/attendance/generate/pdf/supervisor", [ReportController::class, "generateAttendanceReportPdfToSupervisor"])->name("generateAttendanceReportPdfToSupervisor")->middleware(['can:Generate Attendance Report to Supervisor']);
Route::get("/attendance/generate/csv/supervisor", [ReportController::class, "generateAttendanceReportCSV"])->name("generateAttendanceReportCsvToSupervisor")->middleware(['can:Generate Attendance Report to Supervisor']);
Route::get("/attendance/generate/view/supervisor", [ReportController::class, "generateAttendanceReportViewToSupervisor"])->name("generateAttendanceReportViewToSupervisor")->middleware(['can:Generate Attendance Report to Supervisor']);

# Report to Salary
Route::get("/salary", [ReportController::class, "salaryReport"])->name("salaryReport")->middleware(['can:Generate Salary Report']);
Route::post("/salary/generate", [ReportController::class, "generateSalaryReport"])->name("generateSalaryReport")->middleware(['can:Generate Salary Report']);

# Report to Incomplete Biometric Data
Route::get("/incomplete-biometric", [ReportController::class, "getIncompleteBiometricEmployee"])->name("incompleteBiometric")->middleware(['can:Incomplete Biometric Data']);

# Report to Leave
Route::get("/leave", [ReportController::class, "leaveReport"])->name("leaveReport")->middleware(['can:Generate Leave Report']);
Route::post("/leave", [ReportController::class, "generateLeaveReport"])->name("generateLeaveReport")->middleware(['can:Generate Leave Report']);

# New Report to Leave
Route::get("/leave-report-yearly", [ReportController::class, "leaveReportYearly"])->name("leaveReportYearly")->middleware(['can:Generate Leave Report']);
Route::get("/generate-leave-report-yearly", [ReportController::class, "generateLeaveReportYearly"])->name("generateLeaveReportYearly")->middleware(['can:Generate Leave Report']);
Route::get("/download-leave-report-yearly", [ReportController::class, "downloadLeaveReportYearly"])->name("downloadLeaveReportYearly")->middleware(['can:Generate Leave Report']);

# Report to Meal

Route::get("/meal-view", [ReportController::class, "mealReportView"])->name("mealReportView");
Route::get("/meal/generate/view", [ReportController::class, "generateMealReportView"])->name("generateMealReportView")->middleware(['can:View Meal Reports']);
Route::get("/meal/generate/pdf", [ReportController::class, "generateMealReportPdf"])->name("generateMealReportPdf")->middleware(['can:Generate Meal Report Pdf']);
Route::get("/meal/generate/csv", [ReportController::class, "generateMealReportCsv"])->name("generateMealReportCsv")->middleware(['can:Generate Meal Report Csv']);

Route::get("/attendance-view-monthly", [ReportController::class, "departmentOrIndividualMonthlyAttendanceReportView"])->name("departmentOrIndividualMonthlyAttendanceReportView")->middleware(['can:Department Wise OR Individual Monthly Attendance Report']);
Route::get("/timebase-attendance-view-monthly", [ReportController::class, "departmentOrIndividualTimebaseMonthlyAttendanceReportView"])->name("departmentOrIndividualTimebaseMonthlyAttendanceReportView")->middleware(['can:Department Wise OR Individual Timebase Monthly Attendance Report']);
Route::get("/attendance-view-yearly", [ReportController::class, "departmentOrIndividualYearlyAttendanceReportView"])->name("departmentOrIndividualYearlyAttendanceReportView")->middleware(['can:Department Wise OR Individual Yearly Attendance Report']);
Route::get("/get-department-and-employee-by-office-division/{forSalary?}", [ReportController::class, "getDepartmentAndEmployeeByOfficeDivision"])->name("getDepartmentAndEmployeeByOfficeDivision");
Route::post("/get-employees-by-department-or-division", [ReportController::class, "getEmployeesByDepartmentOrDivision"])->name("getEmployeesByDepartmentOrDivision");
Route::get("/generate-monthly-attendance-report-view", [ReportController::class, "generateMonthlyAttendanceReportView"])->name("generateMonthlyAttendanceReportView")->middleware(['can:Department Wise OR Individual Monthly Attendance Report']);
Route::get("/generate-monthly-timebase-attendance-report-view", [ReportController::class, "generateMonthlyTimebaseAttendanceReportView"])->name("generateMonthlyTimebaseAttendanceReportView")->middleware(['can:Department Wise OR Individual Timebase Monthly Attendance Report']);
Route::get("/generate-yearly-attendance-report-view", [ReportController::class, "generateYearlyAttendanceReportView"])->name("generateYearlyAttendanceReportView")->middleware(['can:Department Wise OR Individual Yearly Attendance Report']);

Route::get("/view-leave-history", [LeaveHistoryReportsController::class, "viewLeaveHistory"])->name("viewLeaveHistory")->middleware(['can:View Leave History Report']);
Route::get("/generate-leave-history", [LeaveHistoryReportsController::class, "generateLeaveHistory"])->name("generateLeaveHistory")->middleware(['can:View Leave History Report']);

