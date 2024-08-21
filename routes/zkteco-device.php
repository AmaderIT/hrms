<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DeviceController::class, 'index'])->name("index")->middleware(['can:View Devices List']);
Route::view("/create", "device.create")->name("create")->middleware(['can:Create Devices']);
Route::post("/store", [DeviceController::class, "store"])->name("store")->middleware(['can:Create Devices']);
Route::get("/edit/{device}", [DeviceController::class, "edit"])->name("edit")->middleware(['can:Edit Devices']);
Route::post("/update/{device}", [DeviceController::class, "update"])->name("update")->middleware(['can:Edit Devices']);
Route::post("/delete/{device}", [DeviceController::class, "delete"])->name("delete")->middleware(['can:Delete Devices']);

Route::get("/online-device-list", [DeviceController::class, "viewAttendanceDeviceList"])->name("online-device-list")->middleware(['can:View Online Devices List']);
Route::get("/online-device-list-data", [DeviceController::class, "getAttendanceDeviceListByApi"])->name("online-device-list-data")->middleware(['can:View Online Devices List']);
