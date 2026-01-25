<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait SmilePayTraits
{
    // 開發票
    public static function SmilePayInvoice($url = null)
    {
        $url = "https://ssl.smse.com.tw/api_test/SPEinvoice_Storage.asp?Grvc=SEI1000034&Verify_key=9D73935693EE0237FABA6AB744E48661&Name=速買配&Phone=0900000000&Email=Test@testmailserver.net&Intype=07&TaxType=1&LoveKey=&DonateMark=0&Description=商品1|商品2&Quantity=5|8&UnitPrice=10|15&Unit=顆|條&Amount=50|120&ALLAmount=170&InvoiceDate=2022/5/26&InvoiceTime=15:33:33";
        $client = new Client(); //初始化客戶端
        $response = $client->get($url);

        $body = $response->getBody(); //獲取響應體，物件
        $bodyStr = (string)$body; //物件轉字串,這就是請求返回的結果
        return $bodyStr;
    }
   
}