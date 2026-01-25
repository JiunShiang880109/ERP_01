<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\MySqlConnection;
use PhpParser\Node\Expr\FuncCall;

class finance_db extends Model
{
    //index撈所有廠商 指撈名字跟id
    public function supplier_id_name_db()
    {
        return DB::table('supplier')->select('id' , 'supplierId' , 'supplierName' , 'rebate')->where('disabled',1)->get();
    }

    /*********************************薪資系統****************************** */
    //撈早晚班
    public function emp_shift_work_db()
    {
        return DB::table('emp_shift_work')->select('id as shift_work_id','name as shift_work_name')->get();
    }

    //撈店別
    public function store_db()
    {
        return DB::table('store')->select('storeId as store_id','storeBranch')->get();
    }

    //撈部門名稱
    public function emp_department_db()
    {
        return DB::table('emp_department')->select('id as department_id','name as department_name')->get();
    }

    //撈職位名稱
    public function emp_job_title_db()
    {
        return DB::table('emp_job_title')->select('id as job_title_id','name as job_title_name')->get(); 
    }

    

    //撈所有員工資訊
    public function employee_db()
    {
        return DB::select("SELECT a.employeeId , a.name , a.isResign ,
                                  b.id AS shift_work_id , b.name AS shift_work_name , 
                                  c.storeId AS store_id , c.storeBranch , d.id AS department_id , 
                                  d.name AS department_name , 
                                  e.id AS job_title_id , e.name AS job_title_name
                           FROM employee AS a
                           LEFT JOIN emp_shift_work AS b
                           ON a.shift_work = b.id
                           LEFT JOIN store AS c
                           ON a.storeId = c.storeId
                           LEFT JOIN emp_department AS d
                           ON a.department = d.id
                           LEFT JOIN emp_job_title AS e
                           ON a.position = e.id
                           ORDER BY a.isResign");   
    }

    //撈指定員工資訊
    public function employee_withId_db($employeeId)
    {
        return DB::table('employee')->select('employeeId','name')->where('employeeId',$employeeId)->get();
    }

    //抓那個員工的薪資資料
    public function salary_db($employeeId,$salaryYear,$salaryMonth)
    {
        return DB::select("SELECT a.id , a.salaryFieldName , b.employeeId , b.keyValue , b.remark , b.recordTime , b.salaryYear , b.salaryMonth
                           FROM salary_key AS a
                           LEFT JOIN salary AS b
                           ON a.id = b.keyId AND b.employeeId = '$employeeId' AND b.salaryYear = '$salaryYear' AND b.salaryMonth = '$salaryMonth'");
    }
    
    //抓所有薪資欄位
    public function salary_key_db()
    {
        return DB::table('salary_key')->select('*')->get();
    }

    //新增欄位
    public function salary_key_insert($data)
    {
        DB::table('salary_key')->insert($data);
    }

    //編輯欄位
    public function salary_key_update($keyId,$data)
    {
        DB::table('salary_key')->where('id',$keyId)->update($data);
    }

    //刪除欄位
    public function salary_key_delete($keyId)
    {
        DB::table('salary_key')
            ->where('id', $keyId)
            ->delete();
    }

    //員工輸入或更新薪資
    public function salary_update($employeeId,$keyId,$salaryYear,$salaryMonth,$data)
    {
       DB::table('salary')
         ->updateOrInsert(
             [
                 'employeeId' => $employeeId,
                 'keyId' => $keyId,
                 'salaryYear' => $salaryYear,
                 'salaryMonth' => $salaryMonth
             ], $data); 
    }
    
    //員工輸入或更新薪資
    public function salary_total_update($employeeId,$salaryYear,$salaryMonth,$totalData)
    {
       DB::table('salary')
         ->updateOrInsert(
             [
                 'employeeId' => $employeeId,
                 'salaryYear' => $salaryYear,
                 'salaryMonth' => $salaryMonth,
                 'type' => 1
             ], $totalData); 
    } 
    
    /*****************************收銀機分析************************** */
    //抓全部收銀機
    public function machine_db()
    {
        return DB::table('machine')->select('id','machineCode')->get();
    }

    //用employeeCode 或 phone 抓employeeId
    public function employee_employeeId_db($employeeCode,$phone)
    {
        return DB::select("SELECT employeeId 
                           FROM employee
                           WHERE employeeCode = '$employeeCode' OR phone = '$phone'");
    }

    //抓今日總結帳數(來客數)
    public function order_id_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT id
                           FROM orders
                           WHERE machineId = '$machineId' AND orderTime >= '$startTime' AND orderTime <= '$endTime'");
    }

    //抓今日總額、現金收入、現今找零
    public function orders_money_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(finalPrice) AS finalPrice , 
                                  SUM(cashIncome) AS cashIncome , 
                                  SUM(cashChange) AS cashChange , 
                                  SUM(giftCardAmount) AS giftCardAmount , 
                                  SUM(totalAmount) AS totalAmount , 
                                  SUM(freeTaxSalesAmount) AS freeTaxSalesAmount , 
                                  SUM(giftCardCount) AS giftCardCount
                           FROM orders
                           WHERE machineId = '$machineId' AND orderTime >= '$startTime' AND orderTime <= '$endTime'");
    }

    //抓台灣pay 跟 信用卡
    public function orders_finalPrice_payMethod_db($machineId,$payMethod,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(finalPrice) AS finalPrice
                           FROM orders
                           WHERE machineId = '$machineId' AND orderTime >= '$startTime' AND orderTime <= '$endTime' AND payMethod = '$payMethod'");
    }

    //抓員工名字
    public function employee_name_db($employeeId)
    {
        return DB::select("SELECT name FROM employee WHERE employeeId = '$employeeId'");
    }

    //抓發票號碼今日最新與最舊
    public function order_invoiceNumber_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT a.invoiceNumber AS startInvoiceNumber , b.invoiceNumber AS endInvoiceNumber
                           FROM orders AS a
                           LEFT JOIN orders AS b
                           ON b.invoiceNumber = (SELECT invoiceNumber
                                                 FROM orders
                                                 WHERE machineId = '$machineId' AND orderTime >= '$startTime' AND orderTime <= '$endTime'
                                                 ORDER BY orderTime DESC
                                                 LIMIT 1)
                           WHERE a.invoiceNumber = (SELECT invoiceNumber
                                                    FROM orders
                                                    WHERE machineId = '$machineId' AND orderTime >= '$startTime' AND orderTime <= '$endTime'
                                                    ORDER BY orderTime ASC
                                                    LIMIT 1)");
    }

