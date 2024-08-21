<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;

Route::get("/", [LoanController::class, 'index'])->name("index")->middleware(['can:View Loan List']);
Route::get("/create", [LoanController::class, 'create'])->name("create")->middleware(['can:Apply for Loans']);
Route::post("/store", [LoanController::class, "store"])->name("store")->middleware(['can:Apply for Loans']);
Route::get("/view/{loan}", [LoanController::class, "show"])->name("show")->middleware(['can:View Loan']);
Route::get("/edit/{loan}", [LoanController::class, "edit"])->name("edit")->middleware(['can:Edit Loans']);
Route::post("/update/{loan}", [LoanController::class, "update"])->name("update")->middleware(['can:Edit Loans']);
Route::post("/delete/{loan}", [LoanController::class, "delete"])->name("delete")->middleware(['can:Delete Loans']);
Route::get("/get-active-loans/{user_id}", [LoanController::class, 'getActiveLoans'])->name("get_active_loans")->middleware(['can:View Loan List']);
Route::post("/check-loan-policy/{loan_user_id?}", [LoanController::class, 'checkLoanPolicy'])->name("check_loan_policy")->middleware(['can:View Loan List']);
Route::post("/generate-installment-table", [LoanController::class, 'generateInstallmentTable'])->name("generate_installment_table")->middleware(['can:View Loan List']);
Route::post("/approval/{type}", [LoanController::class, "loanApproval"])->name("loanApproval")->middleware(['can:Approve Loan / Advance Salary']);
Route::post("/loan-payment", [LoanController::class, "loanPayment"])->name("loanPayment")->middleware(['can:Loan Amount Payment']);
Route::post("/loan-hold", [LoanController::class, "loanHold"])->name("loanHold")->middleware(['can:Loan Hold']);
