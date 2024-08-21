<?php

use App\Http\Controllers\WarningController;
use Illuminate\Support\Facades\Route;

Route::get("/", [WarningController::class, 'index'])->name("index");
Route::get("create", [WarningController::class, 'create'])->name("create");
Route::post("store", [WarningController::class, "store"])->name("store");
Route::get("edit/{warning}", [WarningController::class, "edit"])->name("edit");
Route::post("update/{warning}", [WarningController::class, "update"])->name("update");
