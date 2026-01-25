<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Dashboard_db;
use Illuminate\Support\Facades\DB;
use App\Traits\HelperTraits;
use App\Http\Controllers\LayoutController;
use App\Models\Expense;

class DashboardController extends Dashboard_db
{
    use HelperTraits;
    /******日報表分析START******/
    function day()
    {
        $storeId = session()->get('storeId');
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));

        $weekTotal = $this->weektotalPerformance_db($today, $storeId);
        $last_weekTotal = $this->last_weektotalPerformance_db($today, $storeId);
        $dayCost = $this->dayCost_db($today, $storeId);
        $weekCost = $this->weekCost_db($storeId);
        //$data['weekTotal'] = number_format($weekTotal[0]->total,0,'.',',');
        
        //當週累計收入
        $data['weekTotal'] = $weekTotal[0]->total ?? 0;
        //日累計成本
        $data['daycosttotal'] = $dayCost[0]->cost ?? 0;
        //當週累計成本
        $data['costtotal'] = $weekCost[0]->cost ?? 0;
        //售出成本
        $usedCost = $this->weekUsedCost_db($storeId);
        $data['usedCost'] = $usedCost ?? 0;

        //計算毛利
        if($data['weekTotal']>0){
            //毛利
            $data['grossProfit'] = $data['weekTotal'] - $data['usedCost'];
            //毛利率
            $data['grossRate'] = round(($data['grossProfit'] / $data['weekTotal']) * 100, 2);
        }else{
            $data['grossProfit'] = 0;
            $data['grossRate'] = 0;
        }
        
        //累計支出
        $expensetotal = Expense::where('storeId', $storeId)
            ->whereBetween('date', [$weekStart, $today])
            ->sum('amount');
        $data['expensetotal'] = $expensetotal ?? 0;

        //淨利
        if($data['weekTotal']>0){
            $data['netProfit'] = floor(($data['weekTotal']-$data['usedCost'])-$data['expensetotal']);
        }else{
            $data['netProfit']=0;
        }

        //進30日成本
        $data['everydayPerform'] = $this->everyday_record_db();
        //上周
        //(當期數據-以前數據）/以前數據
        # $data['last_weekTotal'] = $last_weekTotal[0]->total;
        # $data['weekrate'] = ($weekTotal[0]->total-$last_weekTotal[0]->total)/$last_weekTotal[0]->total;
        //商品排行
        $data['pd'] = $this->products_db($today, $storeId);
        # $data['cate'] = $this->catechart_db($today);


        return view('dashboard/day', $data);
    }
    function day_chartApi(Request $Request)
    {
        $token = $Request->_token;
        $storeId = session()->get('storeId');
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $today = date('Y-m-d');
        $array['today'] = $this->opening();
        $array['yesterday'] = $this->opening();
        //今日時段業績
        foreach ($array['today'] as $key => $value) {
            $data['today'] = $this->hour_record_db($today, $storeId);
            foreach ($data['today'] as $key2 => $value2) {
                if ($value->order_hour == $value2->order_hour) {
                    $value->orderNum = $value2->orderNum;
                    $value->totalAmount = $value2->totalAmount;
                }
            }
        }
        //昨日時段業績
        foreach ($array['yesterday'] as $key => $value) {
            $data['yesterday'] = $this->hour_record_db($yesterday, $storeId);
            foreach ($data['yesterday'] as $key2 => $value2) {
                if ($value->order_hour == $value2->order_hour) {
                    $value->orderNum = $value2->orderNum;
                    $value->totalAmount = $value2->totalAmount;
                }
            }
        }
        //總日總業績 & 昨日
        $array['todayTotal'] = $this->totalPerformance_db($today, $storeId);
        $array['yesterdayTotal'] = $this->totalPerformance_db($yesterday, $storeId);
        
        //其他項目
        $weekCost = $this->weekCost_db($storeId)[0]->cost ?? 0;
        $usedCost = $this->weekUsedCost_db($storeId) ?? 0;
        $weekIcome = $this->weektotalPerformance_db($today, $storeId)[0]->total ?? 0;
        $grossProfit = $weekIcome - $usedCost;
        $grossRate = $weekIcome > 0 ? round(($grossProfit / $weekIcome) * 100 ,2) : 0;

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $expensetotal = Expense::where('storeId', $storeId)
            ->whereBetween('date', [$weekStart, $today])
            ->sum('amount');
        
        $netProfit = ($grossProfit - $expensetotal);
        
        $array['weekCost'] = $weekCost;
        $array['usedCost'] = $usedCost;
        $array['grossProfit'] = $grossProfit;
        $array['grossRate'] = $grossRate;
        $array['expensetotal'] = $expensetotal;
        $array['netProfit'] = $netProfit;

        echo json_encode($array);
    }
    function week_chartApi()
    {
        $storeId = session()->get('storeId');

        $weekIncomeArr = [];

        for ($i = 0; $i < 7; $i++) {
            $record = $this->weektotal_db($storeId, $i);
            $value = $record[0]->total ?? 0;
            $weekIncomeArr[] = floatval($value);
        }

        $array['weektotal'] = $weekIncomeArr;

        //成本
        $weekUsedCostArr = [];

        for ($i = 0; $i < 7; $i++) {
            $date = now()->startOfWeek()->addDays($i)->format('Y-m-d');
            $cost = $this->dailyUsedCost_db($storeId, $date);
            $weekUsedCostArr[] = floatval($cost);
        }

        $array['weekUsedCostArr'] = $weekUsedCostArr;

        // 支出
        $weekExpense = [];
        for($i =0; $i<7; $i++){
            $date = now()->startOfWeek()->addDays($i)->format('Y-m-d');
            $dailyExpense = Expense::where('storeId', $storeId)
                ->where('date', $date)
                ->sum('amount') ?? 0;
            $weekExpense[] = floatval($dailyExpense);
        }
        $array['weekExpense'] = $weekExpense;

        // 每日毛利 = 收入 - 售出成本
        $dailyGrossProfit = [];
        for ($i = 0; $i < 7; $i++) {
            $income = $weekIncomeArr[$i] ?? 0;
            $used = $weekUsedCostArr[$i] ?? 0;
            $dailyGrossProfit[] = $income - $used;
        }
        $array['dailyGrossProfit'] = $dailyGrossProfit;

        // 每日淨利 = 毛利 - 支出
        $dailyNetProfit = [];
        for ($i = 0; $i < 7; $i++) {
            $gross = $dailyGrossProfit[$i] ?? 0;
            $exp = $weekExpense[$i] ?? 0;
            $dailyNetProfit[] = $gross - $exp;
        }
        $array['dailyNetProfit'] = $dailyNetProfit;

        echo json_encode($array);
    }


    /******日報表分析END******/

    /******月報表分析START******/
    function mon()
    {
        $storeId = session()->get('storeId');
        $startMon = date('Y') . '-01-01';
        $endMon = date('Y') . '-12-31';
        $thisyear = date('Y');
        //每月排行
        $data['today'] = $this->mon_record_db($storeId, $startMon, $endMon);
        //商品排行
        $data['pd'] = $this->thisyear_products_db($storeId, $thisyear);


        return view('dashboard/mon', $data);
    }
    function mon_chartApi(Request $Request)
    {
        $storeId = session()->get('storeId');
        $startMon = date('Y') . '-01-01';
        $endMon = date('Y') . '-12-31';
        $last_startMon = date("Y", strtotime("-1 year")) . '-01-01';
        $last_endMon = date("Y", strtotime("-1 year")) . '-12-31';
        $array['mon'] = array('一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月');
        $array['today'] = $this->mon_opening();
        $array['yesterday'] = $this->mon_opening();
        //去年時段業績
        foreach ($array['today'] as $key => $value) {
            $data['today'] = $this->mon_record_db($storeId, $startMon, $endMon);
            foreach ($data['today'] as $key2 => $value2) {
                if ($value->order_hour == $value2->order_month) {
                    $value->orderNum = $value2->orderNum;
                    $value->totalAmount = $value2->totalAmount;
                }
            }
        }
        //去年時段業績
        foreach ($array['yesterday'] as $key => $value) {
            $data['yesterday'] = $this->mon_record_db($storeId, $last_startMon, $last_endMon);

            foreach ($data['yesterday'] as $key2 => $value2) {
                if ($value->order_hour == $value2->order_month) {
                    $value->orderNum = $value2->orderNum;
                    $value->totalAmount = $value2->totalAmount;
                }
            }
        }
        //總日總業績 & 昨日
        $array['todayTotal'] = $this->year_totalPerformance_db(date('Y'));
        $array['yesterdayTotal'] = $this->year_totalPerformance_db(date("Y", strtotime("-1 year")));

        echo json_encode($array);
    }
    /******月報表分析END******/


    /******訂單查詢START******/
    function orders(Request $req)
    {
        $storeId = session()->get('storeId');
        $startdate = $req->starttime;
        $enddate = $req->endtime;
        if ($startdate != null && $enddate != null) {
            $data['startdate'] = $startdate;
            $data['enddate'] = $enddate;
            $data['orders'] = $this->orders_date_db($startdate, $enddate, $storeId);
        } else {
            $today = date('Y-m-d');
            $data['orders'] = $this->orders_db($today, $storeId);
        }

        $data['useType_color'] = [
            1=>"text-success bg-light-success",
            2=>"text-info bg-light-info",
            3=>"text-danger bg-light-danger",
            4=>"text-warning bg-light-warning",
        ];

        $data['useType_array'] = [
            1=>"外帶",
            2=>"內用",
            3=>"自取",
            4=>"外送",
        ];
        
        return view('dashboard/orders', $data);
    }
    function orders_detail(Request $Request)
    {
        $storeId = session()->get('storeId');
        $orderNum = $Request->orderNum;
        $data['orders'] = $this->orders_detail_db($storeId, $orderNum);

        return view('dashboard/orders_detail', $data);
    }


    #日期期間
    function getDayarray($startdate, $enddate)
    {
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        $days = ($etimestamp - $stimestamp) / 86400 + 1;
        //保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
        }
        return $date;
    }
    /******類別分析END******/
}
