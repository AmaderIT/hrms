<?php

use App\Http\Controllers\DesignationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DesignationController::class, 'index'])->name("index")->middleware(['can:View Designation List']);
Route::get("/create", [DesignationController::class, "create"])->name("create")->middleware(['can:Create New Designation']);
Route::post("/store", [DesignationController::class, "store"])->name("store");
Route::get("/edit/{designation}", [DesignationController::class, "edit"])->name("edit")->middleware(['can:Edit Designation']);
Route::post("/update/{designation}", [DesignationController::class, "update"])->name("update");
Route::post("/delete/{designation}", [DesignationController::class, "delete"])->name("delete")->middleware(['can:Delete Designation']);
Route::post("/store-ajx", [DesignationController::class, "storeAjx"])->name("store-ajax");
