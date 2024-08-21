<?php

use App\Http\Controllers\DashboardSupervisorController;
use Illuminate\Support\Facades\Route;

Route::get('/employees', [DashboardSupervisorController::class, 'employees'])->name('employees');
Route::get('/in-leave/today', [DashboardSupervisorController::class, 'inLeaveToday'])->name('inLeaveToday');
Route::get('/in-leave/tomorrow', [DashboardSupervisorController::class, 'inLeaveTomorrow'])->name('inLeaveTomorrow');
Route::get('/today/present', [DashboardSupervisorController::class, 'todayPresent'])->name('todayPresent');
Route::get('/today/absent', [DashboardSupervisorController::class, 'todayAbsent'])->name('todayAbsent');
Route::get('/today/late', [DashboardSupervisorController::class, 'todayLate'])->name('todayLate');

Route::post("/get-data-table-in-leave-today", [DashboardSupervisorController::class, "getDatatableInLeaveToday"])->name("datatableInLeaveToday");
Route::post("/get-data-table-in-leave-tomorrow", [DashboardSupervisorController::class, "getDatatableInLeaveTomorrow"])->name("datatableInLeaveTomorrow");
Route::post("/get-data-table-today-absent", [DashboardSupervisorController::class, "getDatatableTodayAbsent"])->name("datatableTodayAbsent");
Route::post("/get-data-table-today-present-late", [DashboardSupervisorController::class, "getDatatableTodayPresentLate"])->name("datatableTodayPresentLate");

