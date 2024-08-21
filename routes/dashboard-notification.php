<?php

use App\Http\Controllers\DashboardNotificationController;
use Illuminate\Support\Facades\Route;

Route::get("/get-data", [DashboardNotificationController::class, "getData"])->name("get-data");

Route::get("/get-employee-list-provision/{room}", [DashboardNotificationController::class, "getEmployeeListProvision"])->name("get-employee-list-provision");
Route::post("/get-employee-list-data-provision", [DashboardNotificationController::class, "getEmployeeListDataProvision"])->name("get-employee-list-data-provision");

Route::get("/get-employee-list-leave-yesterday/{room}", [DashboardNotificationController::class, "getEmployeeListLeaveYesterday"])->name("get-employee-list-leave-yesterday");
Route::post("/get-employee-list-data-leave-yesterday", [DashboardNotificationController::class, "getEmployeeListDataLeaveYesterday"])->name("get-employee-list-data-leave-yesterday");
Route::post("/leave-lists", [DashboardNotificationController::class, "getSpecificDateLeaveLists"])->name("getSpecificDateLeaveLists");
