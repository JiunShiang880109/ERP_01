<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
class backcheckAuth
{

    public function handle(Request $request, Closure $next)
    {
        $employeeId = Session::get('employeeId');
        if(empty($employeeId)){
            echo "<script>location.href='".route('adminShowLogin')."';</script>";
            exit();
         }
        return $next($request);
       

     
    }
}
