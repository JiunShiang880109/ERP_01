<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\MySqlConnection;
use PhpParser\Node\Expr\FuncCall;

class Receipt_db extends Model
{
    public function product_deldb($productId)
    {
        DB::table('product')->where('productId', $productId)->delete();
    }
    public function detail_insertdb($insert)
    {
        DB::table('product_detail')->insert($insert);
    }
    public function detail_deldb($productDetailId)
    {
        DB::table('product_detail')->where('productDetailId', $productDetailId)->delete();
    }
    //抓今天的進貨單的數量(審單、送貨中、進貨單都算)
    public function bill_order_count_db($type)
    {
        return DB::select("SELECT count(id) AS count FROM bill_order WHERE TO_DAYS(create_at) = TO_DAYS(NOW()) AND billTyle = $type ");
    }


    //進貨單沒有篩選時間時撈的本季度資料
    public function bill_order_db($status,$billtype = 0)
    {
        return DB::select("SELECT a.id , a.billTyle , a.billNumber ,  b.supplierName , a.create_at , a.deliveryDate , c.`name` , a.billState , a.remark  
                           FROM (bill_order AS a 
                           LEFT JOIN supplier AS b
                           ON a.supplierId = b.supplierId)
                           LEFT JOIN employee AS c
                           ON a.employeeId = c.employeeId
                           WHERE a.billTyle = $billtype AND a.billState = '$status' AND QUARTER(a.create_at)=QUARTER(NOW())
                           ORDER BY id DESC");
    }

    //進貨單有篩選時間時撈的資料
    public function bill_order_withTime_db($status,$startTime,$endTime,$billtype = 0)
    {
        return DB::select("SELECT a.id , a.billTyle , a.billNumber ,  b.supplierName , a.create_at , a.deliveryDate , c.`name` , a.billState , a.remark  
                           FROM (bill_order AS a 
                           LEFT JOIN supplier AS b
                           ON a.supplierId = b.supplierId)
                           LEFT JOIN employee AS c
                           ON a.employeeId = c.employeeId
                           WHERE a.billTyle = $billtype AND a.billState = '$status' AND a.create_at >= '$startTime' AND a.create_at <= '$endTime'");
    }

    //撈這筆單的狀態(辨認是訂貨單還是進貨單)
    public function bill_order_type_db($billNumber)
    {
        return DB::table('bill_order')->select('billState','billTyle')->where('billNumber',$billNumber)->get();
    }

    //訂單資訊
    public function bill_order_data_db($billNumber)
    {
        return DB::select("SELECT a.billNumber , 
                                  CONVERT(a.create_at , DATE) AS billNumber_time , 
                                  a.deliveryDate , 
                                  a.employeeId , 
                                  b.name AS employeeName , 
                                  a.remark , 
                                  a.billState , 
                                  a.year , 
                                  a.month , 
                                  a.supplierId,
                                  c.id, 
                                  c.supplierId , 
                                  c.supplierName , 
                                  c.addr , 
                                  c.mail , 
                                  c.phone , 
                                  c.fax , 
                                  c.contractName , 
                                  c.contractPhone
                           FROM bill_order AS a
                           LEFT JOIN employee AS b
                           ON a.employeeId = b.employeeId
                           LEFT JOIN supplier AS c
                           ON a.supplierId = c.supplierId
                           WHERE a.billNumber = '$billNumber'");
    }                         

    //撈那筆訂貨單的商品
    public function bill_orderdetail_db($billNumber)
    {
        return DB::select("SELECT a.id , a.billNumber , a.productId , a.ean , a.name , a.remark , a.maxQuity , a.maxPP , a.maxUnit , a.minQuity , a.minPP , a.minUnit , b.amount , b.safeAmount , a.ProductFreebieType , a.isDelete
                           FROM bill_orderdetail AS a
                           LEFT JOIN product AS b
                           ON a.productId = b.productId
                           WHERE billNumber = '$billNumber'");
    }   

    //撈商品規格價錢
    public function product_detail_specification_price_db($ean)
    {
        return DB::select("SELECT specification , price , taxType
                           FROM product_detail
                           WHERE ean = '$ean' OR productDetailId = '$ean'");
    }

    //撈員工資料
    public function employee_db()
    {
        return DB::select("SELECT employeeId , name
                           FROM employee
                           WHERE isResign = 0");   
    }

    //撈有報價的廠商跟全部的廠商
    public function supplier_quote_db($productId)
    {
        return DB::select("SELECT a.supplierId , a.supplierName ,CAST(b.quoteMaxPrice AS DECIMAL(9,2)) AS maxPrice , CAST( b.quoteMinPrice AS DECIMAL(9,2)) AS minPrice
                           FROM (SELECT supplierId, supplierName
                                 FROM supplier 
                                 WHERE disabled = 1) AS a
                           left JOIN supplier_quote AS b
                           ON a.supplierid = b.supplierid AND b.productId = '$productId'
                           ORDER BY IF(ISNULL(minPrice),1,0),minPrice ASC");
    }
    //抓有促銷進價的價格
    public function supplier_quote_promotion_db($productId,$today)
    {
        return DB::select("SELECT quoteMaxPrice AS maxPrice , quoteMinPrice AS minPrice , discount
                           FROM supplier_quote
                           WHERE supplierId IS NULL AND productId = '$productId' 
                           AND promotionStartTime <= '$today' AND promotionEndTime >= '$today'");
    }

    //撈那個商品的最近五筆最低的進貨價跟全部報價 取最低的價格
    public function bill_orderdetail_record_db($productId)
    {
        return DB::select("SELECT CAST(h.maxPrice AS DECIMAL(9,2)) AS maxPrice , CAST(h.minPrice AS DECIMAL(9,2)) AS minPrice , h.discount , h.supplierId , h.supplierName
                           FROM (SELECT d.maxPrice , d.minPrice , d.discount , f.supplierId , f.supplierName 
                                 FROM (bill_orderdetail AS d 
                                 LEFT JOIN bill_order AS e
                                 ON d.billNumber = e.billNumber)
                                 LEFT JOIN supplier AS f
                                 ON e.supplierId = f.supplierId
                                 WHERE d.productId = '$productId'
                                 UNION ALL
                                 SELECT a.quoteMaxPrice AS maxPrice , a.quoteMinPrice AS minPrice , a.discount , a.supplierId , b.supplierName
                                 FROM supplier_quote AS a
                                 LEFT JOIN supplier AS b
                                 ON a.supplierId = b.supplierId
                                 WHERE a.productId = '$productId' AND b.disabled = 1) AS h
                           WHERE h.minprice = (SELECT MIN(c.minPrice) AS minPrice 
                                               FROM ((SELECT a.minPrice 
                                                      FROM bill_orderdetail AS a , bill_order AS b 
                                                      WHERE a.productId = '$productId' AND a.minPrice != 0 AND a.billNumber = b.billNumber AND b.billTyle = 0 AND b.billState = 2 
                                                      ORDER BY a.id DESC LIMIT 5)
                                                      UNION ALL
                                                      (SELECT c.quoteMinPrice AS minPrice
                                                       FROM supplier_quote AS c 
                                                       LEFT JOIN supplier AS d
                                                       ON c.supplierId = d.supplierId
                                                       WHERE c.productId = '$productId' AND d.disabled = 1
                                                       ORDER BY c.id DESC )) AS c)");
    }

    //撈這個商品最近2天有沒有抄貨過
    public function bill_orderdetail_purchase_ox_db($productId)
    {
        return DB::select("SELECT b.id
                           FROM bill_orderdetail AS a
                           LEFT JOIN bill_order AS b
                           ON a.billNumber = b.billNumber
                           WHERE a.productId = '$productId' AND date_sub(curdate(),interval 2 day)<= b.create_at");
    }
    
    //撈所有廠商
    public function supplier_db()
    {
        // return DB::table('supplier')->get();
        return DB::table('supplier')->select('id', 'supplierId' ,'supplierName' , 'addr' , 'mail' , 'phone' , 'fax' , 'contractName' , 'contractPhone')->where('disabled',1)->get();
    }

    //index撈所有廠商 指撈名字跟id
    public function supplier_id_name_db()
    {
        return DB::table('supplier')->select('id', 'supplierId' ,'supplierName')->where('disabled',1)->get();
    }

    //撈最近五筆進貨紀錄
    public function bill_order_purchaseRecord_db($productId)
    {
        return DB::select("SELECT CONVERT(a.create_at , DATE) AS create_at , c.supplierName , b.maxQuity , b.maxUnit , b.minQuity , b.minUnit , b.maxPrice , b.minPrice , b.discount   
                           FROM (bill_order AS a
                           LEFT JOIN bill_orderdetail AS b
                           ON a.billNumber = b.billNumber)
                           LEFT JOIN supplier AS c
                           ON a.supplierId = c.supplierId
                           WHERE billTyle = 0 AND billState = 2 AND productId = '$productId'
                           ORDER BY create_at DESC
                           LIMIT 5");
    }

    //一般售價跟檔期售價
    public function product_special_product_price_db($productId)
    {
        return DB::select("SELECT a.id , a.productId , a.pp , a.unit , a.price , b.unit AS specialUnit, b.price AS specialPrice , b.specialName , CONVERT(b.starttime , DATE) AS starttime , CONVERT(b.endtime , DATE) AS endtime
                           FROM product_detail AS a
                           LEFT JOIN special_product_with_time AS b
                           ON a.productDetailId = b.productDetailId AND b.activityType = 1
                           WHERE a.productId = '$productId' 
                           ORDER BY a.pp ASC");
    }

    //撈沒有一般檔期的時候的活動
    public function product_special_product_unusual_db($productId)
    {
        return DB::select("SELECT a.id , a.productId , a.pp , a.unit , a.price , b.unit AS specialUnit, b.price AS specialPrice , b.specialName , CONVERT(b.starttime , DATE) AS starttime , CONVERT(b.endtime , DATE) AS endtime
                           FROM product_detail AS a
                           LEFT JOIN special_product_with_time AS b
                           ON a.productDetailId = b.productDetailId AND (b.activityType = 2 OR b.activityType = 3)
                           WHERE a.productId = '$productId' 
                           ORDER BY a.pp ASC");
    }

    //進貨單搜尋商品
    public function product_detail_ppUnit_db($ean)
    {
        return DB::select("SELECT a.id , d.productDetailName AS name , a.productId , a.minUnit , a.minPP , b.maxUnit , b.maxPP , c.amount , c.safeAmount
                           FROM (SELECT id , productId , productDetailName , unit AS minUnit , pp AS minPP 
                                 FROM product_detail
                                 WHERE productId = (SELECT productId
                                                    FROM product_detail
                                                    WHERE ean = '$ean' OR productDetailId = '$ean')
                                 ORDER BY pp ASC
                                 LIMIT 1) AS a
                                 LEFT JOIN (SELECT productId , productDetailName , unit AS maxUnit , pp AS maxPP
                                            FROM product_detail
                                            WHERE productId = (SELECT productId
                                                               FROM product_detail
                                                               WHERE ean = '$ean' OR productDetailId = '$ean')
                                            ORDER BY pp DESC
                                            LIMIT 1) AS b
                                 ON a.productId = b.productId
                                 LEFT JOIN product AS c
                                 ON c.productId = a.productId
                                 LEFT JOIN product_detail AS d
		                         ON (d.ean = '$ean' OR d.productDetailId = '$ean')");
    }

    //專門搜尋商品名稱的api
    public function product_detail_db($keyWord)
    {
        return DB::select("SELECT ean , productDetailId , productDetailName
                           FROM product_detail
                           WHERE ean like '$keyWord%' OR productDetailId like '$keyWord%' OR productDetailName like '%$keyWord%'");
    }

    //更新當前價錢
    public function product_detail_price_update_db($productId,$pp,$price)
    {   
        DB::table('product_detail')->where('productId',$productId)->where('pp',$pp)->update($price);
    }

    //分單並更新單的狀態
    public function bill_order_update_db($billNumber,$bill_order_information)
    {
        DB::table('bill_order')
            ->updateOrInsert(
                [
                  'billNumber' =>$billNumber,
                ],$bill_order_information);
    }

    //分單並更新單細項的廠商等等
    public function bill_order_detail_update_db($billNumber,$productId,$ProductFreebieType,$product)
    {
        DB::table('bill_orderdetail')
            ->updateOrInsert(
                [
                    'billNumber' =>$billNumber,
                    'productId' => $productId,
                    'ProductFreebieType' => $ProductFreebieType
                ], $product);
    }

    //撈省完單後的價格
    public function bill_orderdetail_supplier_db($billNumber,$productId,$ProductFreebieType)
    {
        return DB::select("SELECT a.maxPrice , a.minPrice , a.discount , a.productSupplierId AS supplierId , b.supplierName
                           FROM bill_orderdetail AS a
                           LEFT JOIN supplier AS b
                           ON a.productSupplierId = b.supplierId
                           WHERE  a.billNumber = '$billNumber' AND a.productId = '$productId' AND a.ProductFreebieType = '$ProductFreebieType'");
    }

    //撈指定廠商
    public function supplier_assign_db($supplierId)
    {
        return DB::table('supplier')->select('id', 'supplierId' ,'supplierName' , 'addr' , 'mail' , 'phone' , 'fax' , 'contractName' , 'contractPhone')->where('disabled',1)->where('supplierId',$supplierId)->get();
    }

    //更新廠商對商品的報價
    public function supplier_quote_update_db($supplierId,$productId,$supplier_quote)
    {
        DB::table('supplier_quote')
            ->updateOrInsert(
                [
                    'supplierId'=>$supplierId,
                    'productId'=>$productId
                ],
                $supplier_quote
            );
    }

    //進貨單商品寫入庫存
    public function stock_inout_log_insert_db($allProduct)
    {
        DB::table('stock_inout_log')->insert($allProduct);
    }

    //進貨單商品抓個別的總庫存跟店內庫存
    public function product_amount_storeAmount_db($productId)
    {
        return DB::table('product')->select('amount','storeAmount')->where('productId',$productId)->get();
    }

    //更新商品總庫存量以及店內庫存量
    public function product_amount_storeAmount_update($amount,$storeAmount,$productId)
    {
        DB::select("UPDATE product SET amount = '$amount',storeAmount = '$storeAmount' WHERE productId = '$productId'");
    }

    /****************************************倉庫移動紀錄**********************************/
    //抓商品資訊及商品店內庫存
    public function product_detail_storeAmount_db()
    {
        return DB::select("SELECT a.ean , a.productDetailId , a.productDetailName , a.unit , a.pp ,  b.storeAmount
                           FROM product_detail AS a
                           LEFT JOIN product AS b
                           ON a.productId = b.productId limit 0, 20");
    }

    //抓商品資訊及商品倉庫庫存
    public function product_detail_warehouseAmount_db()
    {
        return DB::select("SELECT a.ean , a.productDetailId , a.productDetailName , a.unit , a.pp , b.warehouseAmount
                           FROM product_detail AS a
                           LEFT JOIN product AS b
                           ON a.productId = b.productId limit 0, 20");
    }    
    
    public function warehouse_list()
    {
        return DB::select("SELECT * FROM warehouse WHERE role = 2");
    }

    public function warehouse_db()
    {
        return DB::select("SELECT * FROM warehouse ");
    }

    //抓倉庫權重
    public function warehouse_role_db($warehouseCode)
    {
        return DB::table("warehouse")->select("role","warehouse")->where('warehouseCode',$warehouseCode)->get();
    }

    //取得特定倉庫商品數量(除了店內)
    public function warehouse_count($warehouseCode,$SearchKey){
        if(empty($SearchKey)){
            return DB::select("SELECT id
                            FROM warehouse_product
                            WHERE warehouseCode = '$warehouseCode'");

        }else{
            return DB::select("SELECT warehouse_product.id
                                FROM warehouse_product
                                LEFT JOIN product_detail
                                ON product_detail.productId = warehouse_product.productId
                                WHERE warehouse_product.warehouseCode = '$warehouseCode'
                                AND ( product_detail.ean LIKE '%$SearchKey%' 
                                OR product_detail.productDetailId LIKE '%$SearchKey%' 
                                OR product_detail.productDetailName LIKE '%$SearchKey%')
                                GROUP BY product_detail.productId
                                ORDER BY warehouse_product.id");
        }


    }

    //取得店內倉庫商品數量
    public function market_warehouse_count($SearchKey){
        if(empty($SearchKey)){
            return DB::select("SELECT id
                            FROM product");
        }else{
            return DB::select("SELECT product.id
                                FROM product
                                LEFT JOIN product_detail
                                ON product_detail.productId = product.productId
                                WHERE product_detail.ean LIKE '%$SearchKey%' 
                                OR product_detail.productDetailId LIKE '%$SearchKey%' 
                                OR product_detail.productDetailName LIKE '%$SearchKey%'
                                GROUP BY product_detail.productId
                                ORDER BY product.id");
        }


    }
   

    //取得特定倉庫商品(除了店內)
    public function warehouse_products($warehouseCode,$first,$purpage,$SearchKey){
        if(empty($SearchKey)){
            return DB::select("SELECT
                            warehouse_product.productId,
                            warehouse_product.warehouseAmount,
                            product_detail.productDetailName,
                            product_detail.ean ,
                            warehouse_product.warehouseCode
                            FROM warehouse_product
                            LEFT JOIN product_detail
                            ON warehouse_product.productId = product_detail.productId
                            WHERE warehouseCode = '$warehouseCode'
                            GROUP BY product_detail.productId
                            ORDER BY warehouse_product.id
                            LIMIT $first,$purpage");

        }else{
            return DB::select("SELECT
                            warehouse_product.productId,
                            warehouse_product.warehouseAmount,
                            product_detail.productDetailName,
                            product_detail.ean ,
                            warehouse_product.warehouseCode
                            FROM warehouse_product
                            LEFT JOIN product_detail
                            ON warehouse_product.productId = product_detail.productId
                            WHERE warehouseCode = '$warehouseCode'
                            AND ( product_detail.ean LIKE '%$SearchKey%' 
                                OR product_detail.productDetailId LIKE '%$SearchKey%' 
                                OR product_detail.productDetailName LIKE '%$SearchKey%')
                            GROUP BY product_detail.productId
                            ORDER BY warehouse_product.id
                            LIMIT $first,$purpage");

        }
    
    }
    

    //取得店內倉庫商品
    public function warehouse_market_products($first,$purpage,$SearchKey){
         if(empty($SearchKey)){
            return DB::select("SELECT
                            product.productId,
                            product.name,
                            product.storeAmount,
                            product_detail.ean
                            FROM product
                            LEFT JOIN product_detail
                            ON product.productId = product_detail.productId
                            GROUP BY product_detail.productId
                            ORDER BY product.id
                            LIMIT $first,$purpage");

         }else{
            return DB::select("SELECT
                            product.productId,
                            product.name,
                            product.storeAmount,
                            product_detail.ean
                            FROM product
                            LEFT JOIN product_detail
                            ON product.productId = product_detail.productId
                            WHERE  product_detail.ean LIKE '%$SearchKey%'
                                OR product_detail.productDetailId LIKE '%$SearchKey%'
                                OR product_detail.productDetailName LIKE '%$SearchKey%'
                            GROUP BY product_detail.productId
                            ORDER BY product.id
                            LIMIT $first,$purpage");

        }
        

    }

    //取得店內倉庫商品細項
    public function product_storeAmount_db($keyWord)
    {
        return DB::select("SELECT a.productDetailName , a.ean , a.productDetailId , a.specification , a.unit , b.storeAmount , a.productId
                           FROM product_detail AS a
                           LEFT JOIN product AS b
                           ON a.productId = b.productId
                           WHERE a.ean = '$keyWord' OR a.productDetailId = '$keyWord'");
    }

    //取得其他倉庫商品細項
    public function warehouse_product_db($keyWord,$warehouseCode)
    {
        return DB::select("SELECT a.productDetailName , a.ean , a.productDetailId , a.specification , a.unit , b.warehouseAmount as storeAmount , a.productId
                           FROM product_detail AS a
                           LEFT JOIN warehouse_product AS b
                           ON b.productId = (SELECT productId
                                             FROM product_detail
                                             WHERE ean = '$keyWord' OR productDetailId = '$keyWord')
                              AND b.warehouseCode = '$warehouseCode'
                           WHERE a.ean = '$keyWord' OR a.productDetailId = '$keyWord'");
    } 
    
    //搜尋商品倉庫權重
    public function warehouse_both_role_db($warehouseCode,$targetWarehouse)
    {
        return DB::select("SELECT role
                           FROM warehouse 
                           WHERE warehouseCode = '$warehouseCode'
                           UNION ALL
                           SELECT role
                           FROM warehouse 
                           WHERE warehouseCode = '$targetWarehouse'");
    }   

    //抓指定倉庫商品庫存數量
    public function warehouse_amount_db($productId,$warehouseCode)
    {
        return DB::select("SELECT warehouseAmount 
                           FROM warehouse_product 
                           WHERE warehouseCode = '$warehouseCode' AND productId = '$productId'");
    }

    //抓店內倉庫商品庫存數量
    public function product_amount_db($productId)
    {
        return DB::select("SELECT storeAmount FROM product WHERE productId = '$productId'");
    }

    //更新指定倉庫商品庫存數量
    public function warehouse_product_update($warehouseCode,$productId,$dat)
    {
        DB::table('warehouse_product')
                    ->updateOrInsert(
                        [
                            'warehouseCode' => $warehouseCode,
                            'productId' => $productId
                        ], $dat);
    }

    //更新店內倉庫商品庫存數量
    public function product_storeAmount_update($productId,$productAmount)
    {
        DB::select("UPDATE product SET storeAmount = '$productAmount' WHERE productId = $productId");
    }

    //移動記錄寫入移動紀錄表
    public function warehouse_record_insert($record)
    {
        DB::table('warehouse_record')->insert($record);
    }

    //撈warehouse_record資料
    public function warehouse_record_db()
    {
        return DB::select("SELECT a.* , d.`name` , b.warehouse AS from_warehouse , c.warehouse AS to_warehouse 
                           FROM warehouse_record AS a
                           LEFT JOIN warehouse AS b
                           ON a.from_warehouseCode = b.warehouseCode
                           LEFT JOIN warehouse AS c
                           ON a.to_warehouseCode = c.warehouseCode
                           LEFT JOIN product AS d
                           ON a.productId = d.productId
                           WHERE DATE_SUB(CURDATE(), INTERVAL 30 DAY) < date(recordTime)");
    }

    //撈單位
    public function product_minUnit_db($productId)
    {
        return DB::select("SELECT unit
                           FROM product_detail
                           WHERE productId = '$productId'
                           ORDER BY pp ASC
                           LIMIT 1");
    }

    /**********************************************************退貨********************************************************* */
    public function bill_orderdetail_supplier_quote_maxPrice_db($productId) //退貨抓進價跟報價比對後最高的那個
    {
        return DB::select("SELECT quoteMinPrice AS minPrice , quoteMaxPrice AS maxPrice
                           FROM supplier_quote
                           WHERE productId = '$productId' AND quoteMaxPrice = (SELECT MAX(quoteMaxPrice)
                                                                           FROM supplier_quote
                                                                           WHERE productId = '$productId')
                           UNION ALL
                           SELECT minPrice , maxPrice
                           FROM bill_orderdetail
                           WHERE productId = '$productId' AND maxPrice = (SELECT MAX(a.maxPrice) 
                                                                      FROM bill_orderdetail AS a
                                                                      LEFT JOIN bill_order AS b
                                                                      ON a.billNumber = b.billNumber
                                                                      WHERE a.productId = '$productId' AND QUARTER(b.create_at)=QUARTER(NOW()))
                           ORDER BY maxPrice DESC
                           LIMIT 1");
    }

    //單據刪除
    public function receipt_delete($billNumber)
    {
        DB::table('bill_order')->where('billNumber',$billNumber)->delete();
    }

    //單據刪除前先找有沒有對的電話
    public function employee_phone_db($phone)
    {
        return DB::select("SELECT phone FROM employee WHERE phone = '$phone'");
    }
}
    
