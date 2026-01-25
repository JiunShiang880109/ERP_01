<?php

namespace App\Traits;
use Illuminate\Support\Facades\Cookie;
use App\libraries\clsLibGTIN;
use DB;
trait AccountTraits
{
    public function get_employee_history($_token){
        $results = DB::select("SELECT * FROM employee_history
		where _token='$_token'  ");
		return $results;
    }
    public function update_employee_logoutTime($logoutTime,$id){
        $results = DB::update("UPDATE employee_history
		SET  logoutTime='$logoutTime'
		WHERE  id='$id'");
    }
    public function update_employee_action($action,$id){
        $results = DB::update("UPDATE employee_history
		SET  action='$action'
		WHERE  id='$id'");
    }
    public function update_employee_tracking($tracking,$id){
        $results = DB::update("UPDATE employee_history
		SET  tracking='$tracking'
		WHERE  id='$id'");
    }
    public function get_employee_history_and_update_tracking($tracking){
        $_token = Cookie::get('_token');
        $result=$this->get_employee_history($_token);
        $original_tracking=$result[0]->tracking;
        $new_tracking=$original_tracking.'->'.$tracking;
        $this->update_employee_tracking($new_tracking,$result[0]->id);
    }
    public function currentEmployeeDetail($employeeId){
        $results = DB::select("SELECT A.*,B.id as currentStore FROM  employee A
        left join store B
        on A.storeId=B.id
		where A.employeeId='$employeeId'  ");
		return $results;
    }
}
