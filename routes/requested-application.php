<?php

use App\Http\Controllers\RequestedApplicationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RequestedApplicationController::class, "index"])->name("index")->middleware(['can:View Leave Application']);
Route::get("/edit/{requestedApplication:uuid}", [RequestedApplicationController::class, "edit"])->name("edit")->middleware(['can:Edit Leave Application']);
Route::post("/manipulate/{requestedApplication:uuid}", [RequestedApplicationController::class, "manipulate"])->name("manipulate");
Route::post("/delete/{requestedApplication:uuid}", [RequestedApplicationController::class, "delete"])->name("delete")->middleware(['can:Delete Leave Application']);
Route::get("/sync-balance", [RequestedApplicationController::class, "syncBalance"])->name("syncBalance")->middleware(['can:Sync Employee Leave Balance']);
Route::get("/getDepartmentsByDivisionId/{officeDivision}", [RequestedApplicationController::class, "getDepartmentsByDivisionId"])->name("getDepartmentsByDivisionId");
Route::get("/balance/{leaveType}/{requestedApplication}", [RequestedApplicationController::class, "availableBalance"])->name("availableBalance")->middleware(['can:Create Leave Application']);
Route::get("/rollback/{requestedApplication:uuid}", [RequestedApplicationController::class, "rollback"])->name("rollback")->middleware(['can:Reverse Approved Leave']);

