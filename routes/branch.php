<?php

use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BranchController::class, 'index'])->name("index")->middleware(['can:View Bank Branch List']);
Route::get("/create", [BranchController::class, 'create'])->name("create")->middleware(['can:Create New Bank Branch']);
Route::post("/store", [BranchController::class, "store"])->name("store");
Route::get("/edit/{branch}", [BranchController::class, "edit"])->name("edit")->middleware(['can:Edit Bank Branch Name']);
Route::post("/update/{branch}", [BranchController::class, "update"])->name("update");
Route::post("/delete/{branch}", [BranchController::class, "delete"])->name("delete")->middleware(['can:Delete Bank Branch Name']);
