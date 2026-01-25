<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrintController extends Controller
{
    // 測試
    public function getOrderData(Request $request)
    {

        $orderNumber['orderNumber'] = $request['orderNumber'];
        
        return $orderNumber;
    }
}
