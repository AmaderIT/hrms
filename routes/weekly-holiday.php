<?php

use App\Http\Controllers\WeeklyHolidayController;
use Illuminate\Support\Facades\Route;

Route::get("/", [WeeklyHolidayController::class, 'index'])->name("index")->middleware(['can:View Weekly Holiday']);
Route::get("/create", [WeeklyHolidayController::class, "create"])->name("create")->middleware(['can:Add Weekly Holiday']);
Route::post("/store", [WeeklyHolidayController::class, "store"])->name("store");
Route::get("/edit/{weeklyHoliday}", [WeeklyHolidayController::class, "edit"])->name("edit")->middleware(['can:Edit Weekly Holiday']);
Route::post("/update/{weeklyHoliday}", [WeeklyHolidayController::class, "update"])->name("update");
Route::post("/delete/{weeklyHoliday}", [WeeklyHolidayController::class, "delete"])->name("delete")->middleware(['can:Delete Weekly Holiday']);