    //抓登入時間、交易取消、交易取消次數、開錢櫃次數
    public function machine_log_db($machineId,$startTime,$endTime,$logType)
    {
        return DB::select("SELECT SUM(a.cancelCount) AS cancelCount, SUM(a.cancelPrice) AS cancelPrice, SUM(a.cashDrawer) AS cashDrawer, b.loginTime , e.logTime 
                           FROM machine_log AS a
                           LEFT JOIN machine_log AS b
                           ON b.loginTime = (SELECT c.loginTime
                                             FROM machine_log AS c
                                             WHERE c.machineCode = '$machineId' AND '$startTime' <= c.loginTime AND '$endTime' >= c.loginTime AND c.loginTime IS NOT NULL 
                                             ORDER BY c.loginTime ASC
                                             LIMIT 1)
                            LEFT JOIN machine_log AS d
                            ON d.logTime = (SELECT logTime
                                            FROM machine_log
                                            WHERE machineCode = '$machineId' AND '$startTime' <= logTime AND '$endTime' >= logTime AND logType = $logType)
                            LEFT JOIN machine_log AS e
                            on e.logTime = (SELECT logTime
                                            FROM machine_log
                                            WHERE machineCode = '$machineId' AND logTime >= '$startTime' AND logTime <= '$endTime' AND trueCash IS NOT NULL
                                            ORDER BY logTime DESC
                                            LIMIT 1) 
                           WHERE a.machineCode = '$machineId' AND a.logTime >= '$startTime' AND a.logTime <= '$endTime'");
    }

    //抓班結實際金額、日結/早班實際金額、日結/晚班實際金額
    public function machine_log_trueCash_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT trueCash FROM machine_log WHERE machineCode = '$machineId' AND logType = '1' AND logTime >= '$startTime' AND logTime <= '$endTime'");
    }

