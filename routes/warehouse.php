<?php

use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get("/", [WarehouseController::class, 'index'])->name("index")->middleware(['can:View Warehouse List']);
Route::get("/create", [WarehouseController::class, "create"])->name("create")->middleware(['can:Create New Warehouse']);
Route::post("/store", [WarehouseController::class, "store"])->name("store")->middleware(['can:Create New Warehouse']);
Route::get("/edit/{warehouse}", [WarehouseController::class, "edit"])->name("edit")->middleware(['can:Edit Warehouse Name']);
Route::post("/update/{warehouse}", [WarehouseController::class, "update"])->name("update")->middleware(['can:Edit Warehouse Name']);
Route::post("/delete/{warehouse}", [WarehouseController::class, "delete"])->name("delete")->middleware(['can:Delete Warehouse Name']);
