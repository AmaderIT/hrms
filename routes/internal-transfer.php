<?php

use App\Http\Controllers\InternalTransferController;
use Illuminate\Support\Facades\Route;

Route::get("/", [InternalTransferController::class, 'index'])->name("index")->middleware(['can:List Internal Transfer']);
Route::get("/create", [InternalTransferController::class, 'create'])->name("create")->middleware(['can:Create Internal Transfer']);
Route::post("/store", [InternalTransferController::class, "store"])->name("store")->middleware(['can:Create Internal Transfer']);
Route::get("/edit/{internalTransfer}", [InternalTransferController::class, "edit"])->name("edit")->middleware(['can:Edit Internal Transfer']);
Route::post("/update/{internalTransfer}", [InternalTransferController::class, "update"])->name("update")->middleware(['can:Edit Internal Transfer']);
Route::post("/verification/{internalTransfer}", [InternalTransferController::class, "verification"])->name("verification");
Route::post("/delete/{internalTransfer}", [InternalTransferController::class, "delete"])->name("delete")->middleware(['can:Delete Internal Transfer']);
Route::get("/generate-pdf/{format?}/{id?}", [InternalTransferController::class, "generatePdf"])->name("generate-pdf")->middleware(['can:Print Internal Transfer']);
Route::get("get-details", [InternalTransferController::class, "getDetails"])->name("getDetails")->middleware(['can:Detail Internal Transfer']);
Route::post('get-challan-approval-view', [InternalTransferController::class, 'getChallanApprovalView'])->name('getChallanApprovalView')->middleware(['can:List Internal Transfer']);
Route::post('get-challan-return-view', [InternalTransferController::class, 'getChallanReturnView'])->name('getChallanReturnView')->middleware(['can:Return Internal Transfer']);
Route::post('approval-action', [InternalTransferController::class, 'approvalAction'])->name('approvalAction')->middleware(['can:Can Internal Transfer Approve']);
Route::post('return-initiate', [InternalTransferController::class, 'returnInitiate'])->name('returnInitiate')->middleware(['can:Return Internal Transfer']);
Route::get("/details/{internalTransfer}", [InternalTransferController::class, "details"])->name("details")->middleware(['can:Detail Internal Transfer']);
Route::post('download/{internalTransfer}', [InternalTransferController::class, 'download'])->name('download')->middleware(['can:Download Internal Transfer Attachment']);
Route::post('export-excel', [InternalTransferController::class, 'exportExcel'])->name('exportExcel')->middleware(['can:Download Internal Transfer Report']);
Route::get("/find-unit-measurement", [InternalTransferController::class, "findUnitMeasurement"])->name("findUnitMeasurement");
Route::get("/find-item-name", [InternalTransferController::class, "findItemName"])->name("findItemName");
