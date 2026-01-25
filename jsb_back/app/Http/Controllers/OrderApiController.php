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
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MemberController; //會員點數


class OrderApiController extends Controller
{

    use InvoiceTraits;
    //所得商品所有規格
    function getProductCustom(Request $req){
        $productId = $req->productId;
        $custom = DB::select("SELECT products_with_custom.customCateId,custom_category.customCateTitle,custom_category.require,custom_category.single
        FROM products_with_custom
        LEFT JOIN custom_category ON custom_category.id = products_with_custom.customCateId
        WHERE products_with_custom.productId = '$productId'");
        $customCateId = $custom[0]->customCateId;
        $option = DB::select("SELECT * FROM custom_option WHERE customCateId = '$customCateId'");
        return ['success' => true, 'custom' => $custom, 'option' => $option];
    }

    //開錢櫃
    public function open_drawer()
    {
        // $ip="192.168.1.21";

        //$port = "80";

        if (env('INVOICE_SETTING_ENV') == 1) { //本地
            return  $this->open_cashDrawer();
        } else { //env('INVOICE_SETTING_ENV') == 2 wifi，固定用第一台開錢櫃
            $ip = env('INVOICE_SETTING_IP1');
            return  $this->open_cashDrawer($ip);
        }
    }

    //結帳
    public function checkout(Request $req)
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
        $order['orderTime'] = date("Y-m-d H:i:s");

        /***********************店別*********************/
        $order['storeId'] = $req->storeId;

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
        $order['invoiceNumberEn'] = "AB";

        /***************************發票號碼(8位數)************************** */
        // $order['invoiceNumber'] = $order['receiptID'];
        $order['invoiceNumber'] = "12345678";

        /*************************發票的隨機碼**************************** */
        $x = 0;
        $y = 4;
        $Strings = '0123456789';
        $order['randomNum'] = substr(str_shuffle($Strings), $x, $y);

        /**********************0 非捐贈發票, 1 捐贈發票******************************* */
        $order['donate'] = 0;

        /***********************課稅別: 1 應稅, 2　零稅率, 3 免稅****************************** */
        $order['taxType'] = 1;

        /****************************稅率(先固定0.05)************************* */
        $order['taxRate'] = 0.05;
        /*************************用餐方式**********************************/
        $useType = $req->SelectUsetype;
        $order['useType'] = $req->SelectUsetype;
        if($useType == 2){ //內用要存桌號
             $order['seatId'] = $req->seatId;
        }

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
        // return $order;
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
            $result =   MemberController::PostMemberPoint($type, $userId, $token, $MemberPoint , $point['storeId']);
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
        return ['success' => true, "orderNum" => $order['orderNum']];
    }

    //桌邊點餐暫存
    public function tempOrder(Request $req)
    {



        /**********************訂單編號****************** */
        $x = 0;
        $y = 22;
        $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $orderNum = substr(str_shuffle($Strings), $x, $y);

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

        $orders = $req->orders;

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
            $temp['byCash'] = 1;
            $orderTemp[] =  $temp;
        }

        DB::table('order_temp')->insert($orderTemp);
        DB::table('order_temp_option')->insert($orderTempOptions);

