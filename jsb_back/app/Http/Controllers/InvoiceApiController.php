<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

class InvoiceApiController extends Controller
{
    public function recent_invoice(Request $request){
        $machineId = $request->machineCode;
        $storeId = $request->storeId;
        // return $request;
        $invoice_5 =  DB::select("SELECT orderNum , invoiceNumber , orderTime , finalPrice
                                  FROM orders
                                  WHERE TO_DAYS(orderTime) = TO_DAYS(NOW()) 
                                  AND machineId = '$machineId' AND storeId = '$storeId' 
                                  ORDER BY orderTime DESC
                                  limit 1");
        // return $invoice_5;
        $invoiceData = array();
        
        foreach ($invoice_5 as $data) {
            
            $orderNumber = $data->orderNum;
            $orderDetail = DB::select("SELECT * FROM orders
                                       LEFT JOIN order_detail
                                       ON orders.orderNum = order_detail.orderNum
                                       WHERE order_detail.orderNum = '$orderNumber'");
            foreach ($orderDetail as $detail){
                
                $orderDetailId = $detail->orderDetailId;
                // return $orderDetailId;
                $customOption = DB::select("SELECT order_detail_option.order_option_title,order_detail_option.count,order_detail_option.price FROM order_detail
                            LEFT JOIN order_detail_option
                            ON order_detail.orderDetailId = order_detail_option.orderDetailId
                            WHERE order_detail.orderDetailId = '$orderDetailId'");

                $detail->option = $customOption;

            }

            $invoice = [
                'order' => $data,
                'orderDetail' => $orderDetail,
            ];
            $invoiceData[] = $invoice;
        }
        return [
            'success' => true,
            'invocieData' => $invoiceData,
        ];

    }

    
}
