<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginAnalysisController;
use App\Http\Controllers\MemberPointHistoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Tool;
use App\Http\Controllers\AccountantController;
use App\Http\Controllers\AccountReportCtrl;
use App\Http\Controllers\AccountVoucherCtrl;
use App\Http\Controllers\OpenAccountCtrl;
use App\Http\Controllers\VoucherTempCtrl;

//登入
Route::prefix('auth')->group(function () {
    //管理員
    Route::get('/login', [AdminController::class, 'login_page'])->name('adminShowLogin');
    Route::any('/auth', [AdminController::class, 'admin_auth'])->name('adminAuth');
    Route::any('/logout', [AdminController::class, 'logout'])->name('adminLogout');
});
//分析
Route::prefix('dashboard')->middleware('backcheckAuth')->group(function () {
    //日報表分析
    Route::any('/day', [DashboardController::class, 'day'])->name('day');
    //時段業績圖表
    Route::any('/day_chartApi', [DashboardController::class, 'day_chartApi'])->name('day_chartApi');
    Route::any('/week_chartApi', [DashboardController::class, 'week_chartApi'])->name('week_chartApi');
    //類別圖表
    Route::any('/cate_chartApi', [DashboardController::class, 'cate_chartApi'])->name('cate_chartApi');
    //月報表分析
    Route::any('/mon', [DashboardController::class, 'mon'])->name('mon');
    Route::any('/mon_chartApi', [DashboardController::class, 'mon_chartApi'])->name('mon_chartApi');
    //訂單
    Route::any('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/orders_detail', [DashboardController::class, 'orders_detail'])->name('orders_detail');
    // //收銀機分析
});
//會計管理
Route::prefix('accountant')->middleware('backcheckAuth')->group(function () {
    
    //類別管理
    Route::get('/category/index', [AccountantController::class, 'categoryIndex'])->name('accountant.category');
    Route::post('/category/add', [AccountantController::class, 'category_add'])->name('accountant.category_add');
    Route::post('/category/update', [AccountantController::class, 'category_update'])->name('accountant.category_update');
    Route::delete('/category/delete', [AccountantController::class, 'category_delete'])->name('accountant.category_delete');
    //開帳管理
    Route::get('/open_account', [OpenAccountCtrl::class, 'openAccountIndex'])->name('accountant.open_account');
    Route::post('/open_account/store', [OpenAccountCtrl::class, 'openAccountStore'])->name('accountant.store_open_account');
    Route::get('/open_account/edit', [OpenAccountCtrl::class, 'openAccountEdit'])->name('accountant.edit_open_account');
    Route::post('/open_account/update',[OpenAccountCtrl::class, 'openAccountUpdate'])->name('accountant.update_open_account');
    //關帳管理
    Route::get('/close_account',[OpenAccountCtrl::class, 'closeAccount'])->name('accountant.close_account');
    Route::post('/close_account/store', [OpenAccountCtrl::class, 'closeAccountStore'])->name('accountant.store_close_account');
    Route::get('/close_account_detail',[OpenAccountCtrl::class, 'closeAccountDetail'])->name('accountant.close_account_detail');
    Route::post('/close_account/reopen', [OpenAccountCtrl::class, 'closeAccountreopen'])->name('accountant.reopen_close_account');
    //傳票登陸
    Route::get('/voucher/index', [AccountVoucherCtrl::class, 'voucherIndex'])->name('accountant.voucher');
    Route::get('/voucher/{id}', [AccountVoucherCtrl::class, 'voucherDetail'])->name('accountant.voucher_detail');
    Route::get('/voucher_add', [AccountVoucherCtrl::class, 'voucherAdd'])->name('accountant.add_voucher');
    Route::post('/voucher/store', [AccountVoucherCtrl::class, 'voucherStore'])->name('accountant.store_voucher');
    Route::delete('/voucher/delete/{id}', [AccountVoucherCtrl::class, 'voucherDelete'])->name('accountant.delete_voucher');
    Route::get('/voucher/edit/{id}', [AccountVoucherCtrl::class, 'voucherEdit'])->name('accountant.edit_voucher');
    Route::post('/voucher/update/{id}',[AccountVoucherCtrl::class, 'voucherUpdate'])->name('accountant.update_voucher');

    //分錄API
    Route::get('/voucher_temp', [AccountVoucherCtrl::class, 'getVoucherTemp']);
    Route::get('/voucher_temp_items/{id}', [AccountVoucherCtrl::class, 'getVoucherTempItems']);
    //常用傳票管理
    Route::get('/voucher_temp/index', [VoucherTempCtrl::class, 'voucherTempIndex'])->name('accountant.voucher_temp');
    Route::get('/voucher_temp_detail/{id}', [VoucherTempCtrl::class, 'voucherTempDetail'])->name('accountant.voucher_temp_detail');
    Route::get('/voucher_temp_add', [VoucherTempCtrl::class, 'voucherTempAdd'])->name('accountant.add_voucher_temp');//儲存，共用AccountVoucherCtrl::class, 'voucherStore
    Route::delete('/voucher_temp/delete/{id}', [VoucherTempCtrl::class, 'voucherTempDelete'])->name('accountant.delete_voucher_temp');
    Route::get('/voucher_temp/edit/{id}', [VoucherTempCtrl::class, 'voucherTempEdit'])->name('accountant.edit_voucher_temp');
    Route::post('/voucher_temp/update/{id}',[VoucherTempCtrl::class, 'voucherTempUpdate'])->name('accountant.update_voucher_temp');
});
//會計報表
Route::prefix('accountReport')->middleware('backcheckAuth')->group(function () {
    //試算表
    Route::get('/trialBalance/index', [AccountReportCtrl::class, 'trialBalanceIndex'])->name('accountReport.trialBalance');
    //總分類帳
    Route::get('/generalLedger/index', [AccountReportCtrl::class, 'generalLedgerIndex'])->name('accountReport.generalLedger');
    //明細分類帳
    Route::get('/detailedLedger/index', [AccountReportCtrl::class, 'detailedLedgerIndex'])->name('accountReport.detailedLedger');
});
//左選單->商品管理->商品管理
Route::prefix('product')->middleware('backcheckAuth')->group(function () {
    /*********產品管理********/
    Route::any('/index', [ProductController::class, 'index'])->name('Products');

    // 商品匯入
    Route::post('/importCSV', [ProductController::class, 'importCSV'])->name('ProductImportCSV');
    // 商品匯出csv
    Route::get('/export/csv', [ProductController::class, 'exportCSV'])->name('ProductExportCSV');
    // 商品匯出xlsx
    Route::get('/export/xlsx', [ProductController::class, 'exportXLSX'])->name('ProductExportXLSX');
    //刪除
    Route::get('/product_del/{productId}', [ProductController::class, 'product_del'])->name('product_del');
    //新增商品
    Route::get('/add_product', [ProductController::class, 'add_product'])->name('AddProducts');
    Route::post('/product_create', [ProductController::class, 'product_create'])->name('product_create');
    Route::post('/cate2_ajax', [ProductController::class, 'cate2_ajax'])->name('cate2ajax');
    /*********產品詳情********/
    Route::get('/product_detail/{productId}', [ProductController::class, 'product_detail'])->name('ProductsDetail');
    Route::post('/product_update', [ProductController::class, 'product_update'])->name('product_update'); 
    /*********產品類別********/
    Route::get('/category', [ProductController::class, 'category'])->name('category');
    Route::post('/category_insert', [ProductController::class, 'category_insert'])->name('category_insert');
    Route::get('/category_delete', [ProductController::class, 'category_delete'])->name('category_delete');
    Route::get('/category_edit', [ProductController::class, 'category_edit'])->name('category_edit');
    Route::post('/category_update', [ProductController::class, 'category_update'])->name('category_update');
    Route::get('/spec_option_del', [ProductController::class, 'spec_option_del'])->name('spec_option_del');
    /*********產品規格********/
    Route::get('/spec', [ProductController::class, 'spec'])->name('spec');
    Route::post('/spec_insert', [ProductController::class, 'spec_insert'])->name('spec_insert');
    Route::get('/spec_delete', [ProductController::class, 'spec_delete'])->name('spec_delete');
    Route::get('/spec_edit', [ProductController::class, 'spec_edit'])->name('spec_edit');
    Route::post('/spec_update', [ProductController::class, 'spec_update'])->name('spec_update');
    Route::post('/spec_option_update', [ProductController::class, 'spec_option_update'])->name('spec_option_update');
    Route::post('/spec_option_insert', [ProductController::class, 'spec_option_insert'])->name('spec_option_insert');
});
//員工管理
Route::prefix('employee')->middleware('backcheckAuth')->group(function () {
    //員工資料
    Route::any('/show', [EmployeeController::class, 'show'])->name('employeeShow');
    Route::any('/add', [EmployeeController::class, 'add'])->name('employeeAdd');
    Route::any('/employee_detail', [EmployeeController::class, 'detail'])->name('employee_detail');
    Route::any('/employee_detailEdit', [EmployeeController::class, 'detailEdit'])->name('employee_detailEdit');
    Route::any('/employee_disable', [EmployeeController::class, 'disable'])->name('employee_disable');
    Route::any('/employee_delete', [EmployeeController::class, 'delete'])->name('employee_delete');
    Route::post('/create', [EmployeeController::class, 'create'])->name('employeeCreate');
    Route::any('/upload_employee', [EmployeeController::class, 'employee_cardIMG'])->name('upload_employee');
});

