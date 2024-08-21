<?php

use App\Http\Controllers\EarningController;
use Illuminate\Support\Facades\Route;

Route::get("/", [EarningController::class, 'index'])->name("index")->middleware(['can:View Earnings List']);
Route::view("/create","earning.create")->name("create")->middleware(['can:Create Earnings']);
Route::post("/store", [EarningController::class, "store"])->name("store")->middleware(['can:Create Earnings']);
Route::get("/edit/{earning}", [EarningController::class, "edit"])->name("edit")->middleware(['can:Edit Earnings']);
Route::post("/update/{earning}", [EarningController::class, "update"])->name("update")->middleware(['can:Edit Earnings']);
Route::post("/delete/{earning}", [EarningController::class, "delete"])->name("delete")->middleware(['can:Delete Earnings']);
