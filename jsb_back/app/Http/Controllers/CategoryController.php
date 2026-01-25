<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    //取得指定店家所有商品分類
    public function category(Request $req){
        $storeId = $req->storeId;
        if(empty($storeId)){
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $category =  DB::select("SELECT * from category 
                                 WHERE storeId = $storeId
                                 AND enable = 1");
        return ['success'=>true,'category'=>$category];
    }

    public function CustomOption(Request $req)
    {
        $storeId = $req->storeId;
        if(empty($storeId)){
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $customCategories = DB::select("SELECT * from custom_category
                                 WHERE storeId = $storeId
                                 AND enable = 1
                                 ORDER BY id");
                                //  return $customCategories;
        foreach($customCategories as $key => $customCategory){
            $options =  DB::select("SELECT * from custom_option
                                 WHERE storeId = $storeId
                                 AND enable = 1
                                 AND customCateId = $customCategory->customCateId");
            $customCategory->options = $options;
        }
        
        return  ['success'=>true,'customOption'=>$customCategories];



    }
}
