<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;

Route::get('/',[PermissionController::class,'index'])->name('index')->middleware(['can:Permission List']);
Route::get('create',[PermissionController::class,'create'])->name('create')->middleware(['can:Create Permission']);
Route::post('store',[PermissionController::class,'store'])->name('store')->middleware(['can:Create Permission']);
Route::get('edit/{permission}',[PermissionController::class,'edit'])->name('edit')->middleware(['can:Edit Permission']);
Route::post('update/{permission}',[PermissionController::class,'update'])->name('update')->middleware(['can:Update Permission']);
Route::post('delete/{permission}',[PermissionController::class,'delete'])->name('delete')->middleware(['can:Delete Permission']);
Route::post("/get-data-table", [PermissionController::class, "getDatatable"])->name("datatable")->middleware(['can:Permission List']);
Route::get("/user-list/{permission}", [PermissionController::class, "getUserList"])->name("user-list")->middleware(['can:Permission List']);
