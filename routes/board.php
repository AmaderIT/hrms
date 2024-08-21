<?php

use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BoardController::class, 'index'])->name("index")->middleware(['can:View Board List']);
Route::view("/create", "board.create")->name("create")->middleware(['can:Create New Board']);
Route::post("/store", [BoardController::class, "store"])->name("store");
Route::get("/edit/{board}", [BoardController::class, "edit"])->name("edit")->middleware(['can:Edit Board Name']);
Route::post("/update/{board}", [BoardController::class, "update"])->name("update");
Route::post("/delete/{board}", [BoardController::class, "delete"])->name("delete")->middleware(['can:Delete Board Name']);
