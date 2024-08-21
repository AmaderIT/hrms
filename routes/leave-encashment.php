<?php

use App\Http\Controllers\LeaveEncashmentController;
use Illuminate\Support\Facades\Route;

Route::get("/leave-encashment", [LeaveEncashmentController::class, "leaveEncashment"])->name("leaveEncashment")->middleware(['can:Generate Leave Encashment']);
Route::get("/generate-leave-encashment", [LeaveEncashmentController::class, "leaveEncashmentGenerate"])->name("leaveEncashmentGenerate")->middleware(['can:Generate Leave Encashment']);
Route::get("/leave-encashment-list", [LeaveEncashmentController::class, "leaveEncashmentList"])->name("leaveEncashmentList")->middleware(['can:View Leave Encashment List']);
Route::get("/details/{departmentLeaveEncashment:uuid}", [LeaveEncashmentController::class, "details"])->name("details")->middleware(['can:View Leave Encashment Details']);
Route::post("/approval/divisional", [LeaveEncashmentController::class, "approvalDivisional"])->name("approvalDivisional")->middleware(['can:Leave Encashment Divisional Approval']);
Route::post("/approval/departmental", [LeaveEncashmentController::class, "approvalDepartmental"])->name("approvalDepartmental")->middleware(['can:Leave Encashment Departmental Approval']);
Route::post("/approval/hr", [LeaveEncashmentController::class, "approvalHr"])->name("approvalHr")->middleware(['can:Leave Encashment HR Approval']);
Route::post("/approval/accounts", [LeaveEncashmentController::class, "approvalAccounts"])->name("approvalAccounts")->middleware(['can:Leave Encashment Accounts Approval']);
Route::post("/approval/managerial", [LeaveEncashmentController::class, "approvalManagerial"])->name("approvalManagerial")->middleware(['can:Leave Encashment Managerial Approval']);
Route::post("/pay-leave-encashment-to-department", [LeaveEncashmentController::class, "payLeaveEncashmentToDepartment"])->name("payLeaveEncashmentToDepartment")->middleware(['can:Pay Leave Encashment']);
Route::post("/export-leave-encashment/{departmentLeaveEncashment:uuid}", [LeaveEncashmentController::class, "exportLeaveEncashment"])->name("exportLeaveEncashment");
Route::get("/get-department-and-employee-by-office-division", [LeaveEncashmentController::class, "getDepartmentAndEmployeeByOfficeDivision"])->name("getDepartmentAndEmployeeByOfficeDivision");
Route::get("download-bank-statement", [LeaveEncashmentController::class, "exportFileLeaveEncashment"])->name("export-file-leave-encashment")
    ->middleware(['permission:Export Leave Encashment Bank Statement PDF|Export Leave Encashment Bank Statement EXCEL']);
