<?php

use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DepartmentController::class, 'index'])->name("index")->middleware(['can:View Department List']);
Route::get("/create", [DepartmentController::class, 'create'])->name("create")->middleware(['can:Create New Department']);
Route::post("/store", [DepartmentController::class, "store"])->name("store");
Route::get("/edit/{department}", [DepartmentController::class, "edit"])->name("edit")->middleware(['can:Edit Department Name']);
Route::post("/update/{department}", [DepartmentController::class, "update"])->name("update");
Route::post("/delete/{department}", [DepartmentController::class, "delete"])->name("delete")->middleware(['can:Delete Department Name']);
Route::post("/store-ajx", [DepartmentController::class, "storeAjx"])->name("store-ajax");
