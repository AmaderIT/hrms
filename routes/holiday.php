<?php

use App\Http\Controllers\HoliDayController;
use Illuminate\Support\Facades\Route;

Route::get("/", [HoliDayController::class, 'index'])->name("index")->middleware(['can:View Holiday List']);
Route::view("/create", "holiday.create")->name("create")->middleware(['can:Add Holidays']);
Route::post("/store", [HoliDayController::class, "store"])->name("store");
Route::get("/edit/{holiday}", [HoliDayController::class, "edit"])->name("edit")->middleware(['can:Edit Holidays']);
Route::post("/update/{holiday}", [HoliDayController::class, "update"])->name("update");
Route::post("/delete/{holiday}", [HoliDayController::class, "delete"])->name("delete")->middleware(['can:Delete Holidays']);
