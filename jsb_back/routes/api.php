<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryApiController;
use App\Http\Controllers\UseTypeApiController;
use App\Http\Controllers\ProductApiController;
use App\Http\Controllers\OrderApiController;
use App\Http\Controllers\StoreApiController;
use App\Http\Controllers\InvoiceApiController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\LoginAnalysisController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPointHistoryController;
use App\Http\Controllers\AdminApiController;
use App\Http\Controllers\KitchenController;


// 新結帳
use App\Http\Controllers\OrderController;










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

Route::prefix('pos')->group(function () {

    //pos機登入
    Route::post('/login', [AdminApiController::class, 'posLogin'])->name('posLogin');

    //pos機檢查會員
    Route::post('/check', [AdminApiController::class, 'posCheck'])->name('posCheck');

});


Route::prefix('category')->group(function () {

    //商品分類
    Route::post('/', [CategoryApiController::class, 'category'])->name('Category');

    //客製化選項管理
    Route::post('/CustomOption', [CategoryApiController::class, 'CustomOption'])->name('CustomOption');


});

Route::prefix('usetype')->group(function () {

    //商品分類
    Route::post('/', [UseTypeApiController::class, 'usetype'])->name('UseType');

});

Route::prefix('product')->group(function () {

    //商品
    Route::post('/', [ProductApiController::class, 'product'])->name('Product');

    //取得指定店家的指定商品
    Route::get('/{productId}', [ProductApiController::class, 'GetProduct'])->name('GetProduct');

    //檢查購物車商品是否售完
    Route::post('/checkcart', [ProductApiController::class, 'checkcart'])->name('Checkcart');

    //商品今日販賣數量
    Route::post('/salecount', [ProductApiController::class, 'salecount'])->name('Salecount');


});

Route::prefix('Order')->group(function () {






    //日結
    Route::post('/daycheckout', [OrderApiController::class, 'daycheckout'])->name('Daycheckout');

    //日結列印
    Route::post('/printdaycheckout', [OrderApiController::class, 'daycheckout_print_invoice'])->name('PrintDaycheckout');

    //桌邊點餐暫存
    Route::post('/tempOrder', [OrderApiController::class, 'tempOrder'])->name('TempOrder');

    //桌邊點餐暫存
    Route::delete('/tempOrder/{orderNum}', [OrderApiController::class, 'deletetempOrder'])->name('DeleteTempOrder');

    //桌邊點餐Linepay
    Route::post('/tempOrder/linepay', [OrderController::class, 'tempOrderLinePay'])->name('TempOrderLinePay');

    //桌邊點餐Linepay
    Route::post('/tempOrder/linepay/ConfirmUrl', [OrderController::class, 'tempOrderLinePayConfirmUrl'])->name('TempOrderLinePayConfirmUrl');

    //前台取得桌邊點餐庫存
    Route::post('/gettempOrder', [OrderApiController::class, 'gettempOrder'])->name('GetTempOrder');
    
    //前台取得桌邊點餐庫存
    Route::post('/getSpecOrder', [OrderApiController::class, 'GetSpecificOrder'])->name('GetSpecificOrder');

    //前台取得桌邊點餐庫存( LinePay版 )
    Route::post('/getSpecOrderLinepay', [OrderApiController::class, 'GetSpecificOrderLinepay'])->name('GetSpecificOrderLinepay');

    //前台桌邊點餐取得LinePay繳費紀錄
    Route::post('/getPhoneOrder', [OrderApiController::class, 'GetPhoneOrder'])->name('GetLinePayOrder');

    //LinePay桌邊點餐列印發票後更新狀態(billStatus)
    Route::post('/updateBillStatus/{orderId}', [OrderApiController::class, 'updateBillStatus'])->name('UpdateBillStatus');


    //開錢櫃
    Route::post('/open_drawer', [OrderApiController::class, 'open_drawer'])->name('open_drawer');

    //結帳(要發票)
    Route::post('/checkoutInvoice', [OrderController::class, 'checkoutInvoice'])->name('CheckoutInvoice');

    // 結帳(不要發票)
    Route::post('/checkoutWithoutInvoice', [OrderController::class, 'checkoutWithoutInvoice'])->name('CheckoutWithoutInvoice');
    Route::get('/checkOrderNumbers/{OrderNum}', [OrderController::class, 'checkOrderNumbers'])->name('checkOrderNumbers');
    // 用orderNumber取得第三方開發票網址
    Route::post('/getSmilePayUrl', [OrderController::class, 'getSmilePayUrl'])->name('getSmilePayUrl');

    // 用orderNumber取得列印明細資料
    Route::post('/getPrintInvoiceUrl', [OrderController::class, 'getPrintInvoiceUrl'])->name('getPrintInvoiceUrl');

    // 用orderNumber取得列印明細資料
    Route::post('/getPrintDetailUrl', [OrderController::class, 'getPrintDetailUrl'])->name('getPrintDetailUrl');

    //訂單查詢發票資訊
    Route::post('/testOrder/{MachineCode}/{OrderNum}', [OrderApiController::class, 'test_order'])->name('testOrder');

    //列印發票
    Route::any('/uploadPrintInvoice/{directoryName}', [OrderApiController::class, 'upload_and_print_invoice'])->name('uploadPrintInvoice');

    //取的商品所有類別
    Route::post('/getProductCustom', [OrderApiController::class, 'getProductCustom'])->name('getProductCustom');

});

