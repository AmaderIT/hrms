<?php

use App\Http\Controllers\RequestedLoanAdvanceController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RequestedLoanAdvanceController::class, "index"])->name("index")->middleware(['can:View Requested Loan / Advance Application']);
Route::get("/create", [RequestedLoanAdvanceController::class, 'create'])->name("create")->middleware(['can:Add Another Employee Loan']);
Route::post("/store", [RequestedLoanAdvanceController::class, "store"])->name("store")->middleware(['can:Add Another Employee Loan']);
Route::get("/edit/{loan:uuid}", [RequestedLoanAdvanceController::class, "edit"])->name("edit")->middleware(['can:Edit Requested Loan / Advance Application']);
Route::post("/update/{loan:uuid}", [RequestedLoanAdvanceController::class, "update"])->name("update")->middleware(['can:Edit Requested Loan / Advance Application']);
Route::post("/instalment-update/{loan:uuid}", [RequestedLoanAdvanceController::class, "instalmentUpdate"])->name("instalment-update")->middleware(['can:Edit Requested Loan / Advance Application']);
Route::post("/delete/{loan:uuid}", [RequestedLoanAdvanceController::class, "delete"])->name("delete")->middleware(['can:Delete Requested Loan / Advance Application']);
