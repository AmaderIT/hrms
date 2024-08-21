<?php

use App\Http\Controllers\TerminationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [TerminationController::class, "index"])->name("index")->middleware(['can:Termination List']);
Route::get("/create", [TerminationController::class, "create"])->name("create")->middleware(['can:Create Termination']);
Route::post("/store", [TerminationController::class, "store"])->name("store");
Route::get("/edit/{termination}", [TerminationController::class, "edit"])->name("edit")->middleware(['can:Termination Edit']);
Route::post("/update/{termination}", [TerminationController::class, "update"])->name("update");
Route::post("/delete/{termination}", [TerminationController::class, "delete"])->name("delete")->middleware(['can:Termination Delete']);
Route::post('/termination/getActionTakenByUsers',[TerminationController::class, 'getActionTakenByUsers'])->name('getActionTakenByUsers');
