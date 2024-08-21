<?php

use App\Http\Controllers\EmployeeLeaveApplicationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [EmployeeLeaveApplicationController::class, "index"])->name("index")->middleware(['can:Show Employee Leave Applications']);
Route::get("/create", [EmployeeLeaveApplicationController::class, "create"])->name("create")->middleware(["can:Create Employee Leave Application"]);
Route::post("/store", [EmployeeLeaveApplicationController::class, "store"])->name("store")->middleware(["can:Create Employee Leave Application"]);
Route::get("/edit/{employeeLeaveApplication}", [EmployeeLeaveApplicationController::class, "edit"])->name("edit")->middleware(["can:Edit Employee Leave Application"]);
Route::post("/update/{employeeLeaveApplication}", [EmployeeLeaveApplicationController::class, "update"])->name("update")->middleware(["can:Edit Employee Leave Application"]);
Route::post("/delete/{employeeLeaveApplication}", [EmployeeLeaveApplicationController::class, "delete"])->name("delete")->middleware(["can:Delete Employee Leave Application"]);
Route::get("/balance/{leaveType}/{employee}", [EmployeeLeaveApplicationController::class, "balance"])->name("balance")->middleware(["can:Delete Employee Leave Application"]);
