<?php

use App\Http\Controllers\LateManagementController;
use App\Http\Controllers\UserLateController;
use Illuminate\Support\Facades\Route;

Route::get("/", [LateManagementController::class, "index"])->name("index")->middleware(['can:View Late Management']);
Route::get("/edit/{lateDeduction}", [LateManagementController::class, "edit"])->name("edit")->middleware(['can:Edit Late Management']);
Route::post("/update/{lateDeduction}", [LateManagementController::class, "update"])->name("update")->middleware(['can:Edit Late Management']);

Route::get("/user-late", [UserLateController::class, "generate"])->name("user-late")->middleware(['can:View User Late']);

Route::post("/get-data-table", [LateManagementController::class, "getDatatable"])->name("get-data-table");
