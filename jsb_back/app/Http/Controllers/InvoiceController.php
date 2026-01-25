<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;


class InvoiceController extends Controller
{
    // SmilePay發票
    public function smilePayInvoice($order)
    {
        // 使用者參數
        $Grvc = 'Grvc=' . env('GRVC');
        $Verify_key = 'Verify_key=' . env('VERIFY_KEY');

        // 發票資訊
        $InvoiceDate = "InvoiceDate=" . date('Y/m/d');
        $InvoiceTime = "InvoiceTime=" . date('H:i:s');
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
        foreach($order['orders'] as $product){
            $Description = $Description . $product['product_title'] . '|';
            $Quantity = $Quantity . $product['count'] . '|';
            $UnitPrice =  $UnitPrice . (int)$product['price'] . '|';
            $price = $product['count'] * $product['price'];
            $Amount = $Amount . $price . '|';
            $AllAmount += $price;
        }

        $Description = "Description=" . rtrim($Description , '|');
        $Quantity = "Quantity=" . rtrim($Quantity , '|');
        $UnitPrice = "UnitPrice=" . rtrim($UnitPrice , '|');
        $Amount = "Amount=" . rtrim($Amount , '|');
        $AllAmount = "ALLAmount=" . $AllAmount;

        // 買受人資訊
        $Buyer_id = !empty($order['buyTax']) ? "&Buyer_id=" . $order['buyTax'] : null;
        $CarrierType = !empty($order['carrier']) ? "&CarrierType=3J0002" : null;
        $CarrierID = !empty($order['carrier']) ? "&CarrierID=" . $order['carrier'] : null;
        $CarrierID2 = !empty($order['carrier']) ? "&CarrierID2=" . $order['carrier'] : null;

        // 組合要打的網址
        $smilePayUrl = env('SMILEPAY_URL') . $Grvc . "&" . $Verify_key . "&" . $Intype . "&" . $TaxType . "&" . $DonateMark . "&" . $Description . "&" . $Quantity . "&" . $UnitPrice . "&" . $Amount . "&" . $AllAmount . "&" . $InvoiceDate . "&" . $InvoiceTime . $Buyer_id . $CarrierType . $CarrierID . $CarrierID2;

        $client = new Client(); //初始化客戶端
        $response = $client->get($smilePayUrl);
        $body = $response->getBody(); //獲取響應體，物件

        $xml = simplexml_load_string($body);
        $json = json_decode(json_encode($xml),true);
        return $json;
    }
    
    // smilePay作廢發票
    public function smilePayCanaelInvoice($oldDateTime , $oldInvoiceEn , $oldInvoiceNumber)
    {
        // 使用者參數
        $Grvc = 'Grvc=' . env('GRVC');
        $Verify_key = 'Verify_key=' . env('VERIFY_KEY');
        // 作廢發票參數
        $InvoiceNumber = 'InvoiceNumber=' . $oldInvoiceEn . $oldInvoiceNumber;
        $InvoiceDate = 'InvoiceDate=' . $oldDateTime;
        $types = 'types=' . 'Cancel';
        $CancelReason = 'CancelReason=' . '作廢';

        // 組合要打的網址
        $smilePayUrl = env('SMILEPAY_CANCEL_URL') . $Grvc . "&" . $Verify_key . "&" . $InvoiceNumber . "&" . $InvoiceDate . "&" . $types . "&" . $CancelReason;

        $client = new Client(); //初始化客戶端
        $response = $client->get($smilePayUrl);
        $body = $response->getBody(); //獲取響應體，物件

        $xml = simplexml_load_string($body);
        $json = json_decode(json_encode($xml),true);
        return $json;
    }

