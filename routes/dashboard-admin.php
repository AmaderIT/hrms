<?php

use App\Http\Controllers\DashboardAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardAdminController::class, 'index'])->name('adminDashboard')->middleware(['can:Show Admin Dashboard']);
Route::get('/employees', [DashboardAdminController::class, 'employees'])->name('employees')->middleware(['can:Show Admin Dashboard']);
Route::get('/in-leave/today', [DashboardAdminController::class, 'inLeaveToday'])->name('inLeaveToday')->middleware(['can:Show Admin Dashboard']);
Route::get('/in-leave/tomorrow', [DashboardAdminController::class, 'inLeaveTomorrow'])->name('inLeaveTomorrow')->middleware(['can:Show Admin Dashboard']);
Route::get('/today/present', [DashboardAdminController::class, 'todayPresent'])->name('todayPresent')->middleware(['can:Show Admin Dashboard']);
Route::get('/today/absent', [DashboardAdminController::class, 'todayAbsent'])->name('todayAbsent')->middleware(['can:Show Admin Dashboard']);
Route::get('/today/late', [DashboardAdminController::class, 'todayLate'])->name('todayLate')->middleware(['can:Show Admin Dashboard']);

Route::post("/get-data-table-in-leave-today", [DashboardAdminController::class, "getDatatableInLeaveToday"])->name("datatableInLeaveToday");
Route::post("/get-data-table-in-leave-tomorrow", [DashboardAdminController::class, "getDatatableInLeaveTomorrow"])->name("datatableInLeaveTomorrow");
Route::post("/get-data-table-today-absent", [DashboardAdminController::class, "getDatatableTodayAbsent"])->name("datatableTodayAbsent");
Route::post("/get-data-table-today-present-late", [DashboardAdminController::class, "getDatatableTodayPresentLate"])->name("datatableTodayPresentLate");