//店家資訊
Route::post('/download', [ProductApiController::class, 'download'])->name('download');


Route::prefix('invoice')->group(function () {


    //最近五筆發票
    Route::post('/recent_invoice', [InvoiceApiController::class, 'recent_invoice'])->name('recent_invoice');


});

/******************************信用卡********************************** */
Route::prefix('creditCard')->group(function () {
    //啟動信用卡exe
    Route::post('/startEdcAPIexe/{machineCode}', [App\Http\Controllers\CreditCardController::class, 'startEdcAPIexe'])->name('startEdcAPIexe');
    //信用卡結帳
    Route::post('/{machineCode}', [App\Http\Controllers\CreditCardController::class, 'creditCard'])->name('creditCard');
    //關閉信用卡exe
    Route::post('/endEdcAPIexe/{machineCode}', [App\Http\Controllers\CreditCardController::class, 'endEdcAPIexe'])->name('endEdcAPIexe');
});
/******************************信用卡********************************** */


// 支付
Route::prefix('pay')->group(function () {

    // LinePay offline api
    Route::post('/LinePayOfflineSubmitOrder', [App\Http\Controllers\PayController::class, 'LinePayOfflineSubmitOrder']);


});


//結帳印發票
// Route::prefix('Order')->group(function () {
    //訂單
    // Route::any('/testOrder/{MachineCode}/{OrderNum}', [OrderController::class, 'test_order'])->name('testOrder');
    // Route::any('/testOrder2', [OrderController::class, 'test_order2'])->name('testOrder2');
    // Route::any('/uploadPrintInvoice', [OrderController::class, 'upload_and_print_invoice'])->name('uploadPrintInvoice');
    // Route::any('/orderDetail', [OrderController::class, 'order_Detail'])->name('orderDetail');
// });

/******************************登入分析********************************** */
//登入分析
Route::prefix('LoginAnalysis')->group(function () {
    // 取紀錄資料
    Route::post('/', [LoginAnalysisController::class, 'loginAnalysis'])->name('loginAnalysis');

    // 取紀錄詳細資料
    Route::post('/detail', [LoginAnalysisController::class, 'loginAnalysisDetail'])->name('loginAnalysisDetail');

    //前台登入記錄寫入
    Route::post('/frontStage', [LoginAnalysisController::class, 'frontStageloginTimeInsert'])->name('frontStageloginTimeInsert');
    //前台登出記錄寫入
    Route::post('/frontStageLogOutTimeInsert', [LoginAnalysisController::class, 'frontStageLogOutTimeInsert'])->name('frontStageLogOutTimeInsert');
});
/******************************信用卡********************************** */


/******************************會員回饋/扣點********************************** */

Route::prefix('Member')->group(function () {

    // 會員回饋or扣點(待分析)
    Route::post('/money', [MemberController::class, 'money'])->name('MemberMoney');


});

/******************************會員回饋/扣點********************************** */


// 列印測試
Route::prefix('print')->group(function () {
    // 會員回饋or扣點
    Route::post('/getOrderData', [ App\Http\Controllers\PrintController::class, 'getOrderData']);
});


Route::prefix('invoice')->group(function () {
    // 抓指定發票資料
    Route::post('/getOldInvoice', [App\Http\Controllers\InvoiceController::class, 'getOldInvoice'])->name('invoiceGetOldInvoice');
    // 補開發票補統編
    Route::post('/reGenerateInvoice', [App\Http\Controllers\InvoiceController::class, 'reGenerateInvoice'])->name('invoiceReGenerateInvoice');
});
/******************************** 廚房接單 *****************************************/

Route::prefix('kitchen')->group(function () {

    // 取得今日尚未完成訂單
    Route::get('/{storeId}', [KitchenController::class, 'GetUnFinishOrder'])->name('GetUnFinishOrder');

    // 更新品項狀態(完成)
    Route::post('/updateDetail', [KitchenController::class, 'updateDetail'])->name('updateDetail');

    // 更新訂單狀態
    Route::post('/updateOrder', [KitchenController::class, 'updateOrder'])->name('updateOrder');

    // 更新品項狀態(取消)
    Route::post('/updateDetailCancel', [KitchenController::class, 'updateDetailCancel'])->name('updateDetailCancel');


});


/******************************** 廚房接單 *****************************************/
