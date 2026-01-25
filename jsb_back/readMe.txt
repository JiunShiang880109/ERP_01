ERP_01
	軟體版本
		-Composer version 2.8.4
		-php 7.4.30
		-larvael 8.83.10
		-mysql 5.7.36
	-jsb_back
		-\routes
			-web.php routes
			-api.php routes
			
		-\app
			-\Http
				-\controllers back-end
					-AccountantController.php會計類別科目
					-AccountVoucherCtrl.php會計傳票
					-DashboardController.php 報表分析
						│
						├── day()  → /dashboard/day  → dashboard/day.blade.php
						│
						├── mon()  → /dashboard/mon  → dashboard/mon.blade.php
						│
						└── orders() → /dashboard/orders → dashboard/orders.blade.php
					-ExpenseController.php 支出管理
					-InventoryController.php 成本管理
					-OrderApiController.php訂單結帳管理
					-OrderController.php訂單結帳管理
					-ProductController.php商品管理
					-Tool.php部分功能
					
				-\Middleware 
					Account_cate_main.php會計-主類別
					Account_cate_sub.php會計-子類別
					Account_item.php會計-主科目
					Account_ledger.php會計-子科目
					Account_voucher.php會計-傳票
					Account_voucher_item.php會計-傳票分錄
					Expense.php 支出
					Expense_Cate_Main.php支出類別
					Orders2_db.php 訂單
					Dashboard_db.php 報表
					Ingredients_db 成本
					Ingredients_purchase_cateMain.php成本類別
					Ingredients_puchase_order.php成本清單
					
				-Kernel.php 設定middlewar參數
			-\Traits(自定義（Custom）)一些常用的工具方法

			-\Model
				-Expense.php 支出資料庫
		-public
			-\assets
				-\js
					│
					├── chart.js dashboard圖表
					│
					├── monchart.js dashboard圖表
				
		-resources
			-\views
				-\accountant 會計前端頁面
					│
					├── voucher.blade.php 			傳票管理
					├── voucher_detail.blade.php 	傳票分錄明細
					├── add.blade.php 				新增傳票頁面
					├── category.blade.php 			類別科目管理
				-\dashboard 報表分析前端頁面
					│
					├── day()  → /dashboard/day  → dashboard/day.blade.php
					│
					├── mon()  → /dashboard/mon  → dashboard/mon.blade.php
					│
					└── orders() → /dashboard/orders → dashboard/orders.blade.php
				-\expenses 支出管理前端頁面
					│
					├── expenses/index.blade.php
					├── expenses/add.blade.php
					├── expenses/edit.blade.php
					├── expenses/category.blade.php
				-\inventory庫存成本管理
					│
					├── inventory/category.blade.php 		類別管理
					├── inventory/add_ingredient.blade.php 	品項管理-新增品項
					├── inventory/edit_ingredient.blade.php 品項管理-修改品項
					├── inventory/index.blade.php 品項管理
					├── inventory/checklist.blade.php 		訂單項目
					├── inventory/add_checklist.blade.php 	訂單項目-新增
				-product商品管理
					│
					├── product/category.blade.php 			類別管理
					├── product/category_edit.blade.php 	類別管理-編輯
					├── product/add_product.blade.php 		商品管理-新增
					├── product/product_detail.blade.php 	商品規格
					├── product/index.blade.php 			商品管理
					├── product/sepc_edit.blade.php 		
					├── product/spec.blade.php 
				-layout.blade.php側邊欄位
					
				
		-public底下用來存放前端需要的css或是圖片等 要記得執行php artisan storage:link 生成一個storage的symlink
			-storage捷徑 每次只要變動設備都要砍掉重新建立 刪除rmkdir -rf public\storage folder
		-storage用來存放前端需要的圖片
		
		-.env(設定api or database等

		基本的傳票前端
    不用作廢功能，調整補帳利用調整分錄做
    收入傳票，因為現金收入為借方，所以傳票登陸綁定貸方，支出傳票反之，常用現金交易
    轉帳傳票 可自行寫入借、貸 非現金交易，如:轉帳匯款、票據、預付等... 也可用於現金交易 借貸金額要平衡才可通過輸入
    傳票編號前四碼需寫死，主類別->子類別->主科目編號，子科目可讓使用者自行寫入
    補充:
        回沖表示應收票據給銀行後錢入帳，若分不同月份，通常直接開一筆新的轉帳傳單
        折讓=折扣
        盤盈概念=股票買在低點後來漲高所賺的
        盤虧概念=盤盈反之

    觀念重整:
        傳票編號要跟傳票日期一樣
        子科目中若有資料無法刪除，只能停用科目
        科目建立之期初金額，為科目開帳時，是否已有資產，若有就設立期初金額，若無則為0
        是否立沖和立沖日期則是單純紀錄，何時開始追帳
            
        目前從一般傳票頁面更新部分儲存常用分錄會有問題，但用常用分錄管理可以正常更新 done
            整理
                一般傳票登陸部分都只能走->儲存常用分路
                只有管理常用才能走->更新常用
        
    觀念重整:
        科目建立之期初金額，為科目開帳時，是否已有資產，若有就設立期初金額，若無則為0 done
        開帳與月度帳/沖銷帳不同，是否立沖和立沖日開帳就設定，讓系統知道從甚麼時候開始追蹤此項目的沖銷
				
	*2026.01.16	
     觀念重整:
        結轉前，需先補齊:
            期末調整、調整試算表(?、結帳、編製財務報表(
                調整分錄-不用額外建表，用account_voucher新增voucher_kind欄位標記一般、調整或結帳 done
            落地彙總表，計算期末餘額
            損益結轉（把收入/費用結到本年損益或保留盈餘）
            年結（鎖全年度傳票 + 註記年結）結轉產生本次年度損益和餘額
				
    *2026.01.20
    觀念重整:
        關帳調整：
            月結可留，不要鎖傳票，要可以回頭補調整分錄，done
            誤關帳，只可重開當月份帳表，不可重開其他月份done
        開帳：
            期初微調功能不需要，傳票調整就好(保留，若要開可能要分帳號權限) done
            
        沖帳：
            指的是傳票的沖銷，用明細分類帳查帳
        結轉：
            結算本期損益、餘額等
        傳票調整：
            補上可用年份查詢傳票，頁面只顯示當年度傳票
            現金支出、收入借/貸方寫反，需調整 done
            新增傳票的科目選項欄位有問題，第一欄不能用輸入查詢 done
        科目類別調整：
            若期中才開帳科目需紀錄，為非期初開帳項目，但還是屬於當年度帳目，要查的到，也要能計算損益等
	
	*2026.01.25	
		*拆除會計以外功能
			

		
			
				