        return ['success' => true, 'orderNum' => $orderNum];
    }

    // 桌邊點餐刪除
    public function deletetempOrder($orderNum)
    {
        DB::table("order_temp")->where("orderNum",$orderNum)->delete();

        return ['success' => true, "msg" => "訂單刪除成功"];
    }


    //查詢發票內容準備印出
    public function test_order($MachineCode, $OrderNum)
    {
        // return 123;
        //列印發票
        $orderNum = $OrderNum;
        // return $orderNum;
        // $machineCode=$MachineCode;
        // $machineDetail=$this->getMachine($machineCode);
        // return $machineDetail;
        // $ip=$machineDetail[0]->ip;
        // $port=$machineDetail[0]->port;
        // $machineId=$machineDetail[0]->id;
        // return $orderNum;
        // $temp=$this->print_invoice_and_write_MIG($orderNum,$machineId);
        $temp = $this->print_invoice_and_write_MIG($orderNum);
        // return $temp;
        $value = $temp['value'];
        // $res=$temp['res'];
        // $value['_token']='Ou2EUmnCLedirfbHEgN4LzBFY1lgnFgHnfc8xhhy';
        // $value['len']=strlen($value['qrcontentLeft']);
        // $value['len2']=strlen($value['qrcontentRight']);
        // $value['ip']=$ip;
        // $value['port']=$port;
        // if($temp['data']['order'][0]->buyType==1){
        //     // return $res;
        //     $filed=$this->write_C0401XML($res);
        // }
        // if($temp['data']['order'][0]->buyType==2){
        //      $filed=$this->write_A0401XML($res);
        // }
        return json_encode($value);
    }

    //查詢發票內容準備印出
    // public function print_invoice_and_write_MIG($orderNum, $machineId)
    public function print_invoice_and_write_MIG($orderNum)
    {
        $order = DB::select("SELECT *,E.comment as useTypeTitle FROM orders A
        left join store  B
        on A.storeId=B.storeId
        left join order_detail  C
        on C.orderNum = A.orderNum
        left join order_detail_option D
        on C.orderDetailId = D.orderDetailId
        left join erp_options E
		on E.Code = A.useType
        WHERE A.orderNum='$orderNum'
        AND E.class = 'use_type'");

        // $order = $this->get_order($orderNum);
        // return $order;
        // $data['machineId'] = $machineId;
        $data['order'] = $order;
        $data['orderNum'] = $orderNum;
        $data['machineId'] = $order[0]->machineId;
        // return  $data['order'] ;
        // $value = 123;
        // return 123;
        // return $data;
        $value = $this->reciept($data); //generate reciept
        // $res = $this->produce_MIG($data);
        // $res = $this->write_MIG($res, $value, $data);
        // $temp = array('data' => $data, 'value' => $value, 'res' => $res);
        // return $temp;
        return ['value' => $value];
    }

    //發票圖片存入資料夾並列印
    public function upload_and_print_invoice(Request $request)
    {
        // return 123;
        // return $request;
        // return($request->input('directoryName'));
        $twice = $request->twice;   //因為沒統編結帳會結兩次 所以用這個參數判斷現在是印發票還是明細 1->發票、2->明細
        // return $twice;
        $type = $request->input('type');
        $pdfdoc = $request->input('base64Img'); //發票的輩死64檔案
        // return $pdfdoc ;
        // $ip= $request->input('ip');
        // $port= $request->input('port');
        $ip = null;
        $port = null;

        $orderNumber = $request->input('OrderNum'); //訂單編號

        /***************** */
        $extension = explode('/', explode(':', substr($pdfdoc, 0, strpos($pdfdoc, ';')))[1])[1];
        $directoryName = date("YmdHis");
        $filename = date("Y-m-d");
        $replace = substr($pdfdoc, 0, strpos($pdfdoc, ',') + 1);
        // find substring fro replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $pdfdoc);
        $image = str_replace(' ', '+', $image);
        $imageName = $directoryName . '.' . $twice . '.' . $extension;

        Storage::disk('public')->put('invoice/' . $filename . '/' . $orderNumber . '/' . $imageName, base64_decode($image));    //到目錄底下建立資料夾並存入發票圖片
        $filename = Storage::disk('public')->path('invoice/' . $filename . '/' . $orderNumber . '/' . $imageName);

        /******************* */
        // return $filename;
        $this->print_image($filename, $ip, $port, $twice);
    }

    //日結圖片存入資料夾並列印
    public function daycheckout_print_invoice(Request $request)
    {
        // return $request;

        // return($request->input('directoryName'));
        // $twice = $request->twice;   //因為沒統編結帳會結兩次 所以用這個參數判斷現在是印發票還是明細 1->發票、2->明細
        // return $twice;
        // $type = $request->input('type');
        $pdfdoc = $request->input('base64Img'); //日結的輩死64檔案
        // return $pdfdoc ;
        // $ip= $request->input('ip');
        // $port= $request->input('port');
        $ip = null;
        $port = null;

        // $orderNumber = $request->input('OrderNum'); //訂單編號

        /***************** */
        $extension = explode('/', explode(':', substr($pdfdoc, 0, strpos($pdfdoc, ';')))[1])[1];
        $directoryName = date("YmdHis");
        $filename = date("Y-m-d");
        $replace = substr($pdfdoc, 0, strpos($pdfdoc, ',') + 1);
        // find substring fro replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $pdfdoc);
        $image = str_replace(' ', '+', $image);
        $imageName = $directoryName . '.' . $extension;

        Storage::disk('public')->put('settlement/' . $filename . '/' . $imageName, base64_decode($image));

        $filename = Storage::disk('public')->path('settlement/' . $filename . '/' . $imageName);


        /******************* */
        // return $filename;
        return $this->print_image($filename, $ip, $port, 1);
    }

    //算商品應稅銷售額合計(輪子)
    public function product_tax_caculate($product, $discountCard)
    {
        // $exchangeProductDiscount = 0;
        // if(!empty($order['ExchangeArray'])){    //如果有換貨的東西的價錢計算
        //     foreach($exchangeArray as $exchangeArray){
        //         $exchangeProductDiscount += (int)$exchangeArray['finalPrice'];
        //     }
        // }
        // return $exchangeProductDiscount;
        $sumWithTax = 0; //含稅總額初始值
        $sumnotax = 0; //未含稅總額初始值
        $discountCard = $discountCard; //整筆訂單折扣總額初始值，外部帶入
        foreach ($product as $product) {
            if ($product['taxType'] == 1) {   //要稅的加起來
                // if(isset($product['DiscountType']) && $product['DiscountType'] == "off"){   //自訂折扣
                //     $sell = round($product['sell'] * ($product['DiscountValue']/100));//全部優惠完總價
                // }elseif(isset($product['DiscountType']) && $product['DiscountType'] == "cut"){  //自訂減價
                //     $sell = $product['sell'] - (int)$product['DiscountValue'];//全部優惠完總價
                // }else{  //什麼都沒有
                $sell = $product['price']; //全部優惠完總價
                // }
                $sumWithTax += $sell;
            }
            if ($product['taxType'] == 0) {   //不要稅的加起來
                // if($product['sell'] < 0){
                //     $discountCard += $product['sell'];
                // }else{
                //     if(isset($product['DiscountType']) && $product['DiscountType'] == "off"){   //自訂折扣
                //         $sell = round($product['sell'] * ($product['DiscountValue']/100));//全部優惠完總價
                //     }elseif(isset($product['DiscountType']) && $product['DiscountType'] == "cut"){  //自訂減價
                //         $sell = $product['sell'] - (int)$product['DiscountValue'];//全部優惠完總價
                //     }else{  //什麼都沒有
                $sell = $product['sell']; //全部優惠完總價
                //     }
                $sumnotax += $sell;
                // $sumnotax += $product['sell'];
                // }
            }
        }
        // $finalSumWithTax = $sumWithTax - $exchangeProductDiscount + $discountCard;  //要稅的商品先減掉換貨的金額
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

    //日結
    public function daycheckout(Request $req)
    {
        // return $req;
        $storeId = $req->storeId;
        $start = date("Y-m-d") . ' ' . $req->start . ':00';
        $end = date("Y-m-d") . ' ' . $req->end . ':59';

        // $orders =   DB::select("SELECT * from orders
        //             WHERE storeId = '$storeId'
        //             AND (orderTime >=  '$start' AND  orderTime <= '$end')");
        // return $orders;

        //取得店家
        $store = DB::select("SELECT * from store
                    WHERE storeId = '$storeId'");
        //店家名稱
        $data['storeName'] = $store[0]->storeName;
        //分店名稱
        $data['storeBranch'] = $store[0]->storeBranch;

        // 日期 : 今天日期 date(Ymd)
        $data['date'] = date("Y-m-d");

        // 來客數 : 結了幾次帳 order總筆數
        $orders =   DB::select("SELECT * from orders
                                WHERE storeId = '$storeId'
                                AND (orderTime >=  '$start' AND  orderTime <= '$end')");
        $data['orderCount'] = count($orders);

        /**********************************現金收入、現今找零、今日營業總額****************************** */
        $finalPrice = 0; //總營業額初始值
        $cashIncome_cash = 0; //總收現金初始值
        $cashIncome_credit = 0; //總收信用卡初始值
        $totalDiscount = 0; //總折扣金額初始值
        $taxAmount = 0; //總稅金初始值
        $cashChange = 0; //總找的錢初始值
        // return $orders;
        foreach ($orders as $order) {
            $finalPrice += $order->finalPrice ? $order->finalPrice : 0;
            $totalDiscount += $order->totalDiscount ? $order->totalDiscount : 0;
            $taxAmount += $order->taxAmount ? $order->taxAmount : 0;
            $cashChange += $order->cashChange ? $order->cashChange : 0;
            if ($order->payMethod == 1) { //現金收款
                $cashIncome_cash += $order->cashIncome;
            } else if ($order->payMethod == 2) { //信用卡收款
                $cashIncome_credit += $order->cashIncome;
            }
        }
        // return $data;
        // return $orders[0]->finalPrice;
        //總營業額
        $data['finalPrice'] = $finalPrice;

        //總收現金
        $data['cashIncome_cash'] = $cashIncome_cash;

        //總收信用卡
        $data['cashIncome_credit'] = $cashIncome_credit;

        //總折扣金額
        $data['totalDiscount'] = $totalDiscount;

        //總共的稅金
        $data['taxAmount'] = $taxAmount;

        // 找錢 : 今天找的錢 orders.cashChange
        $data['cashChange'] = $cashChange;

        //錢櫃應有 = cashIncome_cash - cashChange
        $data['TrueMomey'] =  $finalPrice - $cashChange;

        // 開始時間
        $data['start'] = $start;

        // 結束時間
        $data['end'] = $end;
        $checkoutUrl ="storeName={$data['storeName']}&storeBranch={$data['storeBranch']}&date={$data['date']}&orderCount={$data['orderCount']}&finalPrice={$data['finalPrice']}&cashIncome_cash={$data['cashIncome_cash']}&cashIncome_credit={$data['cashIncome_credit']}&totalDiscount={$data['totalDiscount']}&taxAmount={$data['taxAmount']}&cashChange={$data['cashChange']}&TrueMomey={$data['TrueMomey']}&start={$data['start']}&end={$data['end']}";

        return [
            'success' => true,
            'data' => $data,
            'checkoutUrl'=>$checkoutUrl,
        ];
    }

    //前台取得桌邊點餐訂單
    public function gettempOrder(Request $req)
    {
        // return $req;
        $orders = [];
        if ($req->randomCode) {
            $orderCheck = $req->randomCode;

            $orderInfo = DB::select("SELECT order_temp.orderNum,order_temp.orderCheck,order_temp.orderTime , order_temp.seatId FROM order_temp
                                     LEFT JOIN products
                                     ON products.productId = order_temp.productId
                                     WHERE orderCheck = '$orderCheck'
                                     AND  (orderTime > (now() - interval 60 minute))
						             GROUP BY order_temp.orderNum");

            // return $orderInfo;
            $orders = DB::select("SELECT * FROM order_temp
                               LEFT JOIN products
                               ON products.productId = order_temp.productId
                               WHERE orderCheck = '$orderCheck'
                               AND  (orderTime > (now() - interval 60 minute))
                                ORDER BY orderTime desc");
            foreach ($orders as $order) {
                $optionId = $order->optionId;
                // return $optionId;
                $options = DB::select("SELECT * FROM order_temp_option AS A
                                                LEFT JOIN custom_option AS B
                                                ON A.custom_option_id = B.id
                                                WHERE order_temp_optionId = '$optionId'");
                $order->options = $options;
            }
            // return $orders;

        } else {
            $seatId = $req->seatId;
            $orderInfo = DB::select("SELECT order_temp.orderNum,order_temp.orderCheck,order_temp.orderTime  , order_temp.seatId FROM order_temp
                                     LEFT JOIN products
                                     ON products.productId = order_temp.productId
                                     WHERE seatId = '$seatId'
                                     AND  (orderTime > (now() - interval 60 minute))
						             GROUP BY order_temp.orderNum");
            $orders = DB::select("SELECT * FROM order_temp
                                  LEFT JOIN products
                                  ON products.productId = order_temp.productId
                                  WHERE seatId = '$seatId'
                                  AND  (orderTime > (now() - interval 60 minute))");
            foreach ($orders as $order) {
                $optionId = $order->optionId;
                $options = DB::select("SELECT * FROM order_temp_option AS A
                                       LEFT JOIN custom_option AS B
                                       ON A.custom_option_id = B.id
                                       WHERE order_temp_optionId = '$optionId'");
                $order->options = $options;
            }

            // return $orderInfo;
            // $orderInfo->orders = $orders;

        }

        foreach ($orders as $order) {
            foreach ($orderInfo as $info) {
                if ($order->orderNum == $info->orderNum) {
                    $info->order[] = $order;
                    break;
                }
            }
        }



        // $data = [];
        // foreach($orders as $order){
        //     )
        // }

        return ['success' => true, 'orders' => $orderInfo];
    }

    //桌邊點餐機完成點餐取得特定單號的訂單
    public function GetSpecificOrder(Request $req)
    {
        // return $req;
        $orderNum = $req->orderNum;
        // return $orderNum ;
        $orderInfo = DB::select("SELECT order_temp.orderNum,order_temp.orderCheck,order_temp.orderTime FROM order_temp
                                     LEFT JOIN products
                                     ON products.productId = order_temp.productId
                                     WHERE orderNum = '$orderNum'
                                     AND  (orderTime > (now() - interval 30 minute))
						             GROUP BY order_temp.orderNum");

        // return $orderInfo;
        $orders = DB::select("SELECT * FROM order_temp
                                    LEFT JOIN products
                                    ON products.productId = order_temp.productId
                                     WHERE orderNum = '$orderNum'
                                    AND  (orderTime > (now() - interval 30 minute))");
        foreach ($orders as $order) {
            $optionId = $order->optionId;
            // return $optionId;
            $options = DB::select("SELECT * FROM order_temp_option AS A
                                                        LEFT JOIN custom_option AS B
                                                        ON A.custom_option_id = B.id
                                                        WHERE order_temp_optionId = '$optionId'");
            $order->options = $options;
        }

        foreach ($orders as $order) {
            foreach ($orderInfo as $info) {
                if ($order->orderNum == $info->orderNum) {
                    $info->order[] = $order;
                    break;
                }
            }
        }

        if (!empty($orderInfo)) {
            return ['success' => true, 'orders' => $orderInfo[0]];
        } else {
            return ['success' => false, 'msg' => '無此訂單'];
        }
    }


    public function GetSpecificOrderLinepay(Request $req)
    {

        $orderNum = $req->orderNum;
        $orders = DB::select("SELECT A.orderTime, B.*,C.* FROM orders AS A
                              LEFT JOIN order_detail AS B
                              ON A.orderNum = B.orderNum
                              LEFT JOIN products AS C
                              ON B.productId = C.productId
                              WHERE A.orderNum = '$orderNum'");
        $orderTime = $orders[0]->orderTime;

        foreach ($orders as $order) {
            $orderDetailId = $order->orderDetailId;
            // return $optionId;
            $options = DB::select("SELECT * FROM order_detail_option AS A
                                   LEFT JOIN order_detail AS B
                                   ON A.orderDetailId = B.orderDetailId
                                   WHERE A.orderDetailId = '$orderDetailId'");
            $order->options = $options;
        }

        if (!empty($orders)) {
            return ['success' => true, 'orders' => $orders , 'orderTime' => $orderTime];
        } else {
            return ['success' => false, 'msg' => '無此訂單'];
        }


    }



    public function GetPhoneOrder(Request $req)
    {
        // return $req;
        $storeId = $req->storeId;

        $orderInfo = DB::select("SELECT order_temp.orderNum,order_temp.orderCheck,order_temp.orderTime  , order_temp.seatId FROM order_temp
                                LEFT JOIN products
                                ON products.productId = order_temp.productId
                                WHERE (orderTime > (now() - interval 60*24 minute))
                                AND byCash = 1
                                GROUP BY order_temp.orderNum
                                ORDER BY orderTime desc");

        $orders = DB::select("SELECT * FROM order_temp
                            LEFT JOIN products
                            ON products.productId = order_temp.productId
                            WHERE (orderTime > (now() - interval 60*24 minute))
                            ORDER BY orderTime desc");

        foreach ($orders as $order) {
            $optionId = $order->optionId;
            $options = DB::select("SELECT * FROM order_temp_option AS A
                    LEFT JOIN custom_option AS B
                    ON A.custom_option_id = B.id
                    WHERE order_temp_optionId = '$optionId'");
            $order->options = $options;
        }

        foreach ($orders as $order) {
            foreach ($orderInfo as $info) {
                if ($order->orderNum == $info->orderNum) {
                    $info->order[] = $order;
                    break;
                }
            }
        }


        $linepayOrders =  DB::select("SELECT * FROM orders
                    WHERE storeId = '$storeId'
                    AND billStatus = 0
                    AND  (orderTime > (now() - interval 60 minute))
                    ORDER BY orderTime desc");

        return ['success' => true, 'linepayOrders' => $linepayOrders ,'CashPhoneOrders'=>$orderInfo];

    }

    public function updateBillStatus($orderId)
    {
        DB::table("orders")->where("orderNum",$orderId)->update([
            'billStatus'=>1
        ]);
        return ['success'=>true,'msg'=>'發票狀態更新成功'];
    }
}
