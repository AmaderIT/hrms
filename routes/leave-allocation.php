<?php

use App\Http\Controllers\LeaveAllocationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [LeaveAllocationController::class, "index"])->name("index")->middleware(['can:View Leave Allocation List']);
Route::get("/create", [LeaveAllocationController::class, "create"])->name("create")->middleware(['can:Create Leave Allocation']);
Route::post("/store", [LeaveAllocationController::class, "store"])->name("store")->middleware(['can:Create Leave Allocation']);
Route::get("/edit/{leaveAllocation}", [LeaveAllocationController::class, "edit"])->name("edit")->middleware(['can:Edit Leave Allocation']);
Route::post("/update/{leaveAllocation}", [LeaveAllocationController::class, "update"])->name("update")->middleware(['can:Edit Leave Allocation']);
Route::post("/delete/{leaveAllocation}", [LeaveAllocationController::class, "delete"])->name("delete")->middleware(['can:Delete Leave Allocation']);
