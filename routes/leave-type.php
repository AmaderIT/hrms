<?php

use App\Http\Controllers\LeaveTypeController;
use Illuminate\Support\Facades\Route;

Route::get("/", [LeaveTypeController::class, "index"])->name("index")->middleware(['can:View Leave Type']);
Route::get("/create", [LeaveTypeController::class, "create"])->name("create")->middleware(['can:Create Leave Type']);
Route::post("/store", [LeaveTypeController::class, "store"])->name("store");
Route::get("/edit/{leaveType}", [LeaveTypeController::class, "edit"])->name("edit")->middleware(['can:Edit Leave Type']);
Route::post("/update/{leaveType}", [LeaveTypeController::class, "update"])->name("update");
Route::post("/delete/{leaveType}", [LeaveTypeController::class, "delete"])->name("delete")->middleware(['can:Delete Leave Type']);
