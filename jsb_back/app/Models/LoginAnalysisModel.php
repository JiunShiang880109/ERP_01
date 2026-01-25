<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginAnalysis extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_analysis';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可以被批量賦值的屬性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip',
        'loginLocation',
        'loginTime',
        'year',
        'month',
        'day',
        'time',
    ];
}

class LoginAnalysisModel
{
    // 取全部進出記錄
    public static function select_login_analysis_count_db($year,$month)
    {
        return DB::select("SELECT day , loginLocation , SUM(count) AS loginCount FROM login_analysis WHERE year = '$year' AND month = '$month' GROUP BY day , loginLocation");
    }

    // 取全部詳細資料
    public static function select_login_analysis_db($year,$month)
    {
        return DB::select("SELECT ip , loginTime , logOutTime , loginLocation FROM login_analysis WHERE year = '$year' AND month = '$month' ORDER BY id desc");
    }

    // 登入紀錄寫入
    public static function insert_login_analysis_db($login)
    {
        LoginAnalysis::create($login);
    }

    // 登出記錄寫入
    public static function update_login_analysis_db($ip,$loginLocation,$logOutTime)
    {
        DB::table("login_analysis")->where('ip',$ip)->where('logOutTime',null)->where('loginLocation',$loginLocation)->orderBy('id','desc')->limit(1)->update($logOutTime);
    }
}
