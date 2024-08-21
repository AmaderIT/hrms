<?php

use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;

Route::get("/", [PromotionController::class, 'index'])->name("index")->middleware(['can:View Promotion List']);
Route::get("/create", [PromotionController::class, "create"])->name("create")->middleware(['can:Create Promotion']);
Route::post("/store", [PromotionController::class, "store"])->name("store")->middleware(['can:Create Promotion']);
Route::get("/edit/{promotion}", [PromotionController::class, "edit"])->name("edit")->middleware(['can:Edit Promotion']);
Route::post("/update/{promotion}", [PromotionController::class, "update"])->name("update")->middleware(['can:Edit Promotion']);
Route::post("/delete/{promotion}", [PromotionController::class, "delete"])->name("delete")->middleware(['can:Delete Promotion']);
Route::get("/{employee}", [PromotionController::class, "getEmployeeCurrentPromotion"])->name("getEmployeeCurrentPromotion")->middleware(['can:View Promotion List']);
Route::post("/get-data-table", [PromotionController::class, "getDatatable"])->name("datatable")->middleware(['can:View Promotion List']);