    // 重開發票 (先報廢再重新寫入訂單並開立新的發票)
    public function reGenerateInvoice(Request $request)
    {
        // 收到舊的訂單號
        $oldOrderNumber = $request->oldOrderNum;

        $newTaxId = $request->newTaxId;

        // 舊的訂單資料 
        $smilePayUrl = $this->smilePayUrl($oldOrderNumber,$newTaxId);

        // 舊的發票號碼 報廢用
        $oldDateTime = $smilePayUrl['oldDateTime'];
        $oldInvoiceEn = $smilePayUrl['oldInvoiceEn'];
        $oldInvoiceNumber = $smilePayUrl['oldInvoiceNumber'];

        // 打api 開立新發票
        $client = new Client(); //初始化客戶端
        $response = $client->get($smilePayUrl['smilePayUrl']);
        $body = $response->getBody(); //獲取響應體，物件
        $xml = simplexml_load_string($body);
        $json = json_decode(json_encode($xml),true);        

        $newRandomNumber = $json['RandomNumber'];
        $newInvoiceEn = $json['InvoiceNumber'][0] . $json['InvoiceNumber'][1];
        $newInvoiceNumber = $json['InvoiceNumber'][2] . $json['InvoiceNumber'][3] . $json['InvoiceNumber'][4] . $json['InvoiceNumber'][5] . $json['InvoiceNumber'][6] . $json['InvoiceNumber'][7] . $json['InvoiceNumber'][8] . $json['InvoiceNumber'][9];
        $newInvoiceTime = date("Y-m-d", strtotime($json['InvoiceDate'])) . " " . $json['InvoiceTime'];

        // 報廢舊發票
        $this->smilePayCanaelInvoice($oldDateTime , $oldInvoiceEn , $oldInvoiceNumber);

        // 用舊的訂單資料 寫一張新的訂單
        $this->reInsertOrder($newInvoiceTime , $oldOrderNumber , $newRandomNumber , $newInvoiceEn , $newInvoiceNumber , $newTaxId);
        
        $getDataSmilePayUrl = "Grvc=" . env('GRVC')  . "&Verify_key=" . env('VERIFY_KEY') . "&InNumber=" . $newInvoiceEn . $newInvoiceNumber . "&RaNumber=" . $newRandomNumber . "&InDate=" . date("Y-m-d", strtotime($json['InvoiceDate']));
        
        return [
            'success' => true,
            'smilePayUrl' => $getDataSmilePayUrl
        ];   
    }

