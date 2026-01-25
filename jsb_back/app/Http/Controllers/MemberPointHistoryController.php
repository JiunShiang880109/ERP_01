<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MemberPointHistoryModel;

class MemberPointHistoryController extends Controller
{
    // 點數紀錄首頁
    public function index()
    {
        return view('memberPointHistory.index');
    }

    // 取得點數紀錄資料
    public function memberPointHistory(Request $request)
    {
        $date = $request->date;
        $startTime = $date . ' ' . '00:00:00';
        $endTime = $date . ' ' . '23:59:59';
        
        // 日的資料
        $memberPointHistory['history'] = MemberPointHistoryModel::select_member_point_history_where_created_at($startTime,$endTime);
        $dayLivePointSum = 0;
        $dayCarryPointSum = 0;
        foreach($memberPointHistory['history'] as $history){
            $dayLivePointSum += $history->livePoint;
            $dayCarryPointSum += $history->carryPoint;
        }
        
        $memberPointHistory['dayLivePointSum'] = $dayLivePointSum;  // 當天回饋點數
        $memberPointHistory['dayCarryPointSum'] = -$dayCarryPointSum;   //當天使用點數
        $memberPointHistory['dayPointSum'] = $dayLivePointSum - $dayCarryPointSum;  //當天點數總和

        // 月的資料
        $explodeMonth = explode('-',$date);
        $month = (int)$explodeMonth[1];
        $monthPointHistorys = MemberPointHistoryModel::select_member_point_history_where_month($month);
        $monthLivePointSum = 0;
        $monthCarryPointSum = 0;
        foreach($monthPointHistorys as $monthPointHistory){
            $monthLivePointSum += $monthPointHistory->livePoint;
            $monthCarryPointSum += $monthPointHistory->carryPoint;
        }
        $memberPointHistory['monthLivePointSum'] = $monthLivePointSum;  // 當天回饋點數
        $memberPointHistory['monthCarryPointSum'] = -$monthCarryPointSum;   //當天使用點數
        $memberPointHistory['monthPointSum'] = $monthLivePointSum - $monthCarryPointSum;    //當天點數總和

        // 當月每日點數紀錄總和
        $memberPointHistory['monthDayPointSum'] = MemberPointHistoryModel::select_member_point_history_where_day($month);

        return response()->json(['memberPointHistory' => $memberPointHistory], Response::HTTP_OK); 
    } 
}
