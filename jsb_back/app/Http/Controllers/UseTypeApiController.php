<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UseTypeApiController extends Controller
{
     //取得指定店家用餐方式
    public function usetype(Request $req){
        $storeId = $req->storeId;
        if(empty($storeId)){
            return ['success' => false, 'msg' => '參數錯誤'];
        }
        $usetype =  DB::select("SELECT erp_options.*, erp_options.comment as use_type_title  from erp_options 
                                 WHERE value = '$storeId'
                                 AND class = 'use_type'
                                 ORDER BY sort ASC");
        return ['success'=>true,'usetype'=>$usetype];
    }

}
