<?php

namespace App\Traits;

use App\libraries\clsLibGTIN;
use DB;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos;
trait InvoiceTraits
{
    public function check_availableIP($host,$port){

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $connection =  @socket_connect($socket, $host,$port);

        if( $connection ){
            // echo 'ONLINE';
            return 1;
        }
        else {
            // echo 'OFFLINE: ' . socket_strerror(socket_last_error( $socket ));
            return 0;
        }

    }
    public function print_image($file,$ip,$port,$twice)
    {
        // if($twice == 1){  //用第一台印 
        //     $dest = env('INVOICE_SETTING_NAME1'); 
        //     $ip = env('INVOICE_SETTING_IP1');
        // }else if($twice == 2){ //用第二台印
        //     $dest = env('INVOICE_SETTING_NAME2'); 
        //     $ip = env('INVOICE_SETTING_IP2'); 
        // }

        // if(env('INVOICE_SETTING_ENV') == 1){ //本地
        //     $connector = new WindowsPrintConnector($dest);
        // }else{ //wifi
        //     $connector = new NetworkPrintConnector($ip);
        // }        

        // // 測試明細印兩次
        // $printer = new Printer($connector); 
        // $tux = EscposImage::load($file, false);

        if($twice == 1){
            $dest = env('INVOICE_SETTING_NAME1');
            $connector = new WindowsPrintConnector($dest);
            $printer = new Printer($connector); 
            $tux = EscposImage::load($file, false);
            $printer->bitImageColumnFormat($tux);
            $printer->feed();
            $printer->cut();
            $printer->close();
        }else{
            for($i=0;$i<2;$i++){
                if($i == 0){
                    $dest = env('INVOICE_SETTING_NAME1');
                    $connector = new WindowsPrintConnector($dest);
                }else{
                    $ip = env('INVOICE_SETTING_IP2');
                    $connector = new NetworkPrintConnector($ip);
                }
                $printer = new Printer($connector); 
                $tux = EscposImage::load($file, false);
                $printer->bitImageColumnFormat($tux);
                $printer->feed();
                $printer->cut();
                $printer->close();
            }
        }


        // 測試明細印兩次

        // if($twice == 1){  //用第一台印 
        //     $dest = env('INVOICE_SETTING_NAME1'); 
        //     $ip = env('INVOICE_SETTING_IP1');
        // }else if($twice == 2){ //用第二台印
        //     $dest = env('INVOICE_SETTING_NAME2'); 
        //     $ip = env('INVOICE_SETTING_IP2'); 
        // }

        // if(env('INVOICE_SETTING_ENV') == 1){ //本地
        //     $connector = new WindowsPrintConnector($dest);
        // }else{ //wifi
        //     $connector = new NetworkPrintConnector($ip);
        // }

        // $printer = new Printer($connector); 
        // $tux = EscposImage::load($file, false);
        // $printer->bitImageColumnFormat($tux);
        // $printer->feed();
        // $printer->cut();
        // $printer->close();
    }
    public function randomString($length)
    {
        $characters = '1234567890';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function randomAlphabet($length)
    {$characters = 'ABCDEFGHIJKLMNPQUSTUVWXYZ';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function valid_EAN($code)
    {
        $check = new \clsLibGTIN($code);
        $res = $check->GTINCheck($code) ? 'correct' : 'incorrect';
        return $res;
    }
    public function aes128_cbc_encrypt($aesKey, $invoice_random)
    {
        $spec_key = "Dt8lyToo17X/XkXaQvihuA==";
        $key = hex2bin($aesKey);
        $iv = base64_decode($spec_key);
        $data = $this->pkcs5_pad($invoice_random, 16);
        return base64_encode(
            openssl_encrypt(
                $data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv
            )
        );
    }
    public function reciept($data)
    {
        date_default_timezone_set('Asia/Taipei');
        $today = date("Y-m-d H:i:s");
        $directoryName = $data['orderNum'];
        $uniformInvoice =$data['order'][0]->invoiceNumberEn.'-'.$data['order'][0]->invoiceNumber;
        $year = date('Y') - 1911;
        $month = date("m");
        $day = date("d");
        $taiwan_date = $year . $month . $day;
        $taiwan_year = $year . '年';
        
        switch($month){
            case 1:
            case 2:
                $taiwan_month = "01" . "-" . "02" . "月";
            break;

            case 3:
            case 4:
                $taiwan_month = "03" . "-" . "04" . "月";
            break;

            case 5:
            case 6:
                $taiwan_month = "05" . "-" . "06" . "月";
            break;

            case 7:
            case 8:
                $taiwan_month = "07" . "-" . "08" . "月";
            break;

            case 9:
            case 10:
                $taiwan_month = "09" . "-" . 10 . "月";
            break;

            case 11:
            case 12:
                $taiwan_month = 11 . "-" . 12 . "月";
            break;
        }

        $randomNum = intval($this->randomString(4));
        $data['randomNum'] = $randomNum;
        $data['invoiceNum'] = $uniformInvoice;
        $data['sellTax'] = $data['order'][0]->sellTax;

        $data['buyTax'] = $data['order'][0]->buyTax;
        // $salePrice = base_convert((intval($data['order'][0]->salesAmount)), 10, 16);//未稅總額
        $salePrice = base_convert((intval($data['order'][0]->finalPrice)), 10, 16);//未稅總額

        $totalPrice =intval($data['order'][0]->finalPrice);//含稅總額
        $data['totalPrice'] = $totalPrice;
        $sixteenbitPrice = base_convert($totalPrice, 10, 16);
        if ($data['order'][0]->buyType== 1) {
            $buyTaxNum = 00000000;
        } else {
            $buyTaxNum =$data['order'][0]->buyTax;
        }
        $sellTaxNum =$data['order'][0]->sellTax;

        $content = $uniformInvoice . $randomNum;
        $aesKey = "52DC135E35B676B933F1A7700E9B8B20";
        $EncrypVerification = $this->aes128_cbc_encrypt($aesKey, $content);
        $ownArea = '**********';
        $encodingParameter = 1;
        DB::connection('mysql');
        $orderDetail = DB::select("SELECT * FROM order_detail WHERE orderNum=?", [$data['orderNum']]);
        $transAmount = count($orderDetail); //該張發票交易品目總筆數
        $orderAmount = count($orderDetail); //交易品目總筆數
        $temp_detail = array();
        for ($i = 0; $i < count($orderDetail); $i++) {
            $productName = $orderDetail[$i]->productName; //商品名稱
            $productAmount =intval($orderDetail[$i]->quantity); //購買數量
            $productPrice =intval($orderDetail[$i]->unitPrice); //商品單價
            array_push($temp_detail, $productName);
            array_push($temp_detail, $productAmount);
            array_push($temp_detail, $productPrice);
        }
        $interval = ':';
        $qrcontentLeft = $uniformInvoice . $taiwan_date .
            $randomNum . $salePrice . $sixteenbitPrice .
            $buyTaxNum . $sellTaxNum .
            $interval . $EncrypVerification . $interval . $ownArea .
            $interval . $transAmount . $interval . $orderAmount . $interval .
            $encodingParameter . $interval . $temp_detail[0];
        $qrcontentRight = '**';
        for ($i =1; $i < 5; $i++) {
            if(isset($temp_detail[$i])){
                $qrcontentRight = $qrcontentRight . $interval . $temp_detail[$i];
            }
            
        }
        $orderDetailId = $orderDetail[0]->orderDetailId;
        $options = DB::select("SELECT * FROM order_detail_option WHERE orderDetailId='$orderDetailId'");
        // return ['value'=>$options];
        $value['qrcontentLeft'] = $qrcontentLeft;
        $value['qrcontentRight'] = $qrcontentRight;
        //$value['qrcontentRight']='**:9:300:冰棒:3:100:羊肉爐:4:300:新鮮蔬菜:4:60:羊肉爐:1:300:新鮮蔬菜:3:60' ;
        $value['directoryName'] = $directoryName;
        $value['orderDetail'] = $orderDetail;
        $value['data'] = $data;
        $value['today'] = $today;
        $value['taiwan_year'] = $taiwan_year;
        $value['taiwan_month'] = $taiwan_month;
        $value['code39'] = $year . $month . $uniformInvoice . $randomNum;
        $value['storeName'] = $data['order'][0]->storeName;
        $value['storeBranch'] = $data['order'][0]->storeBranch;
        $value['storePhone'] = $data['order'][0]->storePhone;
        // $value['storeCategory'] = $data['order'][0]->storeBranch;
        $value['useTypeTitle'] = $data['order'][0]->useTypeTitle;//用餐方式
        $value['payMethod'] = $data['order'][0]->payMethod;//付款方式
        $value['totalDiscount'] = $data['order'][0]->totalDiscount;//折抵總額


        if(count($options)){

            $value['options'] = $options;

        }else{
            
            $value['options'] =[];

        }
        return $value;
    }
    public function reciept0($data)
    {
        date_default_timezone_set('Asia/Taipei');
        $today = date("Y-m-d H:i:s");
        $directoryName = $data['orderNum'];
        $uniformInvoice = $data['invoiceNum'];
        $year = date('Y') - 1911;
        $month = date("m");
        $day = date("d");

        $taiwan_date = $year . $month . $day;
        $taiwan_year = $year . '年';
        $taiwan_month = $month . "-0" . (intval($month) + 1) . "月";
        $randomNum = intval($this->randomString(4));
        $data['randomNum'] = $randomNum;
        $salePrice = base_convert($data['noTaxPrice'], 10, 16);
        $totalPrice = $data['totalPrice'];
        $sixteenbitPrice = base_convert($totalPrice, 10, 16);
        if ($data['buyType'] = 1) {
            $buyTaxNum = 00000000;
        } else {
            $buyTaxNum = $data['buyTax'];
        }
        $sellTaxNum = $data['sellTax'];
        $content = $uniformInvoice . $randomNum;
        $aesKey = "52DC135E35B676B933F1A7700E9B8B20";
        $EncrypVerification = $this->aes128_cbc_encrypt($aesKey, $content);
        $ownArea = '**********';
        $encodingParameter = 1;
        DB::connection('mysql');
        $orderDetail = DB::select("SELECT * FROM order_detail WHERE unitPrice>0 and orderNum=?", [$data['orderNum']]);
        $transAmount = count($orderDetail); //該張發票交易品目總筆數
        $orderAmount = count($orderDetail); //交易品目總筆數
        $temp_detail = array();
        for ($i = 0; $i < count($orderDetail); $i++) {
            $productName = $orderDetail[$i]->productName; //商品名稱
            $productAmount = $orderDetail[$i]->amount; //購買數量
            $productPrice = $orderDetail[$i]->price; //商品單價
            array_push($temp_detail, $productName);
            array_push($temp_detail, $productAmount);
            array_push($temp_detail, $productPrice);
        }
        $interval = ':';
        $qrcontentLeft = $uniformInvoice . $taiwan_date .
            $randomNum . $salePrice . $sixteenbitPrice .
            $buyTaxNum . $sellTaxNum .
            $interval . $EncrypVerification . $interval . $ownArea .
            $interval . $transAmount . $interval . $orderAmount . $interval .
            $encodingParameter . $interval . $temp_detail[0] . $interval . $temp_detail[1];
        $qrcontentRight = '**';
        for ($i = 2; $i < 5; $i++) {
            $qrcontentRight = $qrcontentRight . $interval . $temp_detail[$i];
        }
        $value['qrcontentLeft'] = $qrcontentLeft;
        $value['qrcontentRight'] = $qrcontentRight;
        //$value['qrcontentRight']='**:9:300:冰棒:3:100:羊肉爐:4:300:新鮮蔬菜:4:60:羊肉爐:1:300:新鮮蔬菜:3:60' ;
        $value['directoryName'] = $directoryName;
        $value['orderDetail'] = $orderDetail;
        $value['data'] = $data;
        $value['today'] = $today;
        $value['taiwan_year'] = $taiwan_year;
        $value['taiwan_month'] = $taiwan_month;
        $value['code39'] = $year . $month . $uniformInvoice . $randomNum;
        $value['storeName'] = $data['storeName'];
        $value['storePhone'] = $data['storePhone'];
        $value['storeCategory'] = $data['storeCategory'];
        return $value;
    }
    public function open_cashDrawer($ip =null,$port =null)
    {
        if($ip){
            
            $connector = new NetworkPrintConnector($ip);

        }else{
            
            $dest = env('INVOICE_SETTING_NAME1');
            $connector = new WindowsPrintConnector($dest);

        }
        // $dest = 'smb://DESKTOP-DNVDU43/XP-80C2';
        // print_r($connector);
        // $printer = new Printer($connector);
        $printer = new Printer($connector);
        $printer->pulse();
        $printer->close();
    }
    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    public function getMachine($machineCode){
        DB::connection('mysql');
        $detail= DB::select("SELECT * FROM machine WHERE machineCode=?", [$machineCode]);
        return $detail;
    }
}
