<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FullCalendarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\TerminationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false]);

Route::get('/daily-meal-report', [HomeController::class, 'dailyMealReport'])->name('daily.meal.report');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/temporary', [HomeController::class, 'temporary'])->name('temporary'); // TODO: Temporary Dashboard
    Route::post('/', [HomeController::class, 'filterAttendance'])->name('filterAttendance');
    Route::post('users/getUsers',[PromotionController::class, 'getUsers'])->name('users.getUsers');
    Route::post('/employee/getBanks',[EmployeeController::class, 'getBanks'])->name('getBanks');
    Route::post('/employee/getBranches',[EmployeeController::class, 'getBranches'])->name('getBranches');
    Route::post('/employee/getInstitutesFilter',[EmployeeController::class, 'getInstituteWithFilterWise'])->name('getInstitutesFilter');
    Route::post('districts/getDistricts',[EmployeeController::class, 'getDistricts'])->name('districts.getDistricts');
    Route::post('institutes/getInstitutes',[EmployeeController::class, 'getInstitutes'])->name('institutes.getInstitutes');
    Route::get('activity-log',[HomeController::class,'getActivityLog'])->name('activity')->middleware(['can:View Activity Log']);
    Route::post('/termination/getActiveUsers',[TerminationController::class, 'getActiveUsers'])->name('termination.getActiveUsers');
    Route::post('/employee/getDesignations',[EmployeeController::class, 'getDesignations'])->name('getDesignations');
});

# TODO: Remove this on production
Route::get('readme', [HomeController::class, 'readme']);

# Sync Supervisor <TODO: REMOVE THIS AFTER SYNC ON PRODUCTION SERVER>
Route::get("/sync-supervisor", [HomeController::class, "syncSupervisor"]);

Route::get('/full-calendar', [FullCalendarController::class, 'index'])->name('calendar');
Route::get('/calendar-days-status', [FullCalendarController::class, 'calendarDaysStatus'])->name('calendar-days-status');
Route::post("/full-calendar/getSpecificDateEvent", [FullCalendarController::class, "getSpecificDateEvent"])->name("getSpecificDateEvent");
Route::get('/view-leave-calendar', [FullCalendarController::class, 'viewLeaveCalendar'])->name('viewLeaveCalendar');


Route::get('/check-auth', function (){
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    $is_auth = \auth()->user() ? 1 : 0;
    echo "data:" . json_encode($is_auth) . PHP_EOL.PHP_EOL;
    ob_end_flush();
    flush();

})->name('check-auth');

