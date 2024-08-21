<?php

use App\Http\Controllers\CopyController;
use Illuminate\Support\Facades\Route;

Route::get("/", [CopyController::class, 'index'])->name("index")->middleware(['can:Copy data to Another Year']);
Route::post("/", [CopyController::class, 'copy'])->name("copy")->middleware(['can:Copy data to Another Year']);