    //抓自訂減價、折扣
    public function order_deatail_cut_off_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(b.discountCut) AS discountCut, SUM(b.discountOff) AS discountOff
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           WHERE a.machineId = '$machineId' AND a.orderTime >= '$startTime' AND a.orderTime <= '$endTime'");
    }
    
    //抓折價券
    public function order_deatail_discount_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(b.finalPrice) AS discount
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           WHERE a.machineId = '$machineId' AND a.orderTime >= '$startTime' AND a.orderTime <= '$endTime' AND b.productType = 4");
    }
    
    //抓換貨數量、價格
    public function order_deatail_changeProduct_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(b.finalPrice) AS changeProductPrice , SUM(b.changeProductCount) AS changeProductCount
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           WHERE a.machineId = '$machineId' AND a.orderTime >= '$startTime' AND a.orderTime <= '$endTime' AND b.productType = 3");  
    }

    //抓生鮮產品
    public function order_detail_fresh_product_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(b.quantity) AS freshQuantity , SUM(b.finalPrice) AS freshPrice
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           WHERE a.machineId = '$machineId' AND a.orderTime >= '$startTime' AND a.orderTime <= '$endTime' AND b.ean LIKE '290%'");
    }

    //抓大量購買商品
    public function order_detail_batchProduct_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT b.batchProductPrice
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           WHERE a.machineId = '$machineId' AND a.productType = 3 AND a.orderTime >= '$startTime'  AND a.orderTime <= '$endTime'");
    }

    //抓麵包商品
    public function order_deatail_bread_db($machineId,$startTime,$endTime)
    {
        return DB::select("SELECT SUM(b.quantity) AS breadQuantity , SUM(b.finalPrice) AS breadPrice
                           FROM orders AS a
                           LEFT JOIN order_detail AS b
                           ON a.orderNum = b.orderNum
                           LEFT JOIN product AS c
                           ON b.productId = c.productId
                           WHERE a.machineId = '$machineId' AND a.orderTime >= '$startTime' AND a.orderTime <= '$endTime' AND c.cateMainId = 73");
    }

    //先抓那個班的主要結帳人
    public function machine_log_employeeId_db($startTime,$endTime,$machineCode)
    {
        return DB::select("SELECT employeeId , logTime
                           FROM machine_log 
                           WHERE logType = 1 AND logTime >= '$startTime' AND logTime <= '$endTime' AND machineCode = '$machineCode'");
    }

    //先抓那個班的最後主要結帳人
    public function machine_log_last_employeeId_db($startTime,$endTime,$machineCode)
    {
        return DB::select("SELECT employeeId
                           FROM machine_log 
                           WHERE logType = 1 AND logTime >= '$startTime' AND logTime <= '$endTime' AND machineCode = '$machineCode'
                           ORDER BY logTime DESC
                           LIMIT 1");
    }
    
    //抓結束時間
    public function machine_log_logInTime_db($employeeId,$startTime,$endTime,$machineCode)
    {
        return DB::select("SELECT loginTime
                           FROM machine_log 
                           WHERE employeeId = '$employeeId' AND loginTime >= '$startTime' AND loginTime <= '$endTime' AND machineCode = '$machineCode'
                           ORDER BY loginTime ASC 
                           LIMIT 1");
    }
    /******************************請款單********************************** */
    //抓該廠商那個請款月份有的單
    public function bill_order_info_db($supplierId,$year,$month)
    {
        return DB::select("SELECT a.billNumber , a.billTyle , a.supplierId , b.supplierName , FLOOR(((c.minPrice*c.minQuity) + (c.maxPrice*c.maxQuity))*(('100'-c.discount)/100)) AS price  ,  a.create_at , CONCAT(a.year,'-',a.month) AS date , a.payType , a.remark , a.discountNumber
                           FROM bill_order AS a
                           LEFT JOIN supplier AS b
                           ON a.supplierId = b.supplierId
                           LEFT JOIN bill_orderdetail AS c
                           ON a.billNumber = c.billNumber
                           WHERE a.supplierId = '$supplierId' AND `year` = '$year' AND `month` = '$month' AND (a.billTyle = '0' OR a.billTyle = '3') AND a.billState = 2
                           GROUP BY a.billNumber
                           ORDER BY a.billTyle ASC , a.create_at DESC");
    }

    //抓指定月份但是沒有廠商的資料(就是全部的資料)
    public function bill_order_without_supplierId_info_db($year,$month)
    {
        return DB::select("SELECT a.billNumber , a.billTyle , a.supplierId , b.supplierName , FLOOR(((c.minPrice*c.minQuity) + (c.maxPrice*c.maxQuity))*(('100'-c.discount)/100)) AS price  ,  a.create_at , CONCAT(a.year,'-',a.month) AS date , a.payType , a.remark , a.discountNumber
                           FROM bill_order AS a
                           LEFT JOIN supplier AS b
                           ON a.supplierId = b.supplierId
                           LEFT JOIN bill_orderdetail AS c
                           ON a.billNumber = c.billNumber
                           WHERE a.`year` = '$year' AND a.`month` = '$month' AND (a.billTyle = '0' OR a.billTyle = '3') AND a.billState = 2
                           GROUP BY a.billNumber
                           ORDER BY a.create_at DESC");
    }

    //抓那個廠商單月的單有沒有折扣編碼
    public function bill_order_discountNumber_db($supplierId,$year,$month)
    {
        return DB::select("SELECT discountNumber
                           FROM bill_order 
                           WHERE supplierId = '$supplierId' AND `year` = '$year' AND `month` = '$month' AND discountNumber IS NOT NULL LIMIT 1");
    }

    //抓該廠商的自訂折扣
    public function supplier_discount_db($supplierId)
    {
        return DB::table('supplier_discount')->select('*')->where('supplierId',$supplierId)->get();
    }

    //抓已編輯過的訂單折扣
    public function bill_order_supplier_discount_db($discountNumber)
    {
        return DB::select("SELECT a.* , b.discountName
                           FROM bill_order_supplier_discount AS a
                           LEFT JOIN supplier_discount AS b
                           ON a.discountKey = b.discountKey
                           WHERE a.discountNumber = '$discountNumber'");
    }

    //更新或寫入編輯過的折扣
    public function bill_order_supplier_discount_updateOrInsert($data)
    {
        DB::table('bill_order_supplier_discount')
                    ->updateOrInsert(
                        [
                            'discountKey' => $data['discountKey'],
                        ], $data);
    }

    //更新訂單狀態
    public function bill_order_update($billNumber,$bill_order)
    {
        DB::table('bill_order')->where('billNumber',$billNumber)->update($bill_order);
    }

    //撈當月有要請款的廠商
    public function supplier_month_db($year,$month)
    {
      return DB::select("SELECT a.id , a.supplierId , a.supplierName , a.rebate
                           FROM supplier AS a
                           LEFT JOIN bill_order AS b
                           ON a.supplierId = b.supplierId
                           WHERE b.`year` = '$year' AND b.`month` = '$month' AND (b.billTyle = '0' OR b.billTyle = '3') AND b.billState = 2
                           GROUP BY supplierId");

    }


}
    
