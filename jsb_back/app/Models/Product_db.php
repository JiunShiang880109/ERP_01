<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\XmlConfiguration\Group;

class Product_db extends Model
{
    function productId_rowdb($storeId){
        return DB::select("SELECT count(productId) as count FROM products
        WHERE storeId = '?'
        ORDER BY id DESC limit 1",[$storeId]);
    }
    function product_db($storeId){
        return DB::select("SELECT *,products.enable FROM products 
        LEFT JOIN category ON category.id=products.categoryId
        WHERE products.storeId = ? AND products.deleted_at IS NULL
        ORDER BY products.enable DESC",[$storeId]);
    }
    function cate_main_db($storeId){
        return DB::select("SELECT * FROM category
        WHERE enable =1 AND storeId = ?",[$storeId]);
    }
    function product_category_db($storeId,$productId){
        return DB::select("SELECT b.id,b.customCateTitle FROM products_with_custom as a,
        custom_category as b
        WHERE a.customCateId = b.id AND b.storeId = '$storeId'
        AND a.productId = '$productId'");
    }
    function custom_cate_db($storeId){
        return DB::select("SELECT *,a.id FROM custom_category as a,
        category as b
        WHERE a.cateId = b.id AND a.storeId = '$storeId' AND a.enable = 1");
    }
    function custom_category_db($storeId,$productId,$cateId){
        return DB::select("SELECT * FROM custom_category
        WHERE storeId = '$storeId' AND enable = 1 AND cateId='$cateId'
        AND id not in (SELECT b.id FROM products_with_custom as a,
        custom_category as b
        WHERE a.customCateId = b.id
        AND a.productId = '$productId')");
    }
    function cate_mid_db($cateId){
        return DB::select("SELECT * FROM custom_category
        WHERE cateId='$cateId'");
    }
    function product_rowdb($storeId,$productId){
        return DB::select("SELECT * FROM products 
        WHERE storeId = ? AND productId = ?",[$storeId,$productId]);
    }
    function product_update_db($update,$productId){
        DB::table('products')
        ->where('productId',$productId)
        ->update($update);
    }
    function custom_option($storeId,$customCateId){
        return DB::select("SELECT * FROM custom_option
        WHERE storeId = '$storeId' AND customCateId = '$customCateId' 
        AND enable = 1");
    }
    function cate_db($storeId){
        return DB::select("SELECT * FROM category
        WHERE storeId = '$storeId' AND enable = 1
        ORDER BY sort ASC");
    }
    function cate_rowdb($storeId,$cateId){
        return DB::select("SELECT * FROM category
        WHERE storeId = '$storeId' AND id = '$cateId' 
        AND enable = 1");
    }
    function custom_category_edit_db($customCateId,$storeId){
        return DB::select("SELECT *,custom_category.id as customCateId FROM custom_category 
        LEFT JOIN custom_option ON custom_option.customCateId=custom_category.id
        WHERE custom_category.id = '$customCateId' AND custom_category.storeId ='$storeId'
        ORDER BY custom_option.sort ASC");
    }
    function custom_category_updatedb($update,$customCateId,$storeId){
        DB::table('custom_category')
        ->where('id',$customCateId)
        ->where('storeId',$storeId)
        ->update($update);
    }
    function spec_option_updatedb($update,$optionId,$storeId){
        DB::table('custom_option')
        ->where('id',$optionId)
        ->where('storeId',$storeId)
        ->update($update);
    }
    function category_updatedb($update,$id,$storeId){
        DB::table('category')
        ->where('id',$id)
        ->where('storeId',$storeId)
        ->update($update);
    }
    function pd_chk_cate_db($storeId,$id){
        return DB::select("SELECT count(*) as num FROM category as a,
        products as b
        WHERE a.id=b.categoryId
        AND a.storeId = '$storeId' AND a.id = '$id' 
        AND a.enable = 1");
    }
    // function product_deldb($update,$productId){
    //     DB::table('products')
    //     ->where('productId',$productId)
    //     ->update($update);
    // }

    




}
