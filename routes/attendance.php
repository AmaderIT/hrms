<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OnlineAttendancesController;
use Illuminate\Support\Facades\Route;

Route::get("/daily-attendance", [AttendanceController::class, 'dailyAttendance'])->name("dailyAttendance")->middleware(['can:Create Daily Attendance']);
Route::post("/daily-attendance", [AttendanceController::class, 'storeDailyAttendance'])->name("storeDailyAttendance")->middleware(['can:Create Daily Attendance']);

Route::group(['middleware' => ['auth'], 'prefix' => 'requested-online-attendances', 'as' => 'requested_online_attendances.'], function (){
    Route::get("/", [OnlineAttendancesController::class, "index"])->name("index")->middleware(['can:Online Attendance List']);
    Route::get("/edit/{onlineAttendance:uuid}", [OnlineAttendancesController::class, "edit"])->name("edit")->middleware(['can:Online Attendance Edit']);
    Route::post("/approve/{onlineAttendance:uuid}", [OnlineAttendancesController::class, "approve"])->name("approve")->middleware(['can:Online Attendance Edit']);
});

Route::post("/online-attendance", [OnlineAttendancesController::class, "storeOnlineAttendance"])->name("online-attendance")->middleware(['can:Online Attendance Feature']);
