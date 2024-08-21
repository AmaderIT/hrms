<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FilterController;

Route::any("/get-office-division", [FilterController::class, "getOfficeDivision"])->name("get-office-division");
Route::any("/get-department", [FilterController::class, "getDepartment"])->name("get-department");
Route::any("/get-employee", [FilterController::class, "getEmployee"])->name("get-employee");
