<?php

use App\Http\Controllers\UserLoanController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UserLoanController::class, 'index'])->name("index")->middleware(['can:Pay Installment Amount']);
Route::post("/pay", [UserLoanController::class, 'pay'])->name("pay")->middleware(['can:Pay Installment Amount']);
Route::post("/custom-payment", [UserLoanController::class, 'customPayment'])->name("custom_payment")->middleware(['can:Pay Installment Amount']);
