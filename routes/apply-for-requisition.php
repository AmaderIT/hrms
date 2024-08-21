<?php

use App\Http\Controllers\RequisitionApplicationController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RequisitionApplicationController::class, 'index'])->name("index")->middleware(['can:View My Requisition']);
Route::get("/create", [RequisitionApplicationController::class, 'create'])->name("create")->middleware(['can:Create My Requisition']);
Route::post("/store", [RequisitionApplicationController::class, "store"])->name("store")->middleware(['can:Create My Requisition']);
Route::get("/edit/{requisition}", [RequisitionApplicationController::class, "edit"])->name("edit")->middleware(['can:Edit My Requisition']);
Route::post("/update/{requisition}", [RequisitionApplicationController::class, "update"])->name("update")->middleware(['can:Edit My Requisition']);
Route::post("/delete/{requisition}", [RequisitionApplicationController::class, "delete"])->name("delete")->middleware(['can:Delete My Requisition']);
Route::post("/receive", [RequisitionApplicationController::class, "receive"])->name("receive");
