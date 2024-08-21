<?php

use App\Http\Controllers\RequisitionItemController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RequisitionItemController::class, 'index'])->name("index")->middleware(['can:View Requisition Item List']);
Route::view("/create", "requisition-item.create")->name("create")->middleware(['can:Create New Requisition Item']);
Route::post("/store", [RequisitionItemController::class, "store"])->name("store")->middleware(['can:Create New Requisition Item']);
Route::get("/edit/{requisitionItem}", [RequisitionItemController::class, "edit"])->name("edit")->middleware(['can:Edit Requisition Item Name']);
Route::post("/update/{requisitionItem}", [RequisitionItemController::class, "update"])->name("update")->middleware(['can:Edit Requisition Item Name']);
Route::post("/delete/{requisitionItem}", [RequisitionItemController::class, "delete"])->name("delete")->middleware(['can:Delete Requisition Item Name']);
Route::get("/sync-item", [RequisitionItemController::class, "syncItem"])->name("syncItem")->middleware(['can:Sync Requisition Item']);
