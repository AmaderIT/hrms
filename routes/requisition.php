<?php

use App\Http\Controllers\RequisitionController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RequisitionController::class, 'index'])->name("index")->middleware(['can:View Requisition']);
Route::get("/create", [RequisitionController::class, 'create'])->name("create")->middleware(['can:Create Requisition']);
Route::post("/store", [RequisitionController::class, "store"])->name("store")->middleware(['can:Create Requisition']);
Route::get("/edit/{requisition}", [RequisitionController::class, "edit"])->name("edit")->middleware(['can:Edit Requisition']);
Route::post("/update/{requisition}", [RequisitionController::class, "update"])->name("update")->middleware(['can:Edit Requisition']);
Route::post("/delete/{requisition}", [RequisitionController::class, "delete"])->name("delete")->middleware(['can:Delete Requisition']);
Route::get("/download/{requisition}", [RequisitionController::class, "download"])->name("download")->middleware(['can:Download Requisition']);
Route::get("/export/csv", [RequisitionController::class, "exportCSV"])->name("exportCSV")->middleware(['can:Export Requisition']);
Route::get("/search", [RequisitionController::class, "searchByChallan"])->name("searchByChallan")->middleware(['can:Filter option for Requisition List']);
Route::get("/find-measurement", [RequisitionController::class, "findMeasurement"])->name("findMeasurement");
Route::get("/change-status", [RequisitionController::class, "changeStatus"])->name("changeStatus");
Route::post("/get-datatable", [RequisitionController::class, "getDatatable"])->name("datatable")->middleware(['can:View Requisition']);
Route::post("/get-details", [RequisitionController::class, "getDetails"])->name("get-details")->middleware(['can:View Requisition']);
