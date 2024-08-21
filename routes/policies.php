<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PolicyController;

Route::get("/", [PolicyController::class, 'index'])->name("index")->middleware(['can:View Policy List']);
Route::get("/create", [PolicyController::class, "create"])->name("create")->middleware(['can:Create New Policy']);
Route::post("/store", [PolicyController::class, "store"])->name("store");
Route::get("/edit/{policy}", [PolicyController::class, "edit"])->name("edit")->middleware(['can:Edit Policy']);
Route::get("/view/{policy}", [PolicyController::class, "show"])->name("show")->middleware(['can:View Policy']);
Route::post("/update/{policy}", [PolicyController::class, "update"])->name("update");
Route::post("/delete/{policy}", [PolicyController::class, "delete"])->name("delete")->middleware(['can:Delete Policy']);

Route::get("/policy-card", [PolicyController::class, 'viewDashboardPolicyCard'])->name("viewDashboardPolicyCard")->middleware(['can:View Dashboard Policy Card']);
