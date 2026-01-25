<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Layout_db extends Model
{
    // public function employee_detail($inputPhone){
    //     $results = DB::select("SELECT * FROM employee
    //     where isResign=0 and phone='$inputPhone'  ");
    //     return $results;
    // }
    // public function update_token($employeeId,$token)    {
    //     $results = DB::update("UPDATE employee
	// 	SET _token='$token'
	// 	WHERE employeeId='$employeeId'");

    // }
    // public function add_employee_history($data){
    //     DB::table('employee_history')->insert($data);
    // }

    // /****************抓該員工權限******************** */
    // public function pages_db($employeeId)
    // {
    //     // return '測試';
    //     return DB::select("SELECT d.id
    //                        FROM employee AS a
    //                        LEFT JOIN erp_options AS b
    //                        ON a.position = b.`value`
    //                        LEFT JOIN empjob_pages AS c
    //                        ON b.`value` = c.emp_jobs_value
    //                        LEFT JOIN pages AS d
    //                        ON c.pages_id = d.id
    //                        WHERE a.employeeId = '$employeeId'
    //                        GROUP BY id");
                        
    // }
    public function pages_mainPages_db($employeeId)
    {
        return DB::select("SELECT c.identifyId , c.mainPages , c.mainIcon
                           FROM employee AS a
                           LEFT JOIN empjob_pages AS b
                           ON a.position = b.emp_jobs_value
                           LEFT JOIN pages AS c
                           ON b.pages_id = c.id
                           WHERE a.employeeId = '$employeeId'
                           GROUP BY c.mainPages
                           ORDER BY c.id ASC");
    }  
    
    public function pages_all_db($employeeId,$identifyId)
    {
        return DB::select("SELECT c.* 
                           FROM employee AS a
                           LEFT JOIN empjob_pages AS b
                           ON a.position = b.emp_jobs_value
                           LEFT JOIN pages AS c
                           ON b.pages_id = c.id
                           WHERE a.employeeId = '$employeeId' AND identifyId = '$identifyId'
                           ORDER BY c.id ASC");
    }

}
