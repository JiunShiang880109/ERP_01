<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function GetUnFinishOrder($storeId)
    {
        // return $storeId;
        $orders =  DB::select("SELECT * FROM orders
                    WHERE storeId = '$storeId'
                    AND TO_DAYS(orderTime) = TO_DAYS(NOW())
                    AND kitchenStatus = 0
                    ORDER BY orderTime ");
        foreach ($orders as $order) {
            $orderDetails = DB::select("SELECT * FROM order_detail
                                    WHERE orderNum = '{$order->orderNum}'");
                                      
            foreach ($orderDetails as $key => $detail) {
                $tempOptions =  DB::select("SELECT * FROM order_detail_option
                                    WHERE orderDetailId = '{$detail->orderDetailId}'");
                // return $tempOptions;

                $detail->options = $tempOptions;                                    
            }

            $order->orderDetail = $orderDetails;
        }

        return ['success' => true, 'orders' => $orders];
    }

    public function updateDetail(Request $req)
    {
        // return $req; 
        $orderNum = $req->orderNum;
        $orderDetailId = $req->orderDetailId;
        DB::table("order_detail")
            ->where([
                ["orderNum", $orderNum],
                ["orderDetailId", $orderDetailId]
            ])->update([
                "kitchenStatus" => 1
            ]);

        return ['success' => true, 'msg' => '品項狀態更新成功'];
    }

    public function updateOrder(Request $req)
    {
        // return $req;
        $orderNum = $req->orderNum;
        DB::table("orders")
            ->where("orderNum", $orderNum)
            ->update([
                "kitchenStatus" => 1
            ]);

        return ['success' => true, 'msg' => '訂單狀態更新成功'];
    }

    public function updateDetailCancel(Request $req)
    {
        $orderNum = $req->orderNum;
        $orderDetailId = $req->orderDetailId;
        DB::table("order_detail")
            ->where([
                ["orderNum", $orderNum],
                ["orderDetailId", $orderDetailId]
            ])->update([
                "kitchenStatus" => 0
            ]);

        return ['success' => true, 'msg' => '品項狀態更新成功'];
    }
}
