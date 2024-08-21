<?php

use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::get("/", [LeaveRequestController::class, "index"])->name("index")->middleware(['can:Leave Application List']);
Route::get("/create/{data?}", [LeaveRequestController::class, "create"])->name("create")->middleware(['can:Create Leave Application']);
Route::post("/store", [LeaveRequestController::class, "store"])->name("store")->middleware(['can:Create Leave Application']);
Route::get("/edit/{applyForLeave:uuid}", [LeaveRequestController::class, "edit"])->name("edit")->middleware(['can:Edit Leave Application']);
Route::post("/update/{applyForLeave:uuid}", [LeaveRequestController::class, "update"])->name("update")->middleware(['can:Edit Leave Application']);
Route::post("/delete/{applyForLeave:uuid}", [LeaveRequestController::class, "delete"])->name("delete")->middleware(['can:Delete Leave Application']);
Route::get("/balance/{leaveType}", [LeaveRequestController::class, "balance"])->name("balance")->middleware(['can:Create Leave Application']);

Route::post("/getSlotWiseTimeRange", [LeaveRequestController::class, "getSlotWiseTimeRange"])->name("getSlotWiseTimeRange");
Route::get("/date-range-checker", [LeaveRequestController::class, "dateRangeChecker"])->name("date-range-checker");
Route::get("/get-employee-leave-graph", [LeaveRequestController::class, "getEmployeeLeaveGraph"])->name("get-employee-leave-graph");
