<?php

namespace App\Traits;
use Illuminate\Support\Facades\Cookie;
use App\libraries\clsLibGTIN;
use DB;
trait ToolTraits
{
    public function get_employee_history($_token){
        $results = DB::select("SELECT * FROM employee_history
		where _token='$_token'  ");
		return $results;
    }
   
}
