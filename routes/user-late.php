<?php

use App\Http\Controllers\UserLateController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UserLateController::class, 'index'])->name("index")->middleware(['can:View Late Status']);
