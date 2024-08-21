<?php

use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UnitController::class, 'index'])->name("index")->middleware(['can:View Unit List']);
Route::view("/create", "unit.create")->name("create")->middleware(['can:Create New Unit']);
Route::post("/store", [UnitController::class, "store"])->name("store")->middleware(['can:Create New Unit']);
Route::get("/edit/{unit}", [UnitController::class, "edit"])->name("edit")->middleware(['can:Edit Unit Name']);
Route::post("/update/{unit}", [UnitController::class, "update"])->name("update")->middleware(['can:Edit Unit Name']);
Route::post("/delete/{unit}", [UnitController::class, "delete"])->name("delete")->middleware(['can:Delete Unit Name']);
