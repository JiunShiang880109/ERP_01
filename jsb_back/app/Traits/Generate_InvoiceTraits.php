<?php

namespace App\Traits;

use App\libraries\clsLibGTIN;
use DB;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
trait Generate_InvoiceTraits
{
    public function get_order($orderNum){
        DB::connection('mysql');
        $order= DB::select("SELECT * FROM orders A
        left join store B
        on A.storeId=B.id
        left join client C
        on C.clientId=A.clientId
        left join employee D
        on D.employeeId=A.casherId
        WHERE A.orderNum=?", [$orderNum]);
        return $order;
    }
    public function get_orderDetail($orderNum){
        DB::connection('mysql');
        $order= DB::select("SELECT * FROM order_detail WHERE orderNum=?", [$orderNum]);
        return $order;
    }
    public function produce_MIG($data){
        $input['invoiceNumber']=$data['order'][0]->invoiceNumberEn.$data['order'][0]->invoiceNumber;//成立MIG
        $input['orderNum']=$data['orderNum'];
        $input['sellTax']=$data['order'][0]->sellTax;
        $input['sellName']=$data['order'][0]->storeName;

        // $input['sellName']=$data['order'][0]->storeName.$data['order'][0]->storeBranch;
        $input['buyTax']=$data['order'][0]->buyTax;
        //業者通知消費者之個人識別碼資料(用於全民稽核功能)，共 4 位
        // ASCII 或 2 位全型中文可填消費者名稱或營業人自行規劃之消費者識
        $input['buyName']=$data['order'][0]->buyName;//一般消費者:0000,
        //07 一般稅額, 08 特種稅額
        $input['invoiceType']=$data['order'][0]->invoiceType;
        //0 非捐贈發票, 1 捐贈發票
        $input['donate']=$data['order'][0]->donate;
        $res=$this->generate_MIG($input);
        return $res;
    }
    public function write_MIG($res,$value,$data){
         //電子發票證明聯已列印註記
        //Y/N，PrintMark 為 Y 時載具// 類別號碼，載具顯碼 ID，載具隱碼 ID 必須為空白，捐贈註記必為 0
        // 消費者使用手機條碼索取(含要求登載買方統編發票)，則不論是否已列印紙本，其載具 類別號碼、載具顯碼 ID 和載具隱碼 ID 皆為必填
        $res['printMark']= 'Y';
        //發票防偽隨機碼
        $res['randomNum']=$value['data']['randomNum'];
        $res['salesAmount']=$data['order'][0]->salesAmount;//應稅銷售額合計
        $res['zeroTaxSalesAmount']=$data['order'][0]->zeroTaxSalesAmount;//免稅銷售額合計
        $res['freeTaxSalesAmount']=$data['order'][0]->freeTaxSalesAmount;//零稅率銷售額合計
        $res['taxType']=$data['order'][0]->taxType;//課稅別: 1 應稅, 2　零稅率, 3 免稅
        $res['taxRate']=0.05;//稅率
        $res['taxAmount']=ceil($data['order'][0]->taxAmount);//總稅金
        $res['totalAmount']=ceil($data['order'][0]->totalAmount);//總計
        return $res;
    }

}
