<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\LoginAnalysisModel;

class LoginAnalysisController extends Controller
{
    // 登入分析首頁
    public function index()
    {
        return view('loginAnalysis.index');
    }

    // 取紀錄資料
    public function loginAnalysis(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $loginAnalysis = LoginAnalysisModel::select_login_analysis_count_db($year,$month);
        return response()->json(['loginAnalysis' => $loginAnalysis], Response::HTTP_OK);
    }

    // 取紀錄詳情資料
    public function loginAnalysisDetail(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $loginAnalysisDetail = LoginAnalysisModel::select_login_analysis_db($year,$month);
        return response()->json(['loginAnalysisDetail' => $loginAnalysisDetail], Response::HTTP_OK);
    }

    // 前台登入記錄寫入
    public function frontStageloginTimeInsert(Request $request)
    {
        $login['ip'] = $this->GetIP();
        $login['loginLocation'] = 1;
        $login['year'] = date('Y');
        $login['month'] = date('m');
        $login['day'] = date('d');
        $login['time'] = date('H:i:s');
        $login['loginTime'] = $login['year'] . '-' . $login['month'] . '-' . $login['day'] . ' ' . $login['time'];
        LoginAnalysisModel::insert_login_analysis_db($login);
    }

    // 前台登出紀錄寫入
    public function frontStageLogOutTimeInsert(Request $request)
    {
        $ip = $this->GetIP();
        $loginLocation = 1;
        $logOutTime['logOutTime'] = date("Y-m-d H:i:s");
        // 更新登登出紀錄
        LoginAnalysisModel::update_login_analysis_db($ip,$loginLocation,$logOutTime);
    }

    // 後台登入紀錄寫入
    public function backStageloginTimeInsert()
    {
        $login['ip'] = $this->GetIP();
        $login['loginLocation'] = 2;
        $login['year'] = date('Y');
        $login['month'] = date('m');
        $login['day'] = date('d');
        $login['time'] = date('H:i:s');
        $login['loginTime'] = $login['year'] . '-' . $login['month'] . '-' . $login['day'] . ' ' . $login['time'];
        LoginAnalysisModel::insert_login_analysis_db($login);
    }

    // 後台登出紀錄寫入
    public function backStageLogOutTimeInsert()
    {
        $ip = $this->GetIP();
        $loginLocation = 2;
        $logOutTime['logOutTime'] = date("Y-m-d H:i:s");
        // 更新登登出紀錄
        LoginAnalysisModel::update_login_analysis_db($ip,$loginLocation,$logOutTime);
    }





    // 取得ip
    public function GetIP(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else{
            $cip = "0";
        }
        return $cip;
    }
}
