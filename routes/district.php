<?php

use App\Http\Controllers\DistrictController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DistrictController::class, 'index'])->name("index")->middleware(['can:View District List']);
Route::get("/create", [DistrictController::class, 'create'])->name("create")->middleware(['can:Create New District']);
Route::post("/store", [DistrictController::class, "store"])->name("store");
Route::get("/edit/{district}", [DistrictController::class, "edit"])->name("edit")->middleware(['can:Edit Districts Name']);
Route::post("/update/{district}", [DistrictController::class, "update"])->name("update");
Route::post("/delete/{district}", [DistrictController::class, "delete"])->name("delete")->middleware(['can:Delete District Name']);
