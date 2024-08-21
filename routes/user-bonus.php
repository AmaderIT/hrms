<?php

use App\Http\Controllers\BonusExportController;
use App\Http\Controllers\BonusReportController;
use App\Http\Controllers\UserBonusController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UserBonusController::class, 'index'])->name("index")->middleware(['can:Salary List']);
Route::get("/create", [UserBonusController::class, 'create'])->name("create")->middleware(['can:Prepare Salary']);
Route::post("/store", [UserBonusController::class, "store"])->name("store")->middleware(['can:Prepare Salary']);
Route::get("/pay-slip", [UserBonusController::class, "paySlip"])->name("paySlip")->middleware(['can:View Pay Slip']);
Route::get("/generate-pay-slip/{userBonus}", [UserBonusController::class, "generatePaySlip"])->name("generatePaySlip")->middleware(['can:View Pay Slip']);
Route::get("/pdf/{UserBonus}", [UserBonusController::class, "pdfDownload"])->name("pdfDownload")->middleware(['can:Download PDF']);

Route::post("/pay-bonus-to-department", [UserBonusController::class, "payBonusToDepartment"])->name("payBonusToDepartment")->middleware(['can:Pay Salary by Department']);
Route::get("/details/{bonusDepartment:uuid}", [UserBonusController::class, "details"])->name("details")->middleware(['can:Salary Details']);
Route::post("/export/{bonusDepartment}", [UserBonusController::class, "bonusExport"])->name("bonusExport");
Route::post("/regenerate/{bonusDepartment:uuid}", [UserBonusController::class, "regenerate"])->name("regenerate")->middleware(['can:Regenerate Salary']);

Route::post("/approval/divisional", [UserBonusController::class, "approvalDivisional"])->name("approvalDivisional")->middleware(['can:Salary Divisional Approval']);
Route::post("/approval/departmental", [UserBonusController::class, "approvalDepartmental"])->name("approvalDepartmental")->middleware(['can:Salary Departmental Approval']);
Route::post("/approval/hr", [UserBonusController::class, "approvalHr"])->name("approvalHr")->middleware(['can:Salary HR Approval']);
Route::post("/approval/accounts", [UserBonusController::class, "approvalAccounts"])->name("approvalAccounts")->middleware(['can:Salary Accounts Approval']);
Route::post("/approval/managerial", [UserBonusController::class, "approvalManagerial"])->name("approvalManagerial")->middleware(['can:Salary Managerial Approval']);

Route::get("download-bank-statement", [BonusExportController::class, "downloadExportFile"])->name("download-bank-statement")
    ->middleware(['can:Export Salary Bank Statement CSV']);
Route::get("download-tax-deduction", [BonusExportController::class, "downloadExportFile"])->name("download-tax-deduction")
    ->middleware(['can:Export Tax Deduction']);
Route::get("download-loan-deduction", [BonusExportController::class, "downloadExportFile"])->name("download-loan-deduction")
    ->middleware(['can:Export Loan Deduction']);

Route::get("/report/filter", [BonusReportController::class, "bonusReportFilter"])->name("bonusReportFilter")->middleware(['can:Generate Salary Report']);
Route::get("/report/view", [BonusReportController::class, "generateBonusReportView"])->name("generateBonusReportView")->middleware(['can:Generate Salary Report']);
Route::post("/report/export/{bonusDepartment?}", [BonusReportController::class, "exportBonusReport"])->name("exportBonusReport")->middleware(['can:Generate Salary Report']);
