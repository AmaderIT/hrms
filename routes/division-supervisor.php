<?php

use App\Http\Controllers\DivisionSupervisorController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DivisionSupervisorController::class, "index"])->name("index")->middleware(['can:View Division Supervisor List']);
Route::get("/create", [DivisionSupervisorController::class, "create"])->name("create")->middleware(['can:Create Division Supervisor']);
Route::post("/store", [DivisionSupervisorController::class, "store"])->name("store")->middleware(['can:Create Division Supervisor']);
Route::post("/delete/{divisionSupervisor}", [DivisionSupervisorController::class, "delete"])->name("delete")->middleware(['can:Delete Division Supervisor']);
Route::post('/divisionSupervisorHistory',[DivisionSupervisorController::class, 'divisionSupervisorHistory'])->name('divisionSupervisorHistory');

Route::post("/get-data-table", [DivisionSupervisorController::class, "getDatatable"])->name("datatable");
Route::get("/edit/{divisionSupervisor}", [DivisionSupervisorController::class, "edit"])->name("edit")->middleware(['can:Edit Division Supervisor']);
Route::post("/lists-office-division-wise", [DivisionSupervisorController::class, "listsOfficeDivisionWise"])->name("listsOfficeDivisionWise");
