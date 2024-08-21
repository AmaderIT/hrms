<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DivisionController;

Route::get("/", [DivisionController::class, 'index'])->name("index")->middleware(['can:View Division List']);
Route::get("/create", [DivisionController::class, "create"])->name("create")->middleware(['can:Create New Divisions']);
Route::post("/store", [DivisionController::class, "store"])->name("store");
Route::get("/edit/{division}", [DivisionController::class, "edit"])->name("edit")->middleware(['can:Edit Divisions Name']);
Route::post("/update/{division}", [DivisionController::class, "update"])->name("update");
Route::post("/delete/{division}", [DivisionController::class, "delete"])->name("delete")->middleware(['can:Delete Divisions']);
Route::post("/import", [DivisionController::class, 'import'])->name("import");
Route::get('/export', [DivisionController::class, 'export'])->name("export");
