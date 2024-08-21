<?php

use App\Http\Controllers\LeaveUnpaidController;
use Illuminate\Support\Facades\Route;

Route::get("/generate", [LeaveUnpaidController::class, 'generate'])->name("generate");
// Route::get("/leave-unpaid-tast", [LeaveUnpaidController::class, 'leaveUnpaidTast'])->name("leaveUnpaidTast");
