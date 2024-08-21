<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealController;
use App\Http\Controllers\UserMealController;

Route::get("/", [MealController::class, 'index'])->name("index")->middleware(['can:View Meal']);
Route::post('/change-status', [MealController::class, 'changeStatus'])->name("changeStatus");

Route::post('/change-user-meal-status', [UserMealController::class, 'changeStatus'])->name("changeDailyMealStatus");
Route::post('/change-user-meal-tomorrow-status', [UserMealController::class, 'changeStatusOfTomorrow'])->name("changeDailyMealTomorrowStatus");

