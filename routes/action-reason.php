<?php

use App\Http\Controllers\ActionReasonController;
use Illuminate\Support\Facades\Route;

Route::get("/", [ActionReasonController::class, 'index'])->name("index")->middleware(['can:View Action Reason']);
Route::get("/create", [ActionReasonController::class, "create"])->name("create")->middleware(['can:Create Action Reason']);
Route::post("/store", [ActionReasonController::class, "store"])->name("store");
Route::get("/edit/{actionReason}", [ActionReasonController::class, "edit"])->name("edit")->middleware(['can:Edit Action Reason']);
Route::post("/update/{actionReason}", [ActionReasonController::class, "update"])->name("update");
Route::post("/delete/{actionReason}", [ActionReasonController::class, "delete"])->name("delete")->middleware(['can:Delete Action Reason']);
