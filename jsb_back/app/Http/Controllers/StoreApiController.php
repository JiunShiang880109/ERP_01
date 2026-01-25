<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class StoreApiController extends Controller
{
    public function info(Request $req){
        if(empty($req->storeId)){
            return ['success'=>false,'msg'=>'參數錯誤'];
        }
        $storeId = $req->storeId;
        $store = DB::select("SELECT * FROM store WHERE storeId =  '$storeId'" );
        $storeTable = DB::select("SELECT * FROM table_info WHERE storeId =  '$storeId'" );
        return ['success' => true, 'store'=>$store[0] , 'table'=>$storeTable];

    }

    //取得桌號
    public function tableinfo($tablecode){
        // return $tablecode;
        $tableNumber = DB::select("SELECT * FROM table_info WHERE code ='$tablecode'");
        if(!empty($tableNumber)){
            return ['success' => true, 'table' => $tableNumber[0]];
        }else{   
            return ['success' => false];
        }
    }
}
