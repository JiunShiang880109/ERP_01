<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class StoreController extends Controller
{
    public function info(Request $req){
        if(empty($req->storeId)){
            return ['success'=>false,'msg'=>'參數錯誤'];
        }
        $storeId = $req->storeId;
        $store = DB::select("SELECT * FROM store WHERE storeId =  $storeId" );

        return ['success' => true, 'store'=>$store[0]];

    }


    public function table_info()
    {
        $storeId = Session::get('storeId');
        $data['table_info'] = DB::select("SELECT * FROM table_info
                    WHERE storeId = '$storeId'");

        return view("store.table_info",$data);
    }

    public function add_table(Request $req)
    {
        $storeId = Session::get('storeId');
        $tableNumber = $req->tableNumber;
        $repeat = DB::table("table_info")->where([
            ['tableNumber',$tableNumber],
            ['storeId',$storeId]
        ])->get();
        if(count($repeat) !== 0){
            return redirect()->back()->withErrors(['RepeatError' => '桌號重複']);
        }
        $code = $this->randomkeys(8);
        DB::table("table_info")->insert([
            'tableNumber'=>$tableNumber,
            'code'=>$code,
            'storeId'=>$storeId
        ]);
        return redirect()->back();
    }

    public function delete_table(Request $req)
    {
        $storeId = Session::get('storeId');
        $code =$req->code;
        $repeat = DB::table("table_info")->where([
            ['code',$code],
            ['storeId',$storeId]
        ])->delete();
        return redirect()->back();
    }

    public function randomkeys($length)   
    {   
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz   
                ABCDEFGHIJKLOMNOPQRSTUVWXYZ';  
        for($i=0;$i<$length;$i++)   
        {   
            $key .= $pattern{mt_rand(0,35)};    //生成php隨機數   
        }   
        return $key;   
    }   
}