<?php
namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use DB;
trait AuthTraits
{
    function verify(){   
        $employeeId = Session::get('employeeId');
        $token = Session::get('token');
        #驗證token有無錯誤
        $chekToken = DB::select("SELECT count(1) as counts FROM employee WHERE employeeId = '$employeeId' AND _token = '$token'");
        $counts = $chekToken[0]->counts;
        if($counts==0){
            echo "<script>location.href='".route('adminShowLogin')."';</script>";
            exit();
        }
        
    }
}
