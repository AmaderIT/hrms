<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DegreeController;

Route::get("/", [DegreeController::class, "index"])->name("index")->middleware(['can:View Degree List']);
Route::view("/create", "degree.create")->name("create")->middleware(['can:Create New Degree']);
Route::post("/store", [DegreeController::class, "store"])->name("store");
Route::get("/edit/{degree}", [DegreeController::class, "edit"])->name("edit")->middleware(['can:Edit Degree Name']);
Route::post("/update/{degree}", [DegreeController::class, "update"])->name("update");
Route::post("/delete/{degree}", [DegreeController::class, "delete"])->name("delete")->middleware(['can:Delete Degree Name']);
