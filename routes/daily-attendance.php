<?php

use App\Http\Controllers\DailyAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DailyAttendanceController::class, 'generate'])->name("generate")->middleware(['can:Generate Daily Attendance']);
