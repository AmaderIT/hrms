<?php

use App\Http\Controllers\AssignRelaxDayController;
use App\Http\Controllers\RelaxDaySettingsController;
use Illuminate\Support\Facades\Route;

Route::get("/", [AssignRelaxDayController::class, "index"])->name("index")->middleware(['can:Assign Relax Day List']);
Route::get("/archived", [AssignRelaxDayController::class, "index"])->name("archived")->middleware(['can:Assign Relax Day List']);
Route::get("/not-assign", [RelaxDaySettingsController::class, "index"])->name("not-assign")->middleware(['can:Assign Relax Day List']);
Route::get("/assign", [RelaxDaySettingsController::class, "assignFrom"])->name("assign")->middleware(['can:Assign Relax Days']);
Route::post("/assign", [RelaxDaySettingsController::class, "store"])->name("assign-store")->middleware(['can:Store Assigned Relax Days']);


Route::get("/create", [AssignRelaxDayController::class, "create"])->name("create")->middleware(['can:Assign Relax Days']);
Route::post("/details", [AssignRelaxDayController::class, "details"])->name("details")->middleware(['can:Relax Days Detail View']);
Route::post("/store", [AssignRelaxDayController::class, "store"])->name("store")->middleware(['can:Store Assigned Relax Days']);
Route::post("/approve", [AssignRelaxDayController::class, "approve"])->name("approve")->middleware(['can:Approve Assigned Relax Days']);
Route::post("/delete", [AssignRelaxDayController::class, "delete"])->name("delete")->middleware(['can:Assign Relax Days']);


