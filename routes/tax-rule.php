<?php

use App\Http\Controllers\TaxRuleController;
use Illuminate\Support\Facades\Route;

Route::get("/edit/{tax}", [TaxRuleController::class, "edit"])->name("edit")->middleware(['can:Edit Tax Rules']);
Route::post("/update/{tax}", [TaxRuleController::class, "update"])->name("update")->middleware(['can:Edit Tax Rules']);
