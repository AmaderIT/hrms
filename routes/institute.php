<?php

use App\Http\Controllers\InstituteController;
use Illuminate\Support\Facades\Route;

Route::get("/", [InstituteController::class, 'index'])->name("index")->middleware(['can:View Institute List']);
Route::get("/create", [InstituteController::class, 'create'])->name("create")->middleware(['can:Create New Institute']);
Route::post("/store", [InstituteController::class, "store"])->name("store");
Route::get("/edit/{institute}", [InstituteController::class, "edit"])->name("edit")->middleware(['can:Edit Institute Name']);
Route::post("/update/{institute}", [InstituteController::class, "update"])->name("update");
Route::post("/delete/{institute}", [InstituteController::class, "delete"])->name("delete")->middleware(['can:Delete Institute Name']);
Route::post("/store-ajx", [InstituteController::class, "storeAjx"])->name("store-ajax");
