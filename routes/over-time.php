<?php

use App\Http\Controllers\OverTimeController;
use Illuminate\Support\Facades\Route;

Route::get("/", [OverTimeController::class, 'generate'])->name("index");
