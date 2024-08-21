<?php

use App\Http\Controllers\LeaveStatusController;
use Illuminate\Support\Facades\Route;

Route::get("/", [LeaveStatusController::class, 'index'])->name("index")->middleware(['can:View Leave Status']);
Route::get('/report/leave-to-supervisor/{user}', [LeaveStatusController::class, 'leaveToSupervisor'])->name('leaveToSupervisor')->middleware(['can:View Leave Status']);
