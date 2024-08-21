<?php

use App\Http\Controllers\BankController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BankController::class, 'index'])->name("index")->middleware(['can:View Bank List']);
Route::view("/create", "bank.create")->name("create")->middleware(['can:Create New Bank']);
Route::post("/store", [BankController::class, "store"])->name("store");
Route::get("/edit/{bank}", [BankController::class, "edit"])->name("edit")->middleware(['can:Edit Bank Name']);
Route::post("/update/{bank}", [BankController::class, "update"])->name("update");
Route::post("/delete/{bank}", [BankController::class, "delete"])->name("delete")->middleware(['can:Delete Bank Name']);
