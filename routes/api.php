<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/hrms-api-login', 'Api\AuthController@login')->name('hrmsApiLogin');

Route::middleware('auth:sanctum')->post('submit-all-completed-requisitions','Api\AuthController@submitAllCompletedRequisitions')
    ->name('submitAllCompletedRequisitions');

Route::middleware('auth:sanctum')->get('get-departments','Api\AuthController@getDepartments')
    ->name('getDepartments');

Route::middleware('auth:sanctum')->post('receive-processed-internal-transfer','Api\InternalTransferController@getChallanFromWarehouse')
    ->name('getChallanFromWarehouse');

Route::middleware('auth:sanctum')->post('receive-processed-return-internal-transfer','Api\InternalTransferController@getReturnChallanFromWarehouse')
    ->name('getReturnChallanFromWarehouse');

Route::middleware('auth:sanctum')->post('update-insert-whms-item','Api\InternalTransferController@upsertWhmsItem')
    ->name('upsertWhmsItem');

Route::post('/get-employee-info-by-code-or-email', 'Api\EmployeeController@getEmployeeInfoByCodeOrEmail')->name('getEmployeeInfoByCodeOrEmail');

Route::middleware('auth:sanctum')->post('insert-bulk-whms-item','Api\InternalTransferController@insertBulkWhmsItem')
    ->name('insertBulkWhmsItem');

Route::post('/get-employee-info-by-id', 'Api\EmployeeController@getEmployeeInfoById')->name('get_employee_info_by_id');
