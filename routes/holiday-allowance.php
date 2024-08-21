<?php

use App\Http\Controllers\HolidayAllowanceController;
use Illuminate\Support\Facades\Route;

Route::get("/", [HolidayAllowanceController::class, 'generate'])->name("index");
