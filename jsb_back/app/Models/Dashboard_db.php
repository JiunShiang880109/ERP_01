<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
class Dashboard_db extends Model{

    use SoftDeletes;
    function __construct(){


    }
    //日報表DB
    function weektotalPerformance_db($day,$storeId){
        return DB::select("SELECT SUM(finalPrice) as total,SUM(cost) as costtotal
        FROM orders 
        WHERE storeId = '$storeId'
        AND DATE(orderTime) >= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - 0 DAY) 
        AND DATE(orderTime) <= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - 6 DAY)");
    }
    function products_db($today,$storeId){
        return DB::SELECT("SELECT c.productId,c.product_title,SUM(b.quantity) AS quantity,b.unitPrice,b.subtotal,SUM(b.subtotal) AS sumfinalPrice 
        FROM orders as a, 
        order_detail as b, 
        products as c WHERE a.orderNum=b.orderNum 
        AND b.productId = c.productId AND a.storeId = '$storeId'
        AND unitPrice>=0 AND DATE(a.orderTime) = '$today' 
        GROUP BY productId 
        ORDER BY sumfinalPrice DESC LIMIT 100;");
    }
    public function everyday_record_db(){
        return DB::select("SELECT DATE(orderTime) AS order_month, count(id) AS orderNum ,SUM(finalPrice) AS totalAmount,SUM(cost) as costtotal 
        FROM orders
        WHERE DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(orderTime)  
        GROUP BY order_month 
        ORDER BY order_month DESC"); 
    }
    //月報表DB
    public function mon_record_db($storeId,$startMom,$endMom){
        return DB::select("SELECT MONTH(orderTime) AS order_month, count(id) AS orderNum ,SUM(finalPrice) AS totalAmount 
        FROM orders
        WHERE orderTime >= str_to_date('{$startMom} 00:00:00','%Y-%m-%d %T')    
        AND orderTime <= str_to_date('{$endMom} 23:59:59','%Y-%m-%d %T')     
        AND storeId = '$storeId'
        GROUP BY order_month 
        ORDER BY order_month ASC"); 
    }
    function  thisyear_products_db($storeId,$thisyear){
        return DB::SELECT("SELECT c.productId,c.product_title,SUM(b.quantity) AS quantity,b.unitPrice,b.subtotal,SUM(b.subtotal) AS sumfinalPrice
        FROM orders as a,
        order_detail as b,
        products as c 
        WHERE a.orderNum=b.orderNum AND b.productId = c.productId AND unitPrice>=0
        AND a.storeId = '$storeId'
        AND YEAR(a.orderTime) = '$thisyear'
        GROUP BY productId
        ORDER BY sumfinalPrice DESC
        LIMIT 500;");
    }



    // 
    function opening(){
        return DB::select("SELECT * FROM opening WHERE openType ='day'");  
    }
    function mon_opening(){
        return DB::select("SELECT * FROM opening WHERE openType ='mon'");  
    }
    public function hour_record_db($today,$storeId){
        return DB::select("SELECT HOUR(orderTime) AS order_hour, count(id) AS orderNum ,SUM(finalPrice) AS totalAmount 
        FROM orders
        WHERE orderTime >= str_to_date('{$today} 00:00:00','%Y-%m-%d %T')    
        AND orderTime <= str_to_date('{$today} 23:59:59','%Y-%m-%d %T')
        AND storeId = '$storeId'   
        GROUP BY order_hour 
        ORDER BY order_hour"); 
    }
    
    

    function totalPerformance_db($day,$storeId){
        return DB::select("SELECT SUM(finalPrice) AS total, count(id) AS orderNum  
        FROM orders WHERE storeId ='$storeId' AND DATE(orderTime) = '$day'");
    }
    function year_totalPerformance_db($day){
        return DB::select("SELECT SUM(finalPrice) AS total, count(id) AS orderNum  
        FROM orders WHERE YEAR(orderTime) = '$day'");
    }
    
    function last_weektotalPerformance_db($storeId){
        return DB::select("SELECT SUM(finalPrice) as total
        FROM orders 
        WHERE storeId = '$storeId' 
        AND DATE(orderTime) >= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) + 7 DAY) 
        AND DATE(orderTime) <= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) + 1 DAY)");
    }
    
    
    function weektotal_db($storeId,$num){
        return DB::select("SELECT SUM(finalPrice) as total
        FROM orders 
        WHERE storeId = '$storeId' AND
        DATE(orderTime) = date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - {$num} DAY) ");
    }

    //訂單查詢
    function orders_db($today,$storeId){
        return DB::select("SELECT * FROM orders
        WHERE DATE(orderTime) = '$today' AND storeId='$storeId'
        ORDER BY orderTime DESC");
    }
    function orders_date_db($startdate,$enddate,$storeId){
        return DB::select("SELECT * FROM orders
        WHERE DATE(orderTime) >= '$startdate' AND DATE(orderTime) <= '$enddate'
        AND storeId='$storeId'
        ORDER BY orderTime DESC");
    }
    function orders_detail_db($storeId,$orderNum){
       
        return DB::select("SELECT * FROM orders
        LEFT JOIN order_detail ON order_detail.orderNum=orders.orderNum
        LEFT JOIN order_detail_option ON order_detail_option.orderDetailId=order_detail.orderDetailId
        WHERE orders.storeId = '$storeId' AND orders.orderNum = '$orderNum'");
    }

    //總成本(日)
    function dayCost_db($today, $storeId){
        return DB::select("SELECT SUM(total) AS cost
        FROM ingredients_purchase_order
        WHERE storeId = '$storeId'
        AND deleted_at IS NULL
        AND DATE(purchaseDate) = '$today'");
    }

    //總成本(週)
    function weekCost_db($storeId){
        return DB::select("SELECT SUM(total) AS cost
        FROM ingredients_purchase_order
        WHERE storeId = '$storeId'
        AND deleted_at IS NULL
        AND purchaseDate >= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - 0 DAY)
        AND purchaseDate <= date_sub(curdate(),INTERVAL WEEKDAY(curdate()) - 6 DAY)");
    }
    //售出成本計算
    function dailyUsedCost_db($storeId, $date){
        return DB::table('order_detail')
            ->join('orders', 'orders.orderNum', '=', 'order_detail.orderNum')
            ->join('products', 'products.productId', '=', 'order_detail.productId')
            ->join('product_recipe', 'product_recipe.productId', '=', 'products.id')
            ->join('ingredients', 'ingredients.id', '=', 'product_recipe.ingredientId')
            ->where('orders.storeId', $storeId)
            ->whereDate('orders.orderTime', $date)
            ->selectRaw('SUM(product_recipe.usageQty * ingredients.costPerUnit * order_detail.quantity) as usedCost')
            ->value('usedCost') ?? 0;
    }

    function weekUsedCost_db($storeId){
        return DB::table('order_detail')
            ->join('orders', 'orders.orderNum', '=', 'order_detail.orderNum')
            ->join('products', 'products.productId', '=', 'order_detail.productId')
            ->join('product_recipe', 'product_recipe.productId', '=', 'products.id')
            ->join('ingredients', 'ingredients.id', '=', 'product_recipe.ingredientId')
            ->where('orders.storeId', $storeId)
            ->whereBetween('orders.orderTime', [
                now()->startOfWeek()->format('Y-m-d').' 00:00:00',
                now()->endOfWeek()->format('Y-m-d').' 23:59:59'
            ])
            ->selectRaw('SUM(product_recipe.usageQty * ingredients.costPerUnit * order_detail.quantity) as usedCost')
            ->value('usedCost') ?? 0;
    }

}