    // 補開發票產生新的開發票的網址
    public function smilePayUrl($oldOrderNumber , $newTaxId)
    {
        // 撈舊的資料
        $oldOrderData = $this->select_orders_db($oldOrderNumber);
        $oldOrderDetailDatas = $this->select_orders_detail_db($oldOrderNumber);

        // 舊的發票字軌發票號 報廢用
        $oldDateTime = date("Y/m/d", strtotime($oldOrderData[0]->orderTime)); 
        $oldInvoiceEn = $oldOrderData[0]->invoiceNumberEn;
        $oldInvoiceNumber = $oldOrderData[0]->invoiceNumber;

        // 使用者參數
        $Grvc = 'Grvc=' . env('GRVC');
        $Verify_key = 'Verify_key=' . env('VERIFY_KEY');

        // 發票資訊
        $InvoiceDate = "InvoiceDate=" . date('Y/m/d');
        $InvoiceTime = "InvoiceTime=" . date('H:i:s');
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
        foreach($oldOrderDetailDatas as $product){
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
        if(!empty($oldOrderData[0]->buyTax)){
            $Buyer_id = "&Buyer_id=" . $oldOrderData[0]->buyTax;
        }

        if(!empty($newTaxId)){
            $Buyer_id = "&Buyer_id=" . $newTaxId;
        }       

        $smilePayUrl = env('SMILEPAY_URL') . $Grvc . "&" . $Verify_key . "&" . $Intype . "&" . $TaxType . "&" . $DonateMark . "&" . $Description . "&" . $Quantity . "&" . $UnitPrice . "&" . $Amount . "&" . $AllAmount . "&" . $InvoiceDate . "&" . $InvoiceTime . $Buyer_id;
        return [
            'smilePayUrl' => $smilePayUrl,
            'oldDateTime' => $oldDateTime,
            'oldInvoiceEn' => $oldInvoiceEn,
            'oldInvoiceNumber' => $oldInvoiceNumber
        ];
    }

    // 寫入一筆新的訂單 資料跟舊的一樣
    public function reInsertOrder($newInvoiceTime , $oldOrderNumber , $newRandomNumber , $newInvoiceEn , $newInvoiceNumber , $newTaxId)
    {
        $newOrderNumber = $this->orderNumber();

        // 抓舊的訂單 更新發票號碼、發票隨機碼、訂單編號 並寫入 id欄位要拿掉
        $oldOrderData = $this->select_orders_db($oldOrderNumber);
        unset($oldOrderData[0]->id);
        $oldInvoiceNumberEn = $oldOrderData[0]->invoiceNumberEn;
        $oldInvoiceNumber = $oldOrderData[0]->invoiceNumber;

        $oldOrderData[0]->orderNum = $newOrderNumber;
        $oldOrderData[0]->orderTime = $newInvoiceTime;
        $oldOrderData[0]->randomNum = $newRandomNumber;
        $oldOrderData[0]->invoiceNumberEn = $newInvoiceEn;
        $oldOrderData[0]->invoiceNumber = $newInvoiceNumber;

        if(!empty($newTaxId)){
            $oldOrderData[0]->buyTax = $newTaxId;
            $oldOrderData[0]->buyName = $newTaxId;
            $oldOrderData[0]->buyType = 2;
        }

        // 新的訂單的資料寫入資料庫
        $this->insert_orders_db((array)$oldOrderData[0]);

        // 抓舊的訂單細項 更新訂單編號 並寫入 id欄位要拿掉
        $oldOrderDetailDatas = $this->select_orders_detail_db($oldOrderNumber);
        foreach($oldOrderDetailDatas as $oldOrderDetailData){
            unset($oldOrderDetailData->id);
            $oldOrderDetailData->orderNum = $newOrderNumber;
            $this->insert_order_detail_db((array)$oldOrderDetailData);
        }

        return [
            'newOrderNumber' => $newOrderNumber,
            'oldInvoiceNumberEn' => $oldInvoiceNumberEn,
            'oldInvoiceNumber' => $oldInvoiceNumber,
            'orderTime' => $oldOrderData[0]->orderTime
        ];
    }

    // 取得指定發票號資料
    public function getOldInvoice(Request $request)
    {
        $invoiceNumber = $request->invoiceNumber;

        $invoice_5 =  DB::select("SELECT orderNum , invoiceNumber , orderTime , finalPrice
                                  FROM orders
                                  WHERE invoiceNumber = '$invoiceNumber'
                                  ORDER BY orderTime DESC");
        $invoiceData = array();
        
        foreach ($invoice_5 as $data) {
            
            $orderNumber = $data->orderNum;
            $orderDetail = DB::select("SELECT * FROM orders
                                       LEFT JOIN order_detail
                                       ON orders.orderNum = order_detail.orderNum
                                       WHERE order_detail.orderNum = '$orderNumber'");
            foreach ($orderDetail as $detail){
                
                $orderDetailId = $detail->orderDetailId;
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
 
    // 訂單編號亂碼
    public function orderNumber()
    {
        $x = 0;
        $y = 22;
        $Strings = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $orderNum = substr(str_shuffle($Strings), $x, $y);

        return $orderNum;
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

    // 寫入新的訂單資料
    public static function insert_orders_db($oldOrderData)
    {
        return DB::table('orders')->insert($oldOrderData);
    }

    // 寫入新的訂單細項資料
    public static function insert_order_detail_db($oldOrderDetailData)
    {
        return DB::table('order_detail')->insert($oldOrderDetailData);
    }
}
