<?php

use App\Http\Controllers\OfficeDivisionController;
use Illuminate\Support\Facades\Route;

Route::get("/", [OfficeDivisionController::class, 'index'])->name("index")->middleware(['can:View Office Division List']);
Route::view("/create", "office-division.create")->name("create")->middleware(['can:Create Office Division']);
Route::post("/store", [OfficeDivisionController::class, "store"])->name("store")->middleware(['can:Create Office Division']);
Route::get("/edit/{officeDivision}", [OfficeDivisionController::class, "edit"])->name("edit")->middleware(['can:Edit Office Division']);
Route::post("/update/{officeDivision}", [OfficeDivisionController::class, "update"])->name("update")->middleware(['can:Edit Office Division']);
Route::post("/delete/{officeDivision}", [OfficeDivisionController::class, "delete"])->name("delete")->middleware(['can:Delete Office Division']);
Route::post("/store-ajx", [OfficeDivisionController::class, "storeAjx"])->name("store-ajax");
