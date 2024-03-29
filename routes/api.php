<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KYCInformationController;
use App\Http\Controllers\OrderCancleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayoutLogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteBannerController;
use App\Http\Controllers\WayBillPdfPrintController;
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
Route::middleware('authToken')->post('add-product', [ProductController::class, 'addNewProduct']);
Route::middleware('authToken')->post('get-category-list', [CategoryController::class, 'getAllCategoryList']);
Route::middleware('authToken')->post('get-order-requests', [OrderController::class, 'getAllOngoingOrderList']);
Route::middleware('authToken')->post('get-order-info-by-id', [OrderController::class, 'getOrderInfoByOrderId']);
Route::middleware('authToken')->post('get-product-list', [ProductController::class, 'getProductList']);

Route::middleware('authToken')->post('update-pay-status', [OrderController::class, 'updatePaymentStatus']);
Route::middleware('authToken')->post('update-order_status', [OrderController::class, 'updateOrderStatus']);
Route::middleware('authToken')->post('set-tracking-number', [OrderController::class, 'updateTrackingNumberOfOrder']);
Route::middleware('authToken')->post('refund-approve', [OrderCancleController::class, 'refundApprove']);
Route::middleware('authToken')->post('pay-out', [PayoutLogController::class, 'addPayOutLog']);
Route::middleware('authToken')->post('get-seller-list', [PayoutLogController::class, 'getSellerList']);

Route::middleware('authToken')->post('get-payout-info', [PayoutLogController::class, 'getPayOutInfoBySeller']);
Route::middleware('authToken')->post('view-pdf', [WayBillPdfPrintController::class, 'printWayBillPdf']);
Route::middleware('authToken')->post('dashboard-data', [DashboardController::class, 'getDashboardData']);

Route::middleware('authToken')->post('add-site-banner', [SiteBannerController::class, 'addSiteBannerImage']);