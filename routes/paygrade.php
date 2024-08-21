<?php

use App\Http\Controllers\PayGradeController;
use Illuminate\Support\Facades\Route;

Route::get("/", [PayGradeController::class, 'index'])->name("index")->middleware(['can:View Pay Grade List']);
Route::get("/create", [PayGradeController::class, 'create'])->name("create")->middleware(['can:Create Pay Grade']);
Route::post("/store", [PayGradeController::class, "store"])->name("store")->middleware(['can:Create Pay Grade']);
Route::get("/edit/{paygrade}", [PayGradeController::class, "edit"])->name("edit")->middleware(['can:Edit Pay Grade']);
Route::post("/update/{paygrade}", [PayGradeController::class, "update"])->name("update")->middleware(['can:Edit Pay Grade']);
Route::post("/delete/{paygrade}", [PayGradeController::class, "delete"])->name("delete")->middleware(['can:Delete Pay Grade']);
Route::get("/generate-salary-sheet", [PayGradeController::class, "generateSalarySheet"])->name("generate-salary-sheet")->middleware(['can:Generate Pay Slip']);
Route::get("/generate-pay-slip/{user}", [PayGradeController::class, "generatePaySlip"])->name("generatePaySlip")->middleware(['can:Generate Pay Slip']);
Route::get("/pdf/{user}", [PayGradeController::class, "pdfDownload"])->name("pdfDownload")->middleware(['can:Download PDF']);
