<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;

Route::get("/", [TransferController::class, 'index'])->name("index")->middleware(['can:View Transfer List']);
Route::get("/create", [TransferController::class, "create"])->name("create")->middleware(['can:Create Transfer']);
Route::post("/store", [TransferController::class, "store"])->name("store")->middleware(['can:Create Transfer']);;
Route::get("/edit/{transfer}", [TransferController::class, "edit"])->name("edit")->middleware(['can:Edit Transfer']);
Route::post("/update/{transfer}", [TransferController::class, "update"])->name("update")->middleware(['can:Update Transfer']);
Route::post("/delete/{transfer}", [TransferController::class, "delete"])->name("delete")->middleware(['can:Delete Transfer']);
Route::post("/history", [TransferController::class, "getHistory"])->name("history")->middleware(['can:View Transfer History']);
Route::post("/get-data-table", [TransferController::class, "getDatatable"])->name("datatable")->middleware(['can:View Transfer List']);

Route::get("/{employee}", [TransferController::class, "getEmployeeCurrentPromotion"])->name("getEmployeeCurrentPromotion")->middleware(['can:Create Transfer']);


