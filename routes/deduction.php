<?php

use App\Http\Controllers\DeductionController;
use Illuminate\Support\Facades\Route;

Route::get("", [DeductionController::class, "index"])->name("index")->middleware(['can:View Deductions List']);
Route::view("create", "deduction.create")->name("create")->middleware(['can:Create Deductions']);
Route::post("store", [DeductionController::class, "store"])->name("store")->middleware(['can:Create Deductions']);
Route::get("edit/{deduction}", [DeductionController::class, "edit"])->name("edit")->middleware(['can:Edit Deductions']);
Route::post("update/{deduction}", [DeductionController::class, "update"])->name("update")->middleware(['can:Edit Deductions']);
Route::post("delete/{deduction}", [DeductionController::class, "delete"])->name("delete")->middleware(['can:Delete Deductions']);
