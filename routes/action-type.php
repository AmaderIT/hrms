<?php

use App\Http\Controllers\ActionTypeController;
use Illuminate\Support\Facades\Route;

Route::get("/", [ActionTypeController::class, 'index'])->name('index')->middleware(['can:View Action type']);
Route::view("/create", "action-type.create")->name("create")->middleware(['can:Create Action type']);
Route::post("/store", [ActionTypeController::class, "store"])->name("store");
Route::get("/edit/{actionType}", [ActionTypeController::class, "edit"])->name("edit")->middleware(['can:Edit Action type']);
Route::post("/update/{actionType}", [ActionTypeController::class, "update"])->name("update");
Route::post("/delete/{actionType}", [ActionTypeController::class, "delete"])->name("delete")->middleware(['can:Delete Action type']);
