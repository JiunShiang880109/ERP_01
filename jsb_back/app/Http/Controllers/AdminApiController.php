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

class AdminApiController extends Admin_db
{
    use HelperTraits;
    //  public function login_page(){
    //     $employeeId = Session::get('employeeId');
    //     $token = Session::get('token');
    //     return view('adminLogin.login');
    // }
    //  public function admin_auth(Request $req){
    //     if(isset($_POST['phone']) && isset($_POST['password'])){
    //         $inputPhone=$_POST['phone'];
    //         $password=$_POST['password'];
    //         if(isset($_POST['ip'])){
    //             $ip=$_POST['ip'];
    //         }
    //         $res=$this->employee_detail($inputPhone);
    //         // return $res;
    //         if(count($res)>0){
    //             $password= md5('gini'.$password);
    //             // return $password;
    //             $realPassword=$res[0]->password;
    //             if($realPassword==$password){
    //                 $current=date("Ymdhis");
    //                 $token=md5($current);
    //                 #$data['_token']=$token;
    //                 $this->update_token($res[0]->employeeId,$token);
    //                 $req->session()->put('token', $token);
    //                 $req->session()->put('employeeId', $res[0]->employeeId);
    //                 // $req->session()->put('ip', "http://" . $ip . "/" );

    //                 if(empty($ip) || $ip==''){
    //                     $req->session()->put('ip', "https://dali-mart.com/");
    //                 }else{
    //                     $req->session()->put('ip', "http://$ip/");
    //                 }

    //                 // $Layout = new LayoutController;
    //                 // $Layout->pages_db($res[0]->employeeId);
    //                 // $dadasdasd = session()->all();

    //                 // Cookie::queue(Cookie::make('_token',$token,45));
    //                 // Cookie::queue(Cookie::make('employeeId',$res[0]->employeeId,45));
    //                 //暫時把登入紀錄刪去
    //                 #$input['_token']=$token;
    //                 #$input['loginTime']=date('Y-m-d H:i:s');
    //                 #$input['employeeId']=$res[0]->employeeId;
    //                 #$this->add_employee_history($input);
    //                 // echo "window.API_URL = 'http: //" . $ip . "/'";
    //                 $this->talk('',route('Products'),2);

    //             }else{
    //                $this->talk('帳號或密碼有誤!',route('adminShowLogin'),3);
    //             }
    //         }else{
    //             $this->talk('使用者不存在!',route('adminShowLogin'),3);
    //         }
    //     }else{
    //         $this->talk('輸入格式有誤!',route('adminShowLogin'),3);
    //     }
    // }

    // public function logout(){
    //     Session::pull('employeeId', Session::get('employeeId'));
    //     Session::pull('token', Session::get('token'));
    //     $this->talk('',route('adminShowLogin'),2);
    // }

    public function posLogin(Request $req)
    {
        // return $req;
        $phone = $req->phone;
        $password = $req->password;
        if (empty($phone) || empty($password)) {
            return ['success' => false, 'msg' => '登入資訊錯誤'];
        }
        $md5password = md5('gini' . $password);
        $employee = DB::select("SELECT * FROM employee
                    WHERE phone = '$phone'
                    AND password = '$md5password'");
        if (empty($employee)) {
            return ['success' => false, 'msg' => '登入資訊錯誤'];
        } else {
            $current = date("Ymdhis");
            $token = md5($current);
            DB::table("employee")
                ->where([
                    ['phone', $phone],
                    ['password', $md5password]
                ])
                ->update([
                    '_FronEndToken' => $token
                ]);

            return ['success' => true, 'msg' => '登入成功', 'token' => $token, 'storeId' => $employee[0]->storeId];
        }
    }

    public function posCheck(Request $req)
    {
        // return $req;
        $phone = $req->phone;
        $token = $req->token;
        $storeId = $req->storeId;
        if (empty($phone) || empty($token)|| empty($storeId)) {
            return ['success' => false, 'msg' => '驗證失敗'];
        }
        $employee = DB::select("SELECT * FROM employee
                    WHERE phone = '$phone'
                    AND _FronEndToken = '$token'
                    AND storeId = '$storeId'");
        if (empty($employee)) {
            return ['success' => false, 'msg' => '驗證失敗'];
        }else{
            return ['success' => true, 'msg' => '驗證成功'];
        }
    }
}
