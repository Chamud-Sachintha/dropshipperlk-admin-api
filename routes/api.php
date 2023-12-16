<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KYCInformationController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'authenticateAdminUser']);

Route::middleware('authToken')->post('get-kyc-list', [KYCInformationController::class, 'getAllKYCInfoList']);
Route::middleware('authToken')->post('update-kyc', [KYCInformationController::class, 'updateKYCInformations']);
Route::middleware('authToken')->post('add-category', [CategoryController::class, 'addNewCategory']);