<?php

use App\Http\Controllers\TaxCustomizationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [TaxCustomizationController::class, 'index'])->name("index")->middleware(['can:View Tax Customization List']);
Route::get("/create", [TaxCustomizationController::class, "create"])->name("create")->middleware(['can:Create Tax Customization']);
Route::post("/store", [TaxCustomizationController::class, "store"])->name("store")->middleware(['can:Create Tax Customization']);
Route::get("/edit/{taxCustomization}", [TaxCustomizationController::class, "edit"])->name("edit")->middleware(['can:Edit Tax Customization']);
Route::post("/update/{taxCustomization}", [TaxCustomizationController::class, "update"])->name("update")->middleware(['can:Edit Tax Customization']);
Route::post("/delete/{taxCustomization}", [TaxCustomizationController::class, "delete"])->name("delete")->middleware(['can:Delete Tax Customization']);
Route::get("/details/{user}", [TaxCustomizationController::class, "details"])->name("details")->middleware(['can:View Tax Customization List']);
