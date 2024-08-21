<?php

use App\Http\Controllers\PublicHolidayController;
use Illuminate\Support\Facades\Route;

Route::get("/", [PublicHolidayController::class, "index"])->name("index")->middleware(['can:View Public Holidays']);
Route::get("/create", [PublicHolidayController::class, "create"])->name("create")->middleware(['can:Add Public Holidays']);
Route::post("/store", [PublicHolidayController::class, "store"])->name("store");
Route::get("/edit/{publicHoliday}", [PublicHolidayController::class, "edit"])->name("edit")->middleware(['can:Edit Public Holidays']);
Route::post("/update/{publicHoliday}", [PublicHolidayController::class, "update"])->name("update");
Route::post("/delete/{publicHoliday}", [PublicHolidayController::class, "delete"])->name("delete")->middleware(['can:Delete Public Holidays']);
Route::get('public-holiday-download', [PublicHolidayController::class, 'publicHolidayDownload'])->name('publicHolidayDownload');