<?php

use App\Http\Controllers\RoasterController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RoasterController::class, 'index'])->name("index")->middleware(['can:View Roaster List']);
Route::get("/create", [RoasterController::class, 'create'])->name("create")->middleware(['can:Create New Roasters']);
Route::get("/create/form", [RoasterController::class, 'createForm'])->name("createForm")->middleware(['can:Create New Roasters']);
Route::post("/store", [RoasterController::class, "store"])->name("store")->middleware(['can:Create New Roasters']);
Route::get("/edit/{roaster}", [RoasterController::class, 'edit'])->name("edit")->middleware(['can:Edit Roasters']);
Route::post("/update/{roaster}", [RoasterController::class, "update"])->name("update")->middleware(['can:Edit Roasters']);
Route::post("/delete/{roaster}", [RoasterController::class, "delete"])->name("delete")->middleware(['can:Delete Roasters']);

Route::get("/roaster-lock-status-update", [RoasterController::class, "roasterLockStatusUpdate"])->name("roasterLockStatusUpdate")->middleware(['can:Roaster Unlock Button']);
Route::get("/new-roaster-date-check", [RoasterController::class, "newRoasterDateCheck"])->name("newRoasterDateCheck")->middleware(['can:Create New Roasters']);

Route::get("/roaster-approval-status", [RoasterController::class, "roasterApprovalStatus"])->name("roasterApprovalStatus")->middleware(['can:Roaster Approval Permission']);
Route::post("/roaster-approval-status-update", [RoasterController::class, "roasterApprovalStatusUpdate"])->name("roasterApprovalStatusUpdate")->middleware(['can:Roaster Approval Permission']);

//UPDATE DEPARTMENT ROASTER
Route::post("/update-department-roaster/{roaster}", [RoasterController::class, "updateDepartmentRoaster"])->name("updateDepartmentRoaster")->middleware(['can:Edit Roasters']);