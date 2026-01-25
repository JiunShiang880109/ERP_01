<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\LinePayTraits; //發票

class PayController extends Controller
{
    use LinePayTraits;

    public function LinePayOfflineSubmitOrder(Request $req)
    {
        // return $req;
        $productName = $req->productName; //商品名稱
        $amount = $req->amount; //總價
        $orderId = $req->orderId; //不可重複
        $oneTimeKey = $req->oneTimeKey; //20分鐘失效需更新
        return $this->Linepay_offline_SubmitOrder($productName, $amount, $orderId, $oneTimeKey);
    }

}
