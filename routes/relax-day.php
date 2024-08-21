<?php

use App\Http\Controllers\RelaxDayController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RelaxDayController::class, "create"])->name("create")->middleware(['can:View Relax Days']);
Route::post("/store", [RelaxDayController::class, "store"])->name("store")->middleware(['can:Add Relax Day']);
Route::post("/get-modal-form", [RelaxDayController::class, "getModalForm"])->name("getModalForm")->middleware(['can:Add Relax Day']);
Route::post("/get-calender", [RelaxDayController::class, "getCalender"])->name("getCalender")->middleware(['can:Add Relax Day']);
