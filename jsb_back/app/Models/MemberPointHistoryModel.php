<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberPointHistory extends Model
{
    //指定資料表
    protected $table = 'member_point_history';

    //時間戳欄位
    public $timestamps = false;

    /**
     * 可以被批量賦值的屬性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'orderNum',
        'MemberUserId',
        'MemberPoint',
        'type'
    ];
}

class MemberPointHistoryModel
{
    // 抓當日點數紀錄資料
    public static function select_member_point_history_where_created_at($startTime,$endTime)
    {
        return DB::select("SELECT a.* , b.MemberPoint AS livePoint , c.MemberPoint AS carryPoint FROM (SELECT orderNum , created_at FROM member_point_history WHERE created_at >= '$startTime' AND created_at <= '$endTime' GROUP BY orderNum ORDER BY id DESC) AS a
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'live') AS b ON a.orderNum = b.orderNum
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'carry') AS c ON a.orderNum = c.orderNum");
    }

    // 抓當月點數紀錄資料
    public static function select_member_point_history_where_month($month)
    {
        return DB::select("SELECT a.* , b.MemberPoint AS livePoint , c.MemberPoint AS carryPoint FROM (SELECT orderNum , created_at FROM member_point_history WHERE MONTH(created_at) = $month GROUP BY orderNum) AS a
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'live') AS b ON a.orderNum = b.orderNum
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'carry') AS c ON a.orderNum = c.orderNum");
    }

    // 抓當月每日點數紀錄總和
    public static function select_member_point_history_where_day($month)
    {
        return DB::select("SELECT DAY(f.created_at) AS 'day' , SUM(f.livePoint) AS livePoint , SUM(f.carryPoint) AS carryPoint FROM 
                           (SELECT a.* , b.MemberPoint AS livePoint , c.MemberPoint AS carryPoint FROM (SELECT orderNum , created_at FROM member_point_history WHERE MONTH(created_at) = $month GROUP BY orderNum) AS a
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'live') AS b ON a.orderNum = b.orderNum
                           LEFT JOIN (SELECT orderNum, MemberPoint FROM member_point_history WHERE type = 'carry') AS c ON a.orderNum = c.orderNum) AS f GROUP BY DAY(f.created_at)");
    }
    
}
