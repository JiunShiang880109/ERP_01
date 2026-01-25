<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function money(Request $req)
    {
        // return $req;


        $data = [
            'user_id' => $req->MemberUserId,
            'token' => $req->MemberToken,
        ];

        $result =  $this->GetMemberPoint($data);
        $result = json_decode($result);
        if($result->action == 'success') {
            return ['success' => true, 'point' => $result->talk->points];
        }else{
            return ['success' => false, 'msg' => $result->talk];

        }

    }

    //取得用戶點數
    public function GetMemberPoint($data)
    {
        $url = env('MEMBER_API_URL') . 'posapi_lookmoney';
        $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        return $output;
    }

    // 會員回饋/扣點，orderApi呼叫
    public static function PostMemberPoint($type,$userId,$token,$money,$storeid){
       return MemberController::SetMemberPoint($type ,$userId, $token, $money,$storeid);
    }


    //會員回饋點數/扣點
    public function SetMemberPoint($type ,$userId, $token, $money,$storeid )
    {
        $key = 'de9b7db54698038ede2';

        $data = [
            'source'=>'POS',
            'storeid'=> $storeid,
            'city'=> $type == 'live' ? 'live' : 'carry',
            'money'=> $money,
            'txt'=> $type == 'live' ? 'POS機累積點數' : 'POS機折抵點數',
            'user_id'=> $userId,
            'token'=> $token,
            'key' => md5($userId . $token . $money . $key),
        ];
        $url = env('MEMBER_API_URL') . 'posapi_money';
        $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        return $output;
    }
}
