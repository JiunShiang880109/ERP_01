<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Traits\InvoiceTraits; //發票
use App\Http\Controllers\db\Orderdb; //產發票
use App\Http\Controllers\LayoutController; //產MIG
use App\Traits\Generate_InvoiceTraits; //發票
use App\Traits\Generate_MIGTraits; //退貨
use PhpParser\Node\Expr\FuncCall;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Tool;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Traits\LinePayTraits; //Linepay


use App\Traits\SmilePayTraits; //發票

class OrderController extends Controller
{

    use InvoiceTraits,SmilePayTraits,LinePayTraits;

    //開錢櫃
    public function open_drawer()
    {
        if (env('INVOICE_SETTING_ENV') == 1) { //本地
            return  $this->open_cashDrawer();
        } else { //env('INVOICE_SETTING_ENV') == 2 wifi，固定用第一台開錢櫃
            $ip = env('INVOICE_SETTING_IP1');
            return  $this->open_cashDrawer($ip);
        }
    }



    // 結帳(有發票版)
    public function checkoutInvoice(Request $req)
    {
        $InvoiceController = new InvoiceController();
        $invoiceData = $InvoiceController->smilePayInvoice($req);

        $order['orderNum'] = '';
        if (!empty($req->orderNum)) {

            $order['orderNum'] = $req->orderNum;
        } else {
            /**********************訂單編號****************** */
            $x = 0;
            $y = 22;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $order['orderNum'] = substr(str_shuffle($Strings), $x, $y);
        }

        /**********************訂單建立時間**************************** */
        $order['orderTime'] = $invoiceData['InvoiceDate'] . ' ' . $invoiceData['InvoiceTime'];

        /***********************店別*********************/
        $order['storeId'] = $req->storeId;

        /**********************載具**************************** */
        $order['carrierId'] = $invoiceData['CarrierID'] ? $invoiceData['CarrierID'] : null;


        /*************************收銀員id******************** */
        // $data['casherId'] = $order['employeeId'];

        /***************************收銀機id************************** */
        $order['machineId'] = $req->MachineCode;

        /*************************店家名字******************************** */
        $order['sellName'] = $req->store['storeName'];

        /*************************店家統編******************************** */
        $order['sellTax'] = $req->store['taxNum'];

        /************************客戶統編***************************** */
        if (empty($req->buyTax)) {
            //無統編
            $order['buyTax'] = null; //統編
            $order['buyType'] = 1; //無統編->1 有統編->2
        } else {
            //有統編
            $order['buyTax'] = $req->buyTax; //統編
            $order['buyType'] = 2; //無統編->1 有統編->2
        }


        /***********************付款方式****************************** */
        $order['payMethod'] = $req->method; //1:現金 2:信用卡 3:禮券 4:轉帳 5:行動支付 6:會員儲值金 7:其他 8:會員點數折抵 9:台灣pay

        /***********************0:開立 1:作廢 2:註銷 3:折讓****************************** */
        $order['status'] = 0;

        /*************************07一般稅額, 08 特種稅額 會補零這裡不用給零**************************** */
        $order['invoiceType'] = 7;

        /*************************建立日期(date("Ymd"))**************************** */
        $order['invoiceDate'] = date("Ymd");

        /************************發票字軌(英文)***************************** */
        // $order['invoiceNumberEn'] = $order['receiptCode'];
        $invoiceNumber = $invoiceData['InvoiceNumber'];
        $invoiceNumberArray = preg_split('/(?<!^)(?!$)/u', "$invoiceNumber" );

        $order['invoiceNumberEn'] = $invoiceNumberArray[0] . $invoiceNumberArray[1];

        /***************************發票號碼(8位數)************************** */
        // $order['invoiceNumber'] = $order['receiptID'];
        $order['invoiceNumber'] = $invoiceNumberArray[2] . $invoiceNumberArray[3] . $invoiceNumberArray[4] . $invoiceNumberArray[5] . $invoiceNumberArray[6] . $invoiceNumberArray[7] . $invoiceNumberArray[8] . $invoiceNumberArray[9] ;

        /*************************發票的隨機碼**************************** */
        $x = 0;
        $y = 4;
        $Strings = '0123456789';
        $order['randomNum'] = $invoiceData['RandomNumber'];

        /**********************0 非捐贈發票, 1 捐贈發票******************************* */
        $order['donate'] = 0;

        /***********************課稅別: 1 應稅, 2　零稅率, 3 免稅****************************** */
        $order['taxType'] = 1;

        /****************************稅率(先固定0.05)************************* */
        $order['taxRate'] = 0.05;
        /*************************用餐方式**********************************/
        $useType = $req->SelectUsetype;
        $order['useType'] = $req->SelectUsetype;

        //用餐方式單號，每天重新計算，不同用餐方式分開
        $buyNumber =  DB::select("SELECT orders.buyNumber FROM orders
                    WHERE useType = '$useType'
                    AND TO_DAYS(orderTime) = TO_DAYS(NOW())
                    ORDER BY orderTime DESC
                    LIMIT 1");
        if (count($buyNumber) == 0) {
            $order['buyNumber'] = 1;
        } else {
            $order['buyNumber'] = $buyNumber[0]->buyNumber + 1;
        }

        /*****************************應稅銷售額合計(全部商品沒有稅加起來)************************ */
        $product_tax = $this->product_tax_caculate($req->orders, $req->discountVal);
        $finalSumNoTax = $product_tax['finalSumNoTax']; //未含稅總金額
        $finalSumWithTax = $product_tax['finalSumWithTax']; //含稅總金額

        //應稅銷售額合計(全部商品沒有稅加起來)
        $total = floor($finalSumWithTax / 1.05) + $finalSumNoTax;
        $order['salesAmount'] = $total;

        //稅額(有稅的商品加起來 + 沒稅的商品加起來 - 應稅銷售合計)
        $order['taxAmount'] = $finalSumWithTax + $finalSumNoTax - $total;

        //含稅總額(有稅的商品加起來)
        $order['totalAmount'] = $finalSumWithTax;

        //免稅銷售額合計(沒稅的商品加起來)
        $order['freeTaxSalesAmount'] = $finalSumNoTax;


        /****************************折抵金額(折扣總價)************************* */
        $order['totalDiscount'] = $req->discountVal;

        /**************************現金收入*************************** */
        $order['cashIncome'] = $req->cashIncome;

        /**************************現金找零*************************** */
        if ($order['payMethod'] == 1) {
            $order['cashChange'] = $req->cashChange;
        }else{
            $order['cashChange'] = 0;
        }

        /****************************最後要跟客人收的************************* */
        $order['finalPrice'] = $req->Total;



        /****************************準備累積的回饋點數(送使用者的點數)************************* */
        $Total_Feed_back = 0;

        // return $order;

        $order_detail = [];
        $order_option = [];
        foreach ($req->orders as $key => $orderInfo) {
            // return $orderInfo['count'];
            /***** 訂單細項id ***** */
            $x = 0;
            $y = 13;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orderDetailId = substr(str_shuffle($Strings), $x, $y);
            /********************* */

            /******** 計算小計******* */
            $subTotal = 0;
            $unitPrice = 0;
            foreach ($orderInfo['options'] as $key => $option) {
                // if ($option['countEnable'] == 1) {
                $subTotal +=  $option['price'];
                // }
                $order_option[] = [
                    // "orderNum"=>$order['orderNum'],
                    "orderDetailId" => $orderDetailId,
                    "order_option_title" => $option['custom_option_title'],
                    "count" => isset($option['count']) ? $option['count'] : 1,
                    "price" => $option['price'],
                    "custom_option_id" => $option['id'],
                ];
            }
            //客製化選項總額 ->$subTotal
            $unitPrice = $subTotal + $orderInfo['price']; //單價 = 客製化總額 + 一份商品

            $subTotal = $subTotal * $orderInfo['count'];
            $subTotal += $orderInfo['price'] * $orderInfo['count'];
            // return $subTotal;

            $order_detail[] = [
                "orderNum" => $order['orderNum'],
                "productId" => $orderInfo['productId'],
                "orderDetailId" => $orderDetailId,
                "productName" => $orderInfo['product_title'],
                "taxType" => 1,
                "unit" => $orderInfo['unit'],
                "quantity" => $orderInfo['count'],
                "unitPrice" => $unitPrice,
                "subtotal" => $subTotal,
            ];
            if (!empty($orderInfo['feedback_point'])) {
                $Total_Feed_back += $orderInfo['feedback_point'];
            }
        }

        if (!empty($req->MemberUserId) && !empty($req->MemberPoint)) {
            // 有扣點
            $order['carryPoint'] = $req->MemberPoint;
            $order['memberUserId'] =  $req->MemberUserId;
        }
        if (
            !empty($req->MemberUserId) && $Total_Feed_back > 0
        ) {
            $order['livePoint'] = $Total_Feed_back;
            $order['memberUserId'] =  $req->MemberUserId;
        }

        // return $order_option;
        DB::table('orders')->insert($order);
        DB::table('order_detail')->insert($order_detail);
        DB::table('order_detail_option')->insert($order_option);

        // *** 計算本筆訂單成本 & 扣庫存 ***
        $tool = new Tool();
        $tool->orderCostAndInventory($order['orderNum'], $order_detail);
        

        // return $order_detail;
        // return $order_option;

        if (!empty($req->orderNum)) {
            DB::table('order_temp')->where('orderNum', $req->orderNum)->delete();
        }



        /****************************會員點數折抵************************* */
        // 會員/扣點
        if (!empty($req->MemberUserId) && !empty($req->MemberPoint)) {
            $point['MemberUserId'] = $req->MemberUserId;
            $point['MemberPoint'] = $req->MemberPoint;
            $point['orderNum'] =  $order['orderNum'];
            $point['type'] = 'carry';
            $point['storeId'] = $req->storeId;
            $userId = $req->MemberUserId;
            $token = $req->MemberToken;
            $type = 'carry';
            $MemberPoint = (int)$req->MemberPoint; //折抵點數
            $result =   MemberController::PostMemberPoint($type, $userId, $token, $MemberPoint,$point['storeId']);
            $result = json_decode($result);
            if ($result->action == 'error') {
                DB::table('orders')->where('orderNum', $order['orderNum'])->update([
                    'carryPoint' => null
                ]);
                return ['success' => false, "msg" => '點數折抵失敗', 'errorLog' => $result->talk];
            } else {
                DB::table("member_point_history")->insert($point);
            }
        }

        if (!empty($req->MemberUserId) && $Total_Feed_back > 0) {
            $point['MemberUserId'] = $req->MemberUserId;
            $point['MemberPoint'] = $Total_Feed_back;
            $point['orderNum'] =  $order['orderNum'];
            $point['type'] = 'live';
            $point['storeId'] = $req->storeId;
            $userId = $req->MemberUserId;
            $token = $req->MemberToken;
            $type = 'live';
            $MemberPoint =  $Total_Feed_back; //回饋點數
            $result =   MemberController::PostMemberPoint($type, $userId, $token, $MemberPoint,$point['storeId']);
            $result = json_decode($result);
            if ($result->action == 'error') {
                DB::table('orders')->where('orderNum', $order['orderNum'])->update([
                    'livePoint' => null
                ]);
                return ['success' => false, "msg" => '點數累積失敗'];
            } else {
                DB::table("member_point_history")->insert($point);
            }
        }



        // 跟第三方發票取得發票資料所要的參數
        $smilePayUrl = "Grvc=" . env('GRVC')  . "&Verify_key=" . env('VERIFY_KEY') . "&InNumber=" . $invoiceData['InvoiceNumber'] . "&RaNumber=" . $invoiceData['RandomNumber'] . "&InDate=" . date("Y-m-d", strtotime($invoiceData['InvoiceDate'])) . ($invoiceData['CarrierID'] ? $invoiceData['CarrierID'] : null);
        // return $smilePayUrl;
        return [
            'success' => true,
            'orderNum' => $order['orderNum'],
            'smilePayUrl' => $smilePayUrl,
        ];
    }
    function checkOrderNumbers(Request $req){

        $OrderNum = $req->OrderNum;
        

        $data['orders'] = DB::select("SELECT a.useType,a.seatId,a.buyNumber,a.finalPrice,b.productName,b.unitPrice,b.quantity,b.subtotal,b.orderDetailId
        FROM orders as a,
        order_detail as b
        WHERE a.orderNum=b.orderNum 
        AND a.orderNum = '$OrderNum'");
        $data['store'] = DB::table('store')->first();
        foreach ($data['orders'] as $value){
            $orderDetailId = $value->orderDetailId;
            $option = DB::select("SELECT order_option_title,price
            FROM order_detail_option
            WHERE orderDetailId = '$orderDetailId'");
            $value->order_option = $option;

        }
        echo json_encode($data);
    }
    // 結帳(無發票版)
    public function checkoutWithoutInvoice(Request $req)
    {
        $order['orderNum'] = '';
        if (!empty($req->orderNum)) {

            $order['orderNum'] = $req->orderNum;
        } else {
            /**********************訂單編號****************** */
            $x = 0;
            $y = 22;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $order['orderNum'] = substr(str_shuffle($Strings), $x, $y);
        }

        /**********************訂單建立時間**************************** */
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $order['orderTime'] = $date . ' ' . $time;

        /***********************店別*********************/
        $order['storeId'] = $req->storeId;

        /**********************載具**************************** */
        $order['carrierId'] = null;

        /*************************收銀員id******************** */
        // $data['casherId'] = $order['employeeId'];

        /***************************收銀機id************************** */
        $order['machineId'] = $req->MachineCode;

        /*************************店家名字******************************** */
        $order['sellName'] = $req->store['storeName'];

        /*************************店家統編******************************** */
        $order['sellTax'] = $req->store['taxNum'];

        /************************客戶統編***************************** */
        if (empty($req->buyTax)) {
            //無統編
            $order['buyTax'] = null; //統編
            $order['buyType'] = 1; //無統編->1 有統編->2
        } else {
            //有統編
            $order['buyTax'] = $req->buyTax; //統編
            $order['buyType'] = 2; //無統編->1 有統編->2
        }


        /***********************付款方式****************************** */
        $order['payMethod'] = $req->method; //1:現金 2:信用卡 3:禮券 4:轉帳 5:行動支付 6:會員儲值金 7:其他 8:會員點數折抵 9:台灣pay

        /***********************0:開立 1:作廢 2:註銷 3:折讓****************************** */
        $order['status'] = 0;

        /*************************07一般稅額, 08 特種稅額 會補零這裡不用給零**************************** */
        $order['invoiceType'] = 7;

        /*************************建立日期(date("Ymd"))**************************** */
        $order['invoiceDate'] = date("Ymd");

        /************************發票字軌(英文)***************************** */

        $order['invoiceNumberEn'] = null;

        /***************************發票號碼(8位數)************************** */
        $order['invoiceNumber'] = null;

        /*************************發票的隨機碼**************************** */
        $order['randomNum'] = null;

        /**********************0 非捐贈發票, 1 捐贈發票******************************* */
        $order['donate'] = 0;

        /***********************課稅別: 1 應稅, 2　零稅率, 3 免稅****************************** */
        $order['taxType'] = 1;

        /****************************稅率(先固定0.05)************************* */
        $order['taxRate'] = 0.05;
        /*************************用餐方式**********************************/
        $useType = $req->SelectUsetype;
        $order['useType'] = $req->SelectUsetype;
        $order['seatId'] = $req->seatId;
        //用餐方式單號，每天重新計算，不同用餐方式分開
        $buyNumber =  DB::select("SELECT orders.buyNumber FROM orders
                    WHERE useType = '$useType'
                    AND TO_DAYS(orderTime) = TO_DAYS(NOW())
                    ORDER BY orderTime DESC
                    LIMIT 1");
        if (count($buyNumber) == 0) {
            $order['buyNumber'] = 1;
        } else {
            $order['buyNumber'] = $buyNumber[0]->buyNumber + 1;
        }

        /*****************************應稅銷售額合計(全部商品沒有稅加起來)************************ */
        $product_tax = $this->product_tax_caculate($req->orders, $req->discountVal);
        $finalSumNoTax = $product_tax['finalSumNoTax']; //未含稅總金額
        $finalSumWithTax = $product_tax['finalSumWithTax']; //含稅總金額

        //應稅銷售額合計(全部商品沒有稅加起來)
        $total = floor($finalSumWithTax / 1.05) + $finalSumNoTax;
        $order['salesAmount'] = $total;

        //稅額(有稅的商品加起來 + 沒稅的商品加起來 - 應稅銷售合計)
        $order['taxAmount'] = $finalSumWithTax + $finalSumNoTax - $total;

        //含稅總額(有稅的商品加起來)
        $order['totalAmount'] = $finalSumWithTax;

        //免稅銷售額合計(沒稅的商品加起來)
        $order['freeTaxSalesAmount'] = $finalSumNoTax;


        /****************************折抵金額(折扣總價)************************* */
        $order['totalDiscount'] = $req->discountVal;

        /**************************現金收入*************************** */
        $order['cashIncome'] = $req->cashIncome;

        /**************************現金找零*************************** */
        if ($order['payMethod'] == 1) {
            $order['cashChange'] = $req->cashChange;
        }else{
            $order['cashChange'] = 0;
        }

        /****************************最後要跟客人收的************************* */
        $order['finalPrice'] = $req->Total;



        /****************************準備累積的回饋點數(送使用者的點數)************************* */
        $Total_Feed_back = 0;

        // return $order;

        $order_detail = [];
        $order_option = [];
        foreach ($req->orders as $key => $orderInfo) {
            // return $orderInfo['count'];
            /***** 訂單細項id ***** */
            $x = 0;
            $y = 13;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orderDetailId = substr(str_shuffle($Strings), $x, $y);
            /********************* */

            /******** 計算小計******* */
            $subTotal = 0;
            $unitPrice = 0;
            foreach ($orderInfo['options'] as $key => $option) {
                // if ($option['countEnable'] == 1) {
                $subTotal +=  $option['price'];
                // }
                $order_option[] = [
                    // "orderNum"=>$order['orderNum'],
                    "orderDetailId" => $orderDetailId,
                    "order_option_title" => $option['custom_option_title'],
                    "count" => isset($option['count']) ? $option['count'] : 1,
                    "price" => $option['price'],
                    "custom_option_id" => $option['id'],
                ];
            }
            //客製化選項總額 ->$subTotal
            $unitPrice = $subTotal + $orderInfo['price']; //單價 = 客製化總額 + 一份商品

            $subTotal = $subTotal * $orderInfo['count'];
            $subTotal += $orderInfo['price'] * $orderInfo['count'];
            // return $subTotal;

            $order_detail[] = [
                "orderNum" => $order['orderNum'],
                "productId" => $orderInfo['productId'],
                "orderDetailId" => $orderDetailId,
                "productName" => $orderInfo['product_title'],
                "taxType" => 1,
                "unit" => $orderInfo['unit'],
                "quantity" => $orderInfo['count'],
                "unitPrice" => $unitPrice,
                "subtotal" => $subTotal,
            ];
            if (!empty($orderInfo['feedback_point'])) {
                $Total_Feed_back += $orderInfo['feedback_point'];
            }
        }

        if (!empty($req->MemberUserId) && !empty($req->MemberPoint)) {
            // 有扣點
            $order['carryPoint'] = $req->MemberPoint;
            $order['memberUserId'] =  $req->MemberUserId;
        }
        if (
            !empty($req->MemberUserId) && $Total_Feed_back > 0
        ) {
            $order['livePoint'] = $Total_Feed_back;
            $order['memberUserId'] =  $req->MemberUserId;
        }

        // return $order_option;
        DB::table('orders')->insert($order);
        DB::table('order_detail')->insert($order_detail);
        DB::table('order_detail_option')->insert($order_option);

        // *** 計算本筆訂單成本 & 扣庫存 ***
        $tool = new Tool();
        $tool->orderCostAndInventory($order['orderNum'], $order_detail);

        // return $order_detail;
        // return $order_option;

        if (!empty($req->orderNum)) {
            DB::table('order_temp')->where('orderNum', $req->orderNum)->delete();
        }



        /****************************會員點數折抵************************* */
        // 會員/扣點
        if (!empty($req->MemberUserId) && !empty($req->MemberPoint)) {
            $point['MemberUserId'] = $req->MemberUserId;
            $point['MemberPoint'] = $req->MemberPoint;
            $point['orderNum'] =  $order['orderNum'];
            $point['type'] = 'carry';
            $point['storeId'] = $req->storeId;
            $userId = $req->MemberUserId;
            $token = $req->MemberToken;
            $type = 'carry';
            $MemberPoint = (int)$req->MemberPoint; //折抵點數
            $result =   MemberController::PostMemberPoint($type, $userId, $token, $MemberPoint,$point['storeId']);
            $result = json_decode($result);
            if ($result->action == 'error') {
                DB::table('orders')->where('orderNum', $order['orderNum'])->update([
                    'carryPoint' => null
                ]);
                return ['success' => false, "msg" => '點數折抵失敗', 'errorLog' => $result->talk];
            } else {
                DB::table("member_point_history")->insert($point);
            }
        }

        if (!empty($req->MemberUserId) && $Total_Feed_back > 0) {
            $point['MemberUserId'] = $req->MemberUserId;
            $point['MemberPoint'] = $Total_Feed_back;
            $point['orderNum'] =  $order['orderNum'];
            $point['type'] = 'live';
            $point['storeId'] = $req->storeId;
            $userId = $req->MemberUserId;
            $token = $req->MemberToken;
            $type = 'live';
            $MemberPoint =  $Total_Feed_back; //回饋點數
            $result =   MemberController::PostMemberPoint($type, $userId, $token, $MemberPoint,$point['storeId']);
            $result = json_decode($result);
            if ($result->action == 'error') {
                DB::table('orders')->where('orderNum', $order['orderNum'])->update([
                    'livePoint' => null
                ]);
                return ['success' => false, "msg" => '點數累積失敗'];
            } else {
                DB::table("member_point_history")->insert($point);
            }
        }

        $detailUrl = $this->detailUrl($req , $date , $time);

        return [
            'success' => true,
            'orderNum' => $order['orderNum'],
            'detailUrl' => $detailUrl,
        ];
    }

    // detailUrl
    public function detailUrl($order , $date , $time)
    {
        $totalAmount = 'TotalAmount=' . (int)$order['Total'] . '&';
        $dateTime = 'date=' . $date . '&' .'time=' . $time . '&';

        $orderDetail = '';

        $productDetail = '';
        foreach($order['orders'] as $product){
            $productName = $product['product_title'];
            $quantity = $product['count'];
            $unitPrice = (int)$product['price'];
            $amount = $product['count'] * $product['price'];
            $productDetail = $productDetail . $productName . '|' . $quantity . '|' . $unitPrice . '|' . $amount . '&';
        }

        $orderDetail = $totalAmount . $dateTime . rtrim($productDetail,'&');

        return $orderDetail;
    }





    // 用orderNumber取得打第三方發票的參數網址
    public function getSmilePayUrl($orderNumber)
    {

        $smilePayUrl = $this->smilePayUrl($orderNumber);

        return $smilePayUrl;
    }

    // 補開發票產生新的開發票的網址
    public function smilePayUrl($orderNumber)
    {
        // 撈舊的資料
        $order = $this->select_orders_db($orderNumber);
        $orderDetailDatas = $this->select_orders_detail_db($orderNumber);

        // 使用者參數
        $Grvc = 'Grvc=' . env('GRVC');
        $Verify_key = 'Verify_key=' . env('VERIFY_KEY');

        // 發票資訊
        $timestamp = strtotime($order[0]->orderTime);
        $date = date("Y/m/d", $timestamp );
        $time = date("H:i:s", $timestamp );
        $InvoiceDate = "InvoiceDate=" . $date;
        $InvoiceTime = "InvoiceTime=" . $time;
        $Intype = "Intype=07";
        $TaxType = "TaxType=" . 1;
        $DonateMark = "DonateMark=" . 0;

        // 商品細項資訊
        $Description = null;
        $Quantity = null;
        $UnitPrice = null;
        $Amount = null;
        $AllAmount = null;

        // 開發票要得訂單明細陣列處理
        foreach($orderDetailDatas as $product){
            $Description = $Description . $product->productName . '|';
            $Quantity = $Quantity . $product->quantity . '|';
            $UnitPrice =  $UnitPrice . (int)$product->unitPrice . '|';
            $price = $product->quantity * $product->unitPrice;
            $Amount = $Amount . $price . '|';
            $AllAmount += $price;
        }

        $Description = "Description=" . rtrim($Description , '|');
        $Quantity = "Quantity=" . rtrim($Quantity , '|');
        $UnitPrice = "UnitPrice=" . rtrim($UnitPrice , '|');
        $Amount = "Amount=" . rtrim($Amount , '|');
        $AllAmount = "ALLAmount=" . $AllAmount;

        // 買受人資訊
        $Buyer_id = null;
        if(!empty($order[0]->buyTax)){
            $Buyer_id = "&Buyer_id=" . $order[0]->buyTax;
        }

        $smilePayUrl = env('SMILEPAY_URL') . $Grvc . "&" . $Verify_key . "&" . $Intype . "&" . $TaxType . "&" . $DonateMark . "&" . $Description . "&" . $Quantity . "&" . $UnitPrice . "&" . $Amount . "&" . $AllAmount . "&" . $InvoiceDate . "&" . $InvoiceTime . $Buyer_id;
        return $smilePayUrl;
    }

    // 抓舊的訂單資料
    public static function select_orders_db($orderNum)
    {
        return DB::select("SELECT *
                           FROM orders
                           WHERE orderNum = '$orderNum'");
    }

    // 抓舊的訂單細項資料
    public static function select_orders_detail_db($orderNum)
    {
        return DB::select("SELECT *
                           FROM order_detail
                           WHERE orderNum = '$orderNum'");
    }


    // 用orderNumber取得列印明細資料
    public function getPrintInvoiceUrl(Request $request)
    {
        $orderNumber = $request->orderNumber;

        $order = DB::select("SELECT * FROM orders WHERE orderNum = '$orderNumber'");
        // 跟第三方發票取得發票資料所要的參數
        $smilePayUrl = "Grvc=" . env('GRVC')  . "&Verify_key=" . env('VERIFY_KEY') . "&InNumber=" . $order[0]->invoiceNumberEn . $order[0]->invoiceNumber . "&RaNumber=" . $order[0]->randomNum . "&InDate=" . date("Y-m-d", strtotime($order[0]->invoiceDate)) . ($order[0]->carrierId ? $order[0]->carrierId : null);

        return $smilePayUrl;
    }

    // 用orderNumber取得列印明細資料
    public function getPrintDetailUrl(Request $request)
    {
        $orderNumber = $request->orderNumber;

        $order = DB::select("SELECT * FROM orders WHERE orderNum = '$orderNumber'");
        $orderDetails = DB::select("SELECT * FROM order_detail WHERE orderNum = '$orderNumber'");

        $totalAmount = 'TotalAmount=' . (int)$order[0]->finalPrice . '&';
        $timestamp = strtotime($order[0]->orderTime);
        $date = date("Y-m-d", $timestamp );
        $time = date("H:i:s", $timestamp );
        $dateTime = 'date=' . $date . '&' .'time=' . $time . '&';

        $productDetail = '';
        foreach($orderDetails as $product){
            $productName = $product->productName;
            $quantity = $product->quantity;
            $unitPrice = (int)$product->unitPrice;
            $amount = $quantity * $unitPrice;
            $productDetail = $productDetail . $productName . '|' . $quantity . '|' . $unitPrice . '|' . $amount . '&';
        }

        // 列印明細所要的參數
        $printDetailUrl = $totalAmount . $dateTime . rtrim($productDetail,'&');

        return $printDetailUrl;
    }


































    //算商品應稅銷售額合計(輪子)
    public function product_tax_caculate($product,$discountCard)
    {
        $sumWithTax = 0; //含稅總額初始值
        $sumnotax = 0; //未含稅總額初始值
        $discountCard = $discountCard; //整筆訂單折扣總額初始值，外部帶入
        foreach($product as $product){
            if($product['taxType'] == 1){   //要稅的加起來
                $sell = $product['price'];//全部優惠完總價
                $sumWithTax += $sell;
            }
            if($product['taxType'] == 0){   //不要稅的加起來
                $sell = $product['sell'];//全部優惠完總價
                $sumnotax += $sell;
            }
        }
        $finalSumWithTax = $sumWithTax - $discountCard; //含稅的商品總額

        //折扣金額先把含稅部分減完，再減為含稅部分
        if ($finalSumWithTax >= 0) { //含稅已將折扣減完不剩，所以aa不需要再減了
            $aa = 0; //尚須減的折扣金額
        } else {
            //aa為負值
            $aa = $finalSumWithTax;
            $finalSumWithTax = 0;
        }
        $finalSumNoTax = $sumnotax + $aa; //如果要稅的商品扣完換貨金額還有剩，再去把不用稅的錢扣掉

        return [
            'finalSumWithTax' => $finalSumWithTax, //含稅總金額
            'finalSumNoTax' => $finalSumNoTax, //未含稅總金額
        ];
    }


    // linepay結帳
    public function tempOrderLinePay(Request $req)
    {
        // return $req;
        $orders = $req->orders;
        $confirmUrl = $req->confirmUrl;
        $orderNum = $req->orderNum;

        /**********************訂單三位隨機碼**************************** */
        $orderCheck =  mt_rand(100, 999);

        /**********************訂單建立時間**************************** */
        $orderTime = date("Y-m-d H:i:s");

        /***********************店別*********************/
        $storeId = $req->storeId;

        /***********************訂單備註*********************/
        $remark = $req->remark;

        /***********************桌號*********************/
        $seatId = $req->seatId;


        //要存入資料庫的陣列
        $orderTemp = [];
        $orderTempOptions = [];
        foreach ($orders as $key => $order) {
            $temp = [];
            $optionId = date("His") . mt_rand(100, 999);
            $productId = $order['productId'];
            $productCount = $order['count'];
            $optionId = $optionId;
            foreach ($order['options'] as $option) {
                $tempOption['order_option_title'] = $option['custom_option_title'];
                $tempOption['count'] = isset($option['count']) ? $option['count'] : 1;
                $tempOption['price'] = (float)$option['price'];
                $tempOption['order_temp_optionId'] = $optionId;
                $tempOption['custom_option_id'] = $option['id'];
                $orderTempOptions[] = $tempOption;
            }
            $temp['productId'] = $productId;
            $temp['quantity'] = $productCount;
            $temp['optionId'] = $optionId;
            $temp['orderNum'] = $orderNum;
            $temp['orderCheck'] = $orderCheck;
            $temp['orderTime'] = $orderTime;
            $temp['remark'] = $remark;
            $temp['storeId'] = $storeId;
            $temp['seatId'] = $seatId;
            $temp['byCash'] = 0;
            $orderTemp[] =  $temp;
        }

        DB::table('order_temp')->insert($orderTemp);
        DB::table('order_temp_option')->insert($orderTempOptions);




        /**********************訂單編號****************** */
        // $x = 0;
        // $y = 22;
        // $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $orderNum = substr(str_shuffle($Strings), $x, $y);

        $store = DB::table('store')
            ->where('storeId',$storeId)
            ->first();
        if(empty($store)){
            return ['success'=>false,'msg'=>'storeId錯誤'];
        }

        $orderList = [];
        foreach ($orders as $key => $order) {
            $orderList[] = [
                'product_title' => $order['product_title'],
                'count' => $order['count'],
                'price' => $order['price'],
            ];
            if(!empty($order['options'])){
                foreach ($order['options'] as $k => $option) {
                    if( (float)$option['price'] > 0){
                        $orderList[] =[
                            'product_title' => $option['custom_option_title'],
                            'count' => $option['count'],
                            'price' => $option['price'],
                        ];
                    }
                }
            }
        }
        // return $orderList;
        return $this->Linepay_online_submitorder($store->storeName,$orderNum,$orderList,$confirmUrl,"");

        // return ['success'=>true,'num'=>$orderNum];
    }

    // linepay收款
    public function tempOrderLinePayConfirmUrl(Request $req)
    {
        $transactionId = $req->transactionId;
        $orderId = $req->orderId;

        $total = $this->OrderFinalPrice($orderId);

        if($total > 0 ){

            $output = $this->Linepay_online_Confirm($transactionId, $total);
            $data = json_decode($output,true);
            if($data['returnCode'] == '0000'){
                // return '請款成功';
                $store = DB::select("SELECT B.* FROM order_temp AS A
                            LEFT JOIN store AS B
                            ON A.storeId = B.storeId
                            WHERE orderNum = '$orderId'");
                $InvoiceMode = $store[0]->InvoiceMode;

                if($InvoiceMode == 1){
                    // Linepay有發票版本
                    $this->LinePayCheckoutReceipt($orderId);
                }else{
                    // Linepay無發票只印明細版本
                    $this->LinePayCheckoutWithoutReceipt($orderId);
                }

                // 刪除在暫存的訂單
                DB::table("order_temp")->where("orderNum",$orderId)->delete();

                return ['success'=>true,'msg'=>'付款成功','orderId'=>$orderId];

            }else{
                return ['success'=>false,'msg'=>'付款失敗','returnCode'=>$data['returnCode']];
            }

        }else{
            return ['success'=>false,'msg'=>'訂單單號回傳錯誤'];
        }


    }

    // Linepay開發票
    public function LinePayCheckoutReceipt($orderId)
    {


        $order['orderNum'] = $orderId;

        //     /**********************訂單建立時間**************************** */
        $order['orderTime'] = date('Y-m-d H:i:s');

        /*************************店家名字******************************** */
        $order['machineId'] = 0; //手機點餐付款機號

        $store = DB::select("SELECT B.* FROM order_temp AS A
                             LEFT JOIN store AS B
                             ON A.storeId = B.storeId
                             Where A.orderNum = '$orderId'");

        $order['storeId'] =$store[0]->storeId;

        //     /*************************店家名字******************************** */
        $order['sellName'] =$store[0]->storeName;
        //     /*************************店家統編******************************** */
        $order['sellTax'] = $store[0]->taxNum;


        //     /***********************付款方式****************************** */
        $order['payMethod'] = 5; //1:現金 2:信用卡 3:禮券 4:轉帳 5:行動支付 6:會員儲值金 7:其他 8:會員點數折抵 9:台灣pay

        /***********************0:開立 1:作廢 2:註銷 3:折讓****************************** */
        $order['status'] = 0;

        /*************************07一般稅額, 08 特種稅額 會補零這裡不用給零**************************** */
        $order['invoiceType'] = 7;

        /*************************建立日期(date("Ymd"))**************************** */
        $order['invoiceDate'] = date("Ymd");


        /**********************0 非捐贈發票, 1 捐贈發票******************************* */
        $order['donate'] = 0;

        /***********************課稅別: 1 應稅, 2　零稅率, 3 免稅****************************** */
        $order['taxType'] = 1;

        /****************************稅率(先固定0.05)************************* */
        $order['taxRate'] = 0.05;

        /*************************用餐方式**********************************/
        $useType = 2; //固定內用
        $order['useType'] = 2; //固定內用

        //     /*************************LinePay預設須為未開啟發票狀態******************************** */
        $order['billStatus'] = 0;

        //用餐方式單號，每天重新計算，不同用餐方式分開
        $buyNumber =  DB::select("SELECT orders.buyNumber FROM orders
                    WHERE useType = '$useType'
                    AND TO_DAYS(orderTime) = TO_DAYS(NOW())
                    ORDER BY orderTime DESC
                    LIMIT 1");
        if (count($buyNumber) == 0) {
            $order['buyNumber'] = 1;
        } else {
            $order['buyNumber'] = $buyNumber[0]->buyNumber + 1;
        }


        //     /*****************************應稅銷售額合計(全部商品沒有稅加起來)************************ */
        $product_tax = $this->product_tax_caculate_ByOrderNum($orderId);
        $finalSumNoTax = $product_tax['finalSumNoTax']; //未含稅總金額
        $finalSumWithTax = $product_tax['finalSumWithTax']; //含稅總金額

        //     //應稅銷售額合計(全部商品沒有稅加起來)
        $total = floor($finalSumWithTax / 1.05) + $finalSumNoTax;
        $order['salesAmount'] = $total;

        // //稅額(有稅的商品加起來 + 沒稅的商品加起來 - 應稅銷售合計)
        $order['taxAmount'] = $finalSumWithTax + $finalSumNoTax - $total;

        // //含稅總額(有稅的商品加起來)
        $order['totalAmount'] = $finalSumWithTax;

        // //免稅銷售額合計(沒稅的商品加起來)
        $order['freeTaxSalesAmount'] = $finalSumNoTax;

        //     /****************************折抵金額(折扣總價)************************* */
        $order['totalDiscount'] = 0; //LinePay不折抵

        //     /**************************現金收入*************************** */
        $order['cashIncome'] = $this->OrderFinalPrice($orderId);

        //     /**************************現金找零*************************** */
        $order['cashChange'] = 0; //LinePay不找零

        //     /****************************最後要跟客人收的************************* */
        $order['finalPrice'] = $this->OrderFinalPrice($orderId);


        $orderProducts = DB::select("SELECT * FROM order_temp
                                     WHERE orderNum = '$orderId'");

        //     /****************************內用需要存桌號************************* */
        $order['seatId'] = $orderProducts[0]->seatId ;

        $order_detail = [];
        $order_option = [];
        foreach($orderProducts as $key => $product){

            /***** 訂單細項id ***** */
            $x = 0;
            $y = 13;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orderDetailId = substr(str_shuffle($Strings), $x, $y);

            $details =  DB::select("SELECT A.orderNum,A.quantity , B.count, B.order_option_title,B.custom_option_id, C.product_title,C.unit ,C.price AS productPrice, D.price AS optionPrice  FROM order_temp AS A
                        LEFT JOIN order_temp_option AS B
                        ON A.optionId = B.order_temp_optionId
                        LEFT JOIN products AS C
                        ON A.productId = C.productId
                        LEFT JOIN custom_option AS D
                        ON B.custom_option_id = D.id
                        WHERE A.orderNum = '$orderId'
                        AND A.productId = '{$product->productId}'
                        AND A.optionId = '{$product->optionId}'
                        ");
            $unitPrice = 0;
            foreach ($details as $key2 => $detail) {
                $unitPrice += $detail->optionPrice;

                if(!empty( $detail->custom_option_id)){

                    $order_option[] = [
                        // "orderNum"=>$order['orderNum'],
                        "orderDetailId" => $orderDetailId,
                        "order_option_title" => $detail->order_option_title,
                        "count" => $detail->count,
                        "price" => $detail->optionPrice,
                        "custom_option_id" => $detail->custom_option_id,
                    ];
                }


            }

            $unitPrice += $details[0]->productPrice;
            $productName = $details[0]->product_title;
            $unit = $details[0]->unit;
            $quantity = $details[0]->quantity;

            $order_detail[] = [
                "orderNum" => $order['orderNum'],
                "productId" => $product->productId,
                "orderDetailId" => $orderDetailId,
                "productName" => $productName,
                "taxType" => 1,
                "unit" => $unit,
                "quantity" => $quantity,
                "unitPrice" => $unitPrice,
                "subtotal" => $unitPrice * $quantity,
            ];

        }

        DB::table('orders')->insert($order);
        DB::table('order_detail')->insert($order_detail);
        DB::table('order_detail_option')->insert($order_option);

        // *** 計算本筆訂單成本 & 扣庫存 ***
        $tool = new Tool();
        $tool->orderCostAndInventory($order['orderNum'], $order_detail);

        $smilePayUrl =  $this->getSmilePayUrl($orderId);

        $client = new Client(); //初始化客戶端
        $response = $client->get($smilePayUrl);
        $body = $response->getBody(); //獲取響應體，物件

        $xml = simplexml_load_string($body);
        $invoiceData = json_decode(json_encode($xml),true);

        /**********************訂單建立時間**************************** */
        $orderInvoice['orderTime'] = $invoiceData['InvoiceDate'] . ' ' . $invoiceData['InvoiceTime'];

        /**********************載具**************************** */
        $orderInvoice['carrierId'] = $invoiceData['CarrierID'] ? $invoiceData['CarrierID'] : null;

        /************************發票字軌(英文)***************************** */
        // $order['invoiceNumberEn'] = $order['receiptCode'];
        $invoiceNumber = $invoiceData['InvoiceNumber'];
        $invoiceNumberArray = preg_split('/(?<!^)(?!$)/u', "$invoiceNumber" );

        $orderInvoice['invoiceNumberEn'] = $invoiceNumberArray[0] . $invoiceNumberArray[1];

        /***************************發票號碼(8位數)************************** */
        // $order['invoiceNumber'] = $order['receiptID'];
        $orderInvoice['invoiceNumber'] = $invoiceNumberArray[2] . $invoiceNumberArray[3] . $invoiceNumberArray[4] . $invoiceNumberArray[5] . $invoiceNumberArray[6] . $invoiceNumberArray[7] . $invoiceNumberArray[8] . $invoiceNumberArray[9] ;

        /*************************發票的隨機碼**************************** */
        $x = 0;
        $y = 4;
        $Strings = '0123456789';
        $orderInvoice['randomNum'] = $invoiceData['RandomNumber'];

        DB::table("orders")
        ->where("orderNum",$orderId)
        ->update($orderInvoice);

    }

    // Linepay沒發票
    public function LinePayCheckoutWithoutReceipt($orderId)
    {
        $order['orderNum'] = $orderId;
        //     /**********************訂單建立時間**************************** */
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $order['orderTime'] = $date . ' ' . $time;

        //     /**********************載具**************************** */
        $order['carrierId'] = null;


        $store = DB::select("SELECT B.* FROM order_temp AS A
                             LEFT JOIN store AS B
                             ON A.storeId = B.storeId
                             Where A.orderNum = '$orderId'");

        $order['storeId'] =$store[0]->storeId;

        //     /*************************店家名字******************************** */
        $order['sellName'] =$store[0]->storeName;
        //     /*************************店家統編******************************** */
        $order['sellTax'] = $store[0]->taxNum;

        //     /*************************LinePay預設須為未開啟發票狀態******************************** */
        $order['billStatus'] = 0;


        //     /***********************付款方式****************************** */
        $order['payMethod'] = 5; //1:現金 2:信用卡 3:禮券 4:轉帳 5:行動支付 6:會員儲值金 7:其他 8:會員點數折抵 9:台灣pay

        //     /***********************0:開立 1:作廢 2:註銷 3:折讓****************************** */
        $order['status'] = 0;

        //     /*************************07一般稅額, 08 特種稅額 會補零這裡不用給零**************************** */
        $order['invoiceType'] = 7;

        //     /*************************建立日期(date("Ymd"))**************************** */
        $order['invoiceDate'] = date("Ymd");

        //     /************************發票字軌(英文)***************************** */
        $order['invoiceNumberEn'] = null;

        //     /***************************發票號碼(8位數)************************** */
        $order['invoiceNumber'] = null;

        //     /*************************發票的隨機碼**************************** */
        $order['randomNum'] = null;

        //     /**********************0 非捐贈發票, 1 捐贈發票******************************* */
        $order['donate'] = 0;

        //     /***********************課稅別: 1 應稅, 2　零稅率, 3 免稅****************************** */
        $order['taxType'] = 1;

        //     /****************************稅率(先固定0.05)************************* */
        $order['taxRate'] = 0.05;

        //     /*************************用餐方式**********************************/
        $useType = 2; //固定內用
        $order['useType'] = 2; //固定內用

        // /***************************收銀機id************************** */
        $order['machineId'] = 0; //手機點餐付款機號


        //用餐方式單號，每天重新計算，不同用餐方式分開
        $buyNumber =  DB::select("SELECT orders.buyNumber FROM orders
                                  WHERE useType = '$useType'
                                  AND TO_DAYS(orderTime) = TO_DAYS(NOW())
                                  ORDER BY orderTime DESC
                                  LIMIT 1");
        if (count($buyNumber) == 0) {
            $order['buyNumber'] = 1;
        } else {
            $order['buyNumber'] = $buyNumber[0]->buyNumber + 1;
        }

        //     /*****************************應稅銷售額合計(全部商品沒有稅加起來)************************ */
        $product_tax = $this->product_tax_caculate_ByOrderNum($orderId);
        $finalSumNoTax = $product_tax['finalSumNoTax']; //未含稅總金額
        $finalSumWithTax = $product_tax['finalSumWithTax']; //含稅總金額

        //     //應稅銷售額合計(全部商品沒有稅加起來)
        $total = floor($finalSumWithTax / 1.05) + $finalSumNoTax;
        $order['salesAmount'] = $total;

        // //稅額(有稅的商品加起來 + 沒稅的商品加起來 - 應稅銷售合計)
        $order['taxAmount'] = $finalSumWithTax + $finalSumNoTax - $total;

        // //含稅總額(有稅的商品加起來)
        $order['totalAmount'] = $finalSumWithTax;

        // //免稅銷售額合計(沒稅的商品加起來)
        $order['freeTaxSalesAmount'] = $finalSumNoTax;

        //     /****************************折抵金額(折扣總價)************************* */
        $order['totalDiscount'] = 0; //LinePay不折抵

        //     /**************************現金收入*************************** */
        $order['cashIncome'] = $this->OrderFinalPrice($orderId);

        //     /**************************現金找零*************************** */
        $order['cashChange'] = 0; //LinePay不找零

        //     /****************************最後要跟客人收的************************* */
        $order['finalPrice'] = $this->OrderFinalPrice($orderId);




        $orderProducts = DB::select("SELECT * FROM order_temp
                                     WHERE orderNum = '$orderId'");

        //     /****************************內用需要存桌號************************* */
        $order['seatId'] = $orderProducts[0]->seatId ;

        $order_detail = [];
        $order_option = [];
        foreach($orderProducts as $key => $product){

            /***** 訂單細項id ***** */
            $x = 0;
            $y = 13;
            $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orderDetailId = substr(str_shuffle($Strings), $x, $y);

            $details =  DB::select("SELECT A.orderNum,A.quantity , B.count, B.order_option_title,B.custom_option_id, C.product_title,C.unit ,C.price AS productPrice, D.price AS optionPrice  FROM order_temp AS A
                        LEFT JOIN order_temp_option AS B
                        ON A.optionId = B.order_temp_optionId
                        LEFT JOIN products AS C
                        ON A.productId = C.productId
                        LEFT JOIN custom_option AS D
                        ON B.custom_option_id = D.id
                        WHERE A.orderNum = '$orderId'
                        AND A.productId = '{$product->productId}'
                        AND A.optionId = '{$product->optionId}'
                        ");
            $unitPrice = 0;
            foreach ($details as $key2 => $detail) {
                $unitPrice += $detail->optionPrice;

                if(!empty( $detail->custom_option_id)){

                    $order_option[] = [
                        // "orderNum"=>$order['orderNum'],
                        "orderDetailId" => $orderDetailId,
                        "order_option_title" => $detail->order_option_title,
                        "count" => $detail->count,
                        "price" => $detail->optionPrice,
                        "custom_option_id" => $detail->custom_option_id,
                    ];
                }


            }

            $unitPrice += $details[0]->productPrice;
            $productName = $details[0]->product_title;
            $unit = $details[0]->unit;
            $quantity = $details[0]->quantity;

            $order_detail[] = [
                "orderNum" => $order['orderNum'],
                "productId" => $product->productId,
                "orderDetailId" => $orderDetailId,
                "productName" => $productName,
                "taxType" => 1,
                "unit" => $unit,
                "quantity" => $quantity,
                "unitPrice" => $unitPrice,
                "subtotal" => $unitPrice * $quantity,
            ];

        }

        // return ['order'=>$order,'order_detail'=>$order_detail,'order_option'=>$order_option];
        DB::table('orders')->insert($order);
        DB::table('order_detail')->insert($order_detail);
        DB::table('order_detail_option')->insert($order_option);

        // *** 計算本筆訂單成本 & 扣庫存 ***
        $tool = new Tool();
        $tool->orderCostAndInventory($order['orderNum'], $order_detail);

    }


    public function product_tax_caculate_ByOrderNum($orderId)
    {
        $sumWithTax = 0; //含稅總額初始值
        $sumnotax = 0; //未含稅總額初始值

        $products =  DB::select("SELECT B.* FROM order_temp AS A
                    LEFT JOIN products AS B
                    ON A.productId = B.productId
                    WHERE orderNum ='$orderId'");
        foreach($products as $product){
            if($product->taxType == 1){   //要稅的加起來
                $sell = $product->price;//全部優惠完總價
                $sumWithTax += $sell;
            }
            if($product->taxType == 0){   //不要稅的加起來
                $sell = $product->sell;//全部優惠完總價
                $sumnotax += $sell;
            }
        }

        $finalSumWithTax = $sumWithTax ; //Linepay不折扣 所以不用減discountCard
        //折扣金額先把含稅部分減完，再減為含稅部分
        if ($finalSumWithTax >= 0) { //含稅已將折扣減完不剩，所以aa不需要再減了
            $aa = 0; //尚須減的折扣金額
        } else {
            //aa為負值
            $aa = $finalSumWithTax;
            $finalSumWithTax = 0;
        }

        $finalSumNoTax = $sumnotax + $aa; //如果要稅的商品扣完換貨金額還有剩，再去把不用稅的錢扣掉

        return [
            'finalSumWithTax' => $finalSumWithTax, //含稅總金額
            'finalSumNoTax' => $finalSumNoTax, //未含稅總金額
        ];

    }


    // 訂單總價 Linepay用
    public function OrderFinalPrice($orderId)
    {
        $total = 0;

        $productPrice = DB::select("SELECT SUM(B.price * A.quantity) AS productPrice FROM `order_temp` AS A
                                    LEFT JOIN products AS B
                                    ON A.productId = B.productId
                                    where orderNum = '$orderId'");
        $optionPrice =  DB::select("SELECT SUM(C.price) AS optionPrice FROM `order_temp` AS A
                                    LEFT JOIN order_temp_option AS B
                                    ON A.optionId = B.order_temp_optionId
                                    LEFT JOIN custom_option AS C
                                    ON B.custom_option_id = C.id
                                    where orderNum = '$orderId'
                                    AND B.custom_option_id IS NOT NULL");

        // return [$productPrice,$optionPrice];
        //算總價
        // return $optionPrice[0]->optionPrice;
        $total = $productPrice[0]->productPrice + $optionPrice[0]->optionPrice;

        return $total;
    }

}
