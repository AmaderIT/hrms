<?php

use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get("/edit", [SettingController::class, "edit"])->name("edit")->middleware(['can:Settings']);
Route::post("/update", [SettingController::class, "update"])->name("update")->middleware(['can:Settings']);
