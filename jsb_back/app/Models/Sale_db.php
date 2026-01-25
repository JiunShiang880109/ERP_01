<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale_db extends Model
{
    //共用
    function select_db($table){
        return DB::table($table)->orderBy('id','desc')->get();
    }
    function select_wheredb($table,$field,$val,$orderBy){
        return DB::table($table)
        ->where($field,$val)
        ->orderBy('id',$orderBy)
        ->get();
    }
    function row_db($table,$field,$val){
        DB::table($table)->where($field,$val);
    }
    //自寫
    function all_today_db($orderType){
        if($orderType=='all'){
            return DB::select("SELECT * FROM orders WHERE TO_DAYS(orderTime) = TO_DAYS(NOW())");     
        }elseif($orderType=='shop'){
            return DB::select("SELECT * FROM orders WHERE orderType = 1 AND TO_DAYS(orderTime) = TO_DAYS(NOW())");     
        }elseif($orderType=='online'){
            return DB::select("SELECT * FROM orders WHERE orderType = 2 AND TO_DAYS(orderTime) = TO_DAYS(NOW())");     
        }  
    }
    function shop_count_db(){
        return DB::select("SELECT SUM(totalAmount) as totalAmount,count(id) as orderCount  FROM orders 
        WHERE orderType = 1 AND TO_DAYS(orderTime) = TO_DAYS(NOW())");     
    }
    function online_count_db(){
        return DB::select("SELECT SUM(totalAmount) as totalAmount,count(id) as orderCount  FROM orders 
        WHERE orderType = 2 AND TO_DAYS(orderTime) = TO_DAYS(NOW())");     
    }
    function orderDetail_db($orderNum){
        return DB::select("SELECT * FROM orders as a,
        order_detail as b 
        WHERE a.orderNum=b.orderNum AND a.orderNum='$orderNum'");
    }
    function target_db($storeId){
         return DB::table('store')
         ->where('storeId',$storeId)
         ->get();
    }




}
