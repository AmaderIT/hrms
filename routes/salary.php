<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SalaryReportController;
use App\Http\Controllers\SalaryExportController;

Route::get("/view-salary", [SalaryController::class, "viewSalary"])->name("viewSalary")->middleware(['can:Salary List']);
Route::get("/view-all-salary", [SalaryController::class, "viewAllSalary"])->name("viewAllSalary")->middleware(['can:View All Salary']);
//Route::get("/view-salary/generate", [SalaryController::class, "generateSalaryReportView"])->name("generateSalaryReportView");

Route::get("/report/filter", [SalaryController::class, "salaryReportFilter"])->name("salaryReportFilter")->middleware(['can:Generate Salary Report']);
Route::get("/report/view", [SalaryReportController::class, "generateSalaryReportView"])->name("generateSalaryReportView")->middleware(['can:Generate Salary Report']);
Route::post("/report/export/{salaryDepartment?}", [SalaryReportController::class, "exportSalaryReport"])->name("exportSalaryReport")->middleware(['can:Generate Salary Report']);

Route::post("/pay-salary-to-department", [SalaryController::class, "paySalaryToDepartment"])->name("paySalaryToDepartment")->middleware(['can:Pay Salary by Department']);
Route::get("/details/{salaryDepartment:uuid}", [SalaryController::class, "details"])->name("details")->middleware(['can:Salary Details']);
Route::post("/export/{salaryDepartment}", [SalaryController::class, "salaryExport"])->name("salaryExport");
Route::post("/regenerate/{salaryDepartment:uuid}", [SalaryController::class, "regenerate"])->name("regenerate")->middleware(['can:Regenerate Salary']);

Route::post("/approval/divisional", [SalaryController::class, "approvalDivisional"])->name("approvalDivisional")->middleware(['can:Salary Divisional Approval']);
Route::post("/approval/departmental", [SalaryController::class, "approvalDepartmental"])->name("approvalDepartmental")->middleware(['can:Salary Departmental Approval']);
Route::post("/approval/hr", [SalaryController::class, "approvalHr"])->name("approvalHr")->middleware(['can:Salary HR Approval']);
Route::post("/approval/accounts", [SalaryController::class, "approvalAccounts"])->name("approvalAccounts")->middleware(['can:Salary Accounts Approval']);
Route::post("/approval/managerial", [SalaryController::class, "approvalManagerial"])->name("approvalManagerial")->middleware(['can:Salary Managerial Approval']);

Route::get("/status", [SalaryController::class, "status"])->name("status")->middleware(["can:Pay Salary by Department"]);
Route::get("/yearly-history", [SalaryController::class, "salaryHistory"])->name("history");
Route::get("/prepare-salary", [SalaryController::class, "prepareSalary"])->name("prepareSalary")->middleware(['can:Prepare Salary']);
Route::post("/generate-salary", [SalaryController::class, "generateSalary"])->name("generateSalary")->middleware(['can:Prepare Salary']);
//Route::get("/show-salary", [SalaryController::class, "showSalary"])->name("showSalary")->middleware(['can:View Salary']);
Route::post("/filter-salary", [SalaryController::class, "filterSalary"])->name("filterSalary")->middleware(['can:View Salary']);
//Route::post("/payNow", [SalaryController::class, "payNow"])->name("payNow")->middleware(['can:Pay Salary']);
Route::get("/payslip/{salary}", [SalaryController::class, "viewPaySlip"])->name("generatePaySlip")->middleware(['can:View Pay Slip']);
Route::get("/pdf/{salary}", [SalaryController::class, "pdfDownload"])->name("pdfDownload")->middleware(['can:Download PDF']);
Route::get("/pdf/cash/{salary}", [SalaryController::class, "pdfCashDownload"])->name("pdfCashDownload")->middleware(['can:Download PDF']);
Route::get("/pay-slip", [SalaryController::class, "paySlip"])->name("paySlip")->middleware(['can:View Pay Slip']);
Route::post("/salary-by-department", [SalaryController::class, "filterSalaryDepartment"])->name("filterSalaryDepartment");
Route::post("/pay-salary-by-department", [SalaryController::class, "paySalaryByDepartment"])->name("paySalaryByDepartment");

# Miscellanies
Route::get("/getDepartmentByOfficeDivision/{officeDivision}", [SalaryController::class, "getDepartmentByOfficeDivision"])->name("getDepartmentByOfficeDivision");
Route::get("/getSupervisorDepartmentByOfficeDivision/{officeDivision}/supervisor", [SalaryController::class, "getSupervisorDepartmentByOfficeDivision"])->name("getSupervisorDepartmentByOfficeDivision");
Route::get("/getDepartmentByAllOfficeDivision/{officeDivision}", [SalaryController::class, "getDepartmentByAllOfficeDivision"])->name("getDepartmentByAllOfficeDivision");
Route::get("/getEmployeeByDepartment/{department}", [SalaryController::class, "getEmployeeByDepartment"])->name("getEmployeeByDepartment");
Route::get("/getEmployeeByOfficeDivision/{officeDivision}", [SalaryController::class, "getEmployeeByOfficeDivision"])->name("getEmployeeByOfficeDivision");
Route::post("/department-division-wise-salary-employees", [SalaryController::class, "departmentDivisionWiseSalaryEmployees"])->name("department_division_wise_salary_employees");

Route::get("/details/{user:fingerprint_no}", [SalaryController::class, "salaryDetails"])->name("salaryDetails");

Route::get("/tax-deduction/{user:fingerprint_no}", [SalaryController::class, "taxDeduction"])->name("taxDeduction");

/**
 * Currently not Using
 *
 * Route::get("/prepare", [SalaryController::class, "prepare"])->name("prepare");
 * Route::post("/generate", [SalaryController::class, "generate"])->name("generate");
 */


Route::get("download-bank-statement", [SalaryExportController::class, "downloadExportFile"])->name("download-bank-statement")
    ->middleware(['can:Export Salary Bank Statement CSV']);
Route::get("download-tax-deduction", [SalaryExportController::class, "downloadExportFile"])->name("download-tax-deduction")
    ->middleware(['can:Export Tax Deduction']);
Route::get("download-loan-deduction", [SalaryExportController::class, "downloadExportFile"])->name("download-loan-deduction")
    ->middleware(['can:Export Loan Deduction']);

/**
 * Route for tax amount adjustment of a specific employee
 */
Route::post("adjust-tax-amount", [\App\Http\Controllers\TaxAdjustmentController::class, "adjustTaxAmount"])->name("adjustTaxAmount")->middleware(['can:Adjust Tax Amount']);
