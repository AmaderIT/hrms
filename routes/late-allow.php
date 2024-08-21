<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LateAllowController;

Route::get("/", [LateAllowController::class, 'index'])->name("index")->middleware(['can:Late Allow']);
Route::post("/store", [LateAllowController::class, "store"])->name("store")->middleware(['can:Late Allow']);
Route::post("/delete/{lateAllow}", [LateAllowController::class, "delete"])->name("delete")->middleware(['can:Delete Late Allow']);
Route::post("/history", [LateAllowController::class, "getHistory"])->name("history")->middleware(['can:Late Allow']);
Route::post("/get-details", [LateAllowController::class, "getDetails"])->name("get-details")->middleware(['can:Late Allow']);
