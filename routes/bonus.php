<?php

use App\Http\Controllers\BonusController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BonusController::class, 'index'])->name("index")->middleware(['can:View Bonus List']);
Route::view("/create", "bonus.create")->name("create")->middleware(['can:Create Bonus']);
Route::post("/store", [BonusController::class, "store"])->name("store")->middleware(['can:Create Bonus']);
Route::get("/edit/{bonus}", [BonusController::class, "edit"])->name("edit")->middleware(['can:Edit Bonus']);
Route::post("/update/{bonus}", [BonusController::class, "update"])->name("update")->middleware(['can:Edit Bonus']);
Route::post("/delete/{bonus}", [BonusController::class, "delete"])->name("delete")->middleware(['can:Delete Bonus']);
