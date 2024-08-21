<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::get("/", [UserController::class, "index"])->name("list");
Route::get('/create', [UserController::class, 'create'])->name('create');
Route::post('/store', [UserController::class, 'store'])->name('store');

Route::post("/update/profile/{profile}", [UserController::class, "updateProfile"])->name("update.profile");
Route::post("/update/employee-info/{employeeInfo}", [UserController::class, "updateEmployeeInfo"])->name("update.employeeInfo");

Route::post("/update/education-info/store/{user}", [UserController::class, "storeDegreeUser"])->name("update.degreeUser.store");
Route::post("/update/education-info/{degreeUser}", [UserController::class, "updateDegreeUser"])->name("update.degreeUser");

Route::post("/update/bank-info/store/{user}", [UserController::class, "storeBankUser"])->name("update.bankUser.store");
Route::post("/update/bank-info/{bankUser}", [UserController::class, "updateBankUser"])->name("update.bankUser");

Route::post("/update/promotion-info/store/{user}", [UserController::class, "storePromotion"])->name("update.promotion.store");
Route::post("/update/promotion-info/{promotion}", [UserController::class, "updatePromotion"])->name("update.promotion");

Route::post("/update/warning/store/{user}", [UserController::class, "storeWarning"])->name("update.warning.store");
Route::post("/update/warning/{warning}", [UserController::class, "updateWarning"])->name("update.warning");
