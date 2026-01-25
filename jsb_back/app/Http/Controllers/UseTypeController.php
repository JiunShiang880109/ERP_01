<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UseTypeController extends Controller
{
     //取得指定店家用餐方式
    public function usetype(Request $req){
        $storeId = $req->storeId;
        if(empty($storeId)){
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $usetype =  DB::select("SELECT * from use_type 
                                 WHERE storeId = '$storeId'
                                 AND enable = 1");
        return ['success'=>true,'usetype'=>$usetype];
    }

}
