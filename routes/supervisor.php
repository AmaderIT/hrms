<?php

use App\Http\Controllers\SupervisorController;
use Illuminate\Support\Facades\Route;

Route::get("/", [SupervisorController::class, "index"])->name("index")->middleware(['can:View Supervisor List']);
Route::get("/create", [SupervisorController::class, "create"])->name("create")->middleware(['can:Create Supervisor']);
Route::post("/store", [SupervisorController::class, "store"])->name("store");
Route::post("/delete/{departmentSupervisor}", [SupervisorController::class, "delete"])->name("delete")->middleware(['can:Delete Supervisor']);
Route::get("/getDepartmentByOfficeDivision/{officeDivision}", [SupervisorController::class, "getDepartmentByOfficeDivision"])->name("getDepartmentByOfficeDivision");
Route::post('/users/getEmployees',[SupervisorController::class, 'getEmployees'])->name('users.getEmployees');
Route::post('/supervisorHistory',[SupervisorController::class, 'supervisorHistory'])->name('supervisorHistory');
Route::post("/get-data-table", [SupervisorController::class, "getDatatable"])->name("datatable");
Route::get("/edit/{departmentSupervisor}", [SupervisorController::class, "edit"])->name("edit")->middleware(['can:Edit Supervisor']);
Route::post("/update/{departmentSupervisor}", [SupervisorController::class, "update"])->name("update")->middleware(['can:Edit Supervisor']);

Route::post("/lists-office-division-wise", [SupervisorController::class, "listsOfficeDivisionWise"])->name("listsOfficeDivisionWise");
Route::post("/lists-office-department-wise", [SupervisorController::class, "listsDepartmentWise"])->name("listsDepartmentWise");
