<?php

use App\Http\Controllers\WorkSlotController;
use Illuminate\Support\Facades\Route;

Route::get("", [WorkSlotController::class, 'index'])->name("index")->middleware(['can:View Workslot List']);
Route::view("create", "work-slot.create")->name("create")->middleware(['can:Create Workslot']);
Route::post("store", [WorkSlotController::class, "store"])->name("store");
Route::get("edit/{workSlot}", [WorkSlotController::class, "edit"])->name("edit")->middleware(['can:Edit Workslot']);
Route::post("update/{workSlot}", [WorkSlotController::class, "update"])->name("update");
Route::post("delete/{workSlot}", [WorkSlotController::class, "delete"])->name("delete")->middleware(['can:Delete Workslot']);
