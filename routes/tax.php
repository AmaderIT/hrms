<?php

use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

Route::get("/", [TaxController::class, 'index'])->name("index")->middleware(['can:View Tax List']);
Route::view("/create", "tax.create")->name("create")->middleware(['can:Create Tax']);
Route::post("/store", [TaxController::class, "store"])->name("store")->middleware(['can:Create Tax']);
Route::get("/edit/{tax}", [TaxController::class, "edit"])->name("edit")->middleware(['can:Edit Tax']);
Route::get("/copy/{tax}", [TaxController::class, "copy"])->name("copy")->middleware(['can:Copy Tax']);
Route::post("/change-status/{tax}", [TaxController::class, "changeStatus"])->name("changeStatus")->middleware(['can:Change Tax Status']);
Route::post("/update/{tax}", [TaxController::class, "update"])->name("update")->middleware(['can:Edit Tax']);
Route::post("/delete/{tax}", [TaxController::class, "delete"])->name("delete")->middleware(['can:Delete Tax']);
Route::get("/history", [TaxController::class, "taxHistory"])->name("history");
