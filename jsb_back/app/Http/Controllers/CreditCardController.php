<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CreditCardController extends Controller
{
    //啟動信用卡exe
    public function startEdcAPIexe()
    {
        $exePath = realpath("EdcAPI-0.1\EdcAPI.exe"); //EdcAPI內會有一個EdcAPI.exe 使用信用卡機前須打開這個exe 位置看那個exe的位置去做變更
        exec($exePath);
        // return $exePath;
    }

    //信用卡結帳
    public function creditCard(Request $request)
    {   
        $transType = (int)$request->type;   //交易類別 1->一般交易、2->退貨、3->分期、4->分期交易退貨(2、3、4關起來了用不到)
        $transAmount = (int)$request->price;    //交易金額

        //api所需要的header
        $r_header = array(
            'Content-Type: application/json',
            'accept:text/plain',
        );

        //api所需要的body
        $url = 'http://localhost:5000/request'; //http port打5000、https port打5001 
        $r_body = json_encode(array(
            "transType" => $transType,
            "transAmount" => $transAmount,
        ));       

        //使用curl函式來進行連線
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $r_header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $r_body);
        $creditCard = curl_exec($curl);
        return $creditCard; //信用卡機response回傳 將data內的content轉成int 1->時間到沒刷 3->兩張信用卡失敗 6->忘記是啥了 前台要擋沒刷過的部分
    }

    //關閉信用卡exe
    public function endEdcAPIexe()
    {
        $endExe = "taskkill /f /im EdcAPI.exe"; //cmd指令 停止正在進行的程序
        exec($endExe);
    }
    
}
