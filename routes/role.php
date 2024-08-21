<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::get('/',[RoleController::class,'index'])->name('index')->middleware(['can:View Role List']);
Route::get('create',[RoleController::class,'create'])->name('create')->middleware(['can:Create Role']);
Route::get('edit/{role}',[RoleController::class,'edit'])->name('edit')->middleware(['can:Edit Role']);
Route::post('update/{role}',[RoleController::class,'update'])->name('update');
Route::post('delete/{role}',[RoleController::class,'delete'])->name('delete')->middleware(['can:Delete Role']);
Route::post('store',[RoleController::class,'store'])->name('store');
Route::get('users',[RoleController::class,'roleUsers'])->name('roleUsers')->middleware(['can:View Role User List']);
Route::get('users/{role}',[RoleController::class,'roleUserList'])->name('roleUserList');

Route::get('update-role',[RoleController::class,'editEmployeeRole'])->name('editEmployeeRole')->middleware(['can:Update Employees Role']);
Route::post('update-role',[RoleController::class,'updateEmployeeRole'])->name('updateEmployeeRole')->middleware(['can:Update Employees Role']);

