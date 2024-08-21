<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::get("/", [EmployeeController::class, "index"])->name("index")->middleware(['can:View Employee List']);
Route::get("/create", [EmployeeController::class, "create"])->name("create")->middleware(['can:Create New Employee']);
Route::get("/create/{employee}", [EmployeeController::class, "createMiscellaneous"])->name("createMiscellaneous")->middleware(['can:Create New Employee']);
Route::post("/store", [EmployeeController::class, "store"])->name("store")->middleware(['can:Create New Employee']);
Route::post("/store/{employee}", [EmployeeController::class, "storeMiscellaneous"])->name("storeMiscellaneous")->middleware(['can:Create New Employee']);
Route::get("/edit/{employee}", [EmployeeController::class, "edit"])->name("edit")->middleware(['can:Edit Employee Info']);
Route::get("/edit/miscellaneous/{employee}", [EmployeeController::class, "editMiscellaneous"])->name("editMiscellaneous")->middleware(['can:Edit Employee Info']);
Route::post("/delete/{employee}", [EmployeeController::class, "delete"])->name("delete");
Route::post("/change-status/{employee}", [EmployeeController::class, "changeStatus"])->name("changeStatus")->middleware(['can:Change Employee Status']);
Route::post("/update/{employee}", [EmployeeController::class, "update"])->name("update")->middleware(['can:Edit Employee Info']);
Route::post("/update/miscellaneous/{employee}", [EmployeeController::class, "updateMiscellaneous"])->name("updateMiscellaneous")->middleware(['can:Edit Employee Info']);
Route::get("/change-password/{employee}", [EmployeeController::class, "changePassword"])->name("changePassword")->middleware(['can:Change Employee Password']);
Route::post("/update-password/{employee}", [EmployeeController::class, "updatePassword"])->name("updatePassword");
Route::post("/reset-password/{employee}", [EmployeeController::class, "resetPassword"])->name("resetPassword")->middleware(['can:Reset Employee Password']);
Route::post("/import", [EmployeeController::class, "import"])->name("import")->middleware(['can:Import Employee Info']);
Route::get('/export', [EmployeeController::class, "export"])->name("export")->middleware(['can:Export Employee Info']);
Route::get("/profile/{employee}", [EmployeeController::class, "profile"])->name("profile");
Route::post("/update-profile/{employee}", [EmployeeController::class, "updateProfile"])->name("updateProfile");
Route::get("/district-by-division/{division}", [EmployeeController::class, "districtByDivision"])->name("districtByDivision");
Route::get("/full-profile/{employee}", [EmployeeController::class, "fullProfile"])->name("fullProfile");

Route::get("/filter", [EmployeeController::class, "searchByOfficeDivisionDepartment"])->name("searchByOfficeDivisionDepartment");
Route::post("/employee/department", [EmployeeController::class, "getEmployeeByDepartment"])->name("getEmployeeByDepartment");

Route::get("/sync-with-biotime", [EmployeeController::class, "syncWithBioTime"])->name("syncWithBioTime")->middleware(["can:Sync Employee to Attendance Device"]);
Route::get("export-profile", [EmployeeController::class, "exportProfile"])->name("exportProfile")->middleware(["can:Export Employee Profile"]);
Route::post("export-profile", [EmployeeController::class, "generateExportProfile"])->name("generateExportProfile")->middleware(["can:Export Employee Profile"]);

Route::post("/get-data-table", [EmployeeController::class, "getDatatable"])->name("datatable");
Route::post("/show-profile", [EmployeeController::class, "showProfile"])->name("showProfile");
Route::post("/department-modal", [EmployeeController::class, "modalDepartment"])->name("modalDepartment");
Route::post("/designation-modal", [EmployeeController::class, "modalDesignation"])->name("modalDesignation");
Route::post("/institution-modal", [EmployeeController::class, "modalInstitution"])->name("modalInstitution");

Route::post('/rejoin-employee',[EmployeeController::class,"rejoinEmployee"])->name("rejoinEmployee");
Route::get('/profile-download/{employee}', [EmployeeController::class, 'profileDownload'])->name('profileDownload');
Route::post("/office-division-modal", [EmployeeController::class, "modalOfficeDivision"])->name("modalOfficeDivision");
Route::post('/reset-employee-password',[EmployeeController::class,"resetEmployeePassword"])->name("resetEmployeePassword");
Route::post('/sync-employee-device',[EmployeeController::class,"syncEmployeeDevice"])->name("syncEmployeeDevice")->middleware(["can:Sync Employee Device"]);

