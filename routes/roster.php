<?php

use App\Http\Controllers\RosterController;
use Illuminate\Support\Facades\Route;

Route::get("/", [RosterController::class, 'index'])->name("index")->middleware(['permission:Roster Create|Roster View|Roster Update']);

Route::get("/get", [RosterController::class, 'getRosters'])->name("get")->middleware(['permission:Roster Create|Roster View|Roster Update']);
Route::get("/info", [RosterController::class, 'getRosterListGroupData'])->name("info")->middleware(['permission:Roster Create|Roster View|Roster Update']);

Route::get("/create", [RosterController::class, 'create'])->name("create")->middleware(['permission:Roster Create|Roster View|Roster Update']);
Route::post("/create-form", [RosterController::class, 'createForm'])->name("post-create-form")->middleware(['permission:Roster Create|Roster View|Roster Update']);

Route::get("/days", [RosterController::class, 'getDaysStatus'])->name("days")->middleware(['permission:Roster Create|Roster View|Roster Update']);
Route::get("/create-form", [RosterController::class, 'createForm'])->name("create-form")->middleware(['permission:Roster Create|Roster View|Roster Update']);
Route::post("/store", [RosterController::class, "store"])->name("store")->middleware(['permission:Roster Create|Roster Update']);
Route::post("/update", [RosterController::class, "update"])->name("update")->middleware(['permission:Roster Approve|Roster Unlock']);

Route::get("/dashboard-roster-calendar", [RosterController::class, "dashboardRosterCalendar"])->name("dashboard-roster-calendar");
