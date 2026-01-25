<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\AccountTraits;
use PDO;

class AccountReportCtrl extends Controller
{
    public function trialBalanceIndex(){
        return view('accountReport.trialBalance');
    }
}
