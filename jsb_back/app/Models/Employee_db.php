<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
class Employee_db extends Model{

    use SoftDeletes;

    protected $table = 'employee';


    function __construct(){


    }
    public function employee_number_db(){
        $results = DB::select("SELECT employeeId
        FROM employee 
        order by id DESC LIMIT 1");
		return $results;
    }
    public function all_employee($storeId){
        $results = DB::select("SELECT *
        FROM employee
        WHERE storeId = ?
        ORDER BY isResign ASC",[$storeId]);
		return $results;
    }
    //查詢員工
    public function employee_detail($storeId,$employeeId){
        $results = DB::select("SELECT *
        FROM employee
		WHERE storeId = ? AND employeeId=?",[$storeId,$employeeId]);
		return $results;
    }




    public function add_employee_db($data) {
        DB::table('employee')->insert($data);

    }
    public function getEmployee($employeeId)
    {
        return DB::select("SELECT employeeId , name FROM employee WHERE employeeId = '$employeeId'");
    }

    public function getToken($employeeId)
    {
        return DB::select("SELECT _token FROM employee WHERE employeeId = '$employeeId'");
    }

    
 
    public function get_store(){
        $results = DB::select("SELECT * FROM store ");
		return $results;
    }
    public function update_isResign($employeeId,$isResign){
        DB::update("UPDATE employee
		SET isResign='$isResign'
		WHERE  employeeId='$employeeId'");
    }
    public function remove_employee($employeeId){
        DB::table('employee')->where('employeeId', $employeeId)->delete();
    }
    
    public function update_employee_details($employeeId,$data){
        DB::table('employee')->where('employeeId', $employeeId)->update($data);
    }

}
