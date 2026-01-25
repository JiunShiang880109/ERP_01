<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\AccountTraits;
use App\Models\Admin_db;
use Illuminate\Support\Facades\Session;
use App\Traits\HelperTraits;
use App\Http\Controllers\db\admindb;
use PhpParser\Node\Stmt\Return_;
use App\Http\Controllers\LayoutController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LoginAnalysisController;

class AdminController extends Admin_db
{
    use HelperTraits;
    //登入頁面
    public function login_page(){
        //從 Session 獲取 employeeId 和 token
        $employeeId = Session::get('employeeId');
        $token = Session::get('token');
        return view('adminLogin.login');
    }
    //身份驗證
     public function admin_auth(Request $req){
        //驗證輸入

        if(isset($_POST['phone']) && isset($_POST['password'])){
            $inputPhone=$_POST['phone'];
            $password=$_POST['password'];
            if(isset($_POST['ip'])){
                $ip=$_POST['ip'];
            }
            //查詢資料庫中是否存在該使用者
            $res=$this->employee_detail($inputPhone);
            // return $res;
            if(count($res)>0){
                //密碼加密與比對
                $password= md5('gini'.$password);
                // return $password;
                $realPassword=$res[0]->password;
                if($realPassword==$password){
                    $current=date("Ymdhis");
                    $token=md5($current);
                    #$data['_token']=$token;
                    $this->update_token($res[0]->employeeId,$token);
                    $req->session()->put('token', $token);
                    $req->session()->put('employeeId', $res[0]->employeeId);
                    $req->session()->put('employeeName', $res[0]->name);
                    $req->session()->put('storeId', $res[0]->storeId);
                    // $req->session()->put('ip', "http://" . $ip . "/" );
                    
                    if(empty($ip) || $ip==''){
                        $req->session()->put('ip', "https://dali-mart.com/");
                    }else{
                        $req->session()->put('ip', "https://$ip/");
                    }

                    // 登入紀錄寫入
                    $loginAnalysis = new LoginAnalysisController();
                    $loginAnalysis->backStageloginTimeInsert();

                    

                    // $Layout = new LayoutController;
                    // $Layout->pages_db($res[0]->employeeId);
                    // $dadasdasd = session()->all();

                    // Cookie::queue(Cookie::make('_token',$token,45));
                    // Cookie::queue(Cookie::make('employeeId',$res[0]->employeeId,45));
                    //暫時把登入紀錄刪去
                    #$input['_token']=$token;
                    #$input['loginTime']=date('Y-m-d H:i:s');
                    #$input['employeeId']=$res[0]->employeeId;
                    #$this->add_employee_history($input);
                    // echo "window.API_URL = 'http: //" . $ip . "/'";
                   
                    
                   //$this->talk('',str_replace('http://','https://',route('Products')),2);
                   $this->talk('', str_replace('http://', 'https://', route('loginAnalysisIndex')), 2);
                    
                }else{
                   $this->talk('帳號或密碼有誤!',route('adminShowLogin'),3);
                }
            }else{
                $this->talk('使用者不存在!',route('adminShowLogin'),3);
            }
        }else{
            $this->talk('輸入格式有誤!',route('adminShowLogin'),3);
        }
    }

    public function logout()
    {
        // 登出紀錄寫入
        $loginAnalysis = new LoginAnalysisController();
        $loginAnalysis->backStageLogOutTimeInsert();


        Session::pull('employeeId', Session::get('employeeId'));
        Session::pull('token', Session::get('token'));
        $this->talk('',route('adminShowLogin'),2);
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
