<?php

use App\Http\Controllers\BloodBankController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BloodBankController::class, "index"])->name("index")->middleware(['can:View Blood Bank']);
Route::post("/get-datatable", [BloodBankController::class, "getDataTable"])->name("get-datatable")->middleware(['can:View Blood Bank']);