//登入分析
Route::prefix('loginAnalysis')->middleware('backcheckAuth')->group(function () {
    //登入
    Route::get('/index', [LoginAnalysisController::class, 'index'])->name('loginAnalysisIndex');
});

//庫存成本管理
Route::prefix('inventory')->middleware('backcheckAuth')->group(function () {
    
    //類別管理
    Route::get('/inventoryIndex', [InventoryController::class, 'categoryIndex'])->name('inventory.category');
    Route::post('/inventory_add', [InventoryController::class, 'category_add'])->name('inventory.category_add');
    Route::post('/category/{id}/update', [InventoryController::class, 'category_update'])->name('inventory.category_update');
    Route::delete('/category/{id}/delete', [InventoryController::class, 'category_delete'])->name('inventory.category_delete');
    //項目管理
    Route::get('/index', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/add', [InventoryController::class, 'add'])->name('inventory.add_ingredient');
    Route::post('/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/id={id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit_ingredient');
    Route::post('/{id}/update',[InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/{id}/delete', [InventoryController::class, 'delete'])->name('inventory.delete');
    //清單管理
    Route::get('/checkListIndex', [InventoryController::class, 'checkListIndex'])->name('inventory.checklist');
    Route::get('/checkList_add', [InventoryController::class, 'checkList_add'])->name('inventory.add_checklist');
    Route::post('/checkList_store', [InventoryController::class, 'checkList_store'])->name('inventory.store_checklist');
    Route::post('/inventory/checkList/arrival/{id}', [InventoryController::class,'checkListArrival'])->name('inventory.checklist.arrival');
    Route::post('/inventory/checkList/cancel/{id}', [InventoryController::class,'checkListCancel'])->name('inventory.checklist.cancel');
    Route::delete('/{id}/checkListDelete', [InventoryController::class, 'checkListDelete'])->name('inventory.checklistDelete');

});

//支出管理
Route::prefix('expenses')->middleware('backcheckAuth')->group(function () {
    //支出管理
    Route::get('/index', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/add', [ExpenseController::class, 'add'])->name('expenses.add');
    Route::post('/store', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/id={id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::post('/id/update',[ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/{id}/delete', [ExpenseController::class, 'delete'])->name('expenses.delete');
    //支出類別
    Route::get('/categoryIndex', [ExpenseController::class, 'categoryIndex'])->name('expenses.category');
    Route::post('/category_add', [ExpenseController::class, 'category_add'])->name('expenses.category_add');
    Route::post('/category/{id}/update', [ExpenseController::class, 'category_update'])->name('expenses.category_update');
    Route::delete('/category/{id}/delete', [ExpenseController::class, 'category_delete'])->name('expenses.category_delete');
});

// 點數紀錄
Route::prefix('MemberPointHistory')->middleware('backcheckAuth')->group(function () {
    //登入
    Route::get('/index', [MemberPointHistoryController::class, 'index'])->name('memberPointHistoryIndex');
});

//店家相關
Route::prefix('store')->middleware('backcheckAuth')->group(function () {
    //桌號資訊
    Route::get('/table_info', [StoreController::class, 'table_info'])->name('table_info');

    //新增桌號
    Route::post('/add_table', [StoreController::class, 'add_table'])->name('add_table');
   
    //刪除桌號
    Route::delete('/delete_table', [StoreController::class, 'delete_table'])->name('delete_table');


});

