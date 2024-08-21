<?php

use App\Http\Controllers\EmployeeByPayGradeController;
use Illuminate\Support\Facades\Route;

Route::get("/", [EmployeeByPayGradeController::class, 'index'])->name("index")->middleware("can:Employee by Pay Grade");
Route::post("/pay-grade/{payGrade}", [EmployeeByPayGradeController::class, "getEmployeeByPayGrade"])->name("getEmployeeByPayGrade")->middleware("can:Employee by Pay Grade");
Route::post("/modify-employee-by-pay-grade", [EmployeeByPayGradeController::class, "modifyEmployeePayGrade"])->name("modifyEmployeePayGrade")->middleware("can:Employee by Pay Grade");
