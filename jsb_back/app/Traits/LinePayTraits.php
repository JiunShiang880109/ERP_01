<?php

namespace App\Traits;

trait LinePayTraits
{

    public function Linepay_online_submitorder($shopName, $orderId, $orderList, $confirmUrl, $cancelUrl)
    {
        $channelId = env('LINEPAY_channelId');
        $Secretkey = env('LINEPAY_SecretKey');
        $requestUrl = '/v3/payments/request';
        $nonce = $this->create_uuid();

        $products = [];
        foreach ($orderList as $order) {
            $products[] = [
                'name' => $order['product_title'],
                'quantity' => $order['count'],
                'price' => (float)$order['price'], //必須單價
            ];
        }
        $amount = $this->calc_linepay_online_productsAmount($products);
        $order = [
            'amount' => $amount, //付款金額(總價)
            'currency' => 'TWD',
            'orderId' => $orderId, //不可重複
            'packages' => [
                [
                    'id' => $orderId,
                    'amount' => $amount, //訂單合計
                    'name' => $shopName, //店家名稱
                    'userFee' => 0,
                    'products' => $products,
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => $confirmUrl,
                'cancelUrl' => $cancelUrl,
            ],
        ];

        // return $order;

        $data = $Secretkey . $requestUrl . json_encode($order) . $nonce;
        $hash = hash_hmac('sha256', $data, $Secretkey, true);

        $hmacBase64 = base64_encode($hash);

        $headers = array(
            'Content-Type:application/json',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-Authorization-Nonce:' . $nonce,
            'X-LINE-Authorization:' . $hmacBase64,
        );

        $url = env('LINEPAY_URL') . $requestUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
        $output = curl_exec($ch);
        return $output;
    }

    //須拿交易號(transactionId)才能請款
    public function Linepay_online_Confirm($transactionId, $amount)
    {
        $channelId = env('LINEPAY_channelId');
        $key = env('LINEPAY_SecretKey');
        $requestUrl = '/v3/payments/' . $transactionId . '/confirm';
        $nonce = $this->create_uuid();

        $url = env('LINEPAY_URL') . $requestUrl;
        $body = [
            'amount' => $amount,
            'currency' => 'TWD',
        ];

        $data = $key . $requestUrl . json_encode($body) . $nonce;
        $hash = hash_hmac('sha256', $data, $key, true);
        $hmacBase64 = base64_encode($hash);

        $headers = array(
            'Content-Type:application/json',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-Authorization-Nonce:' . $nonce,
            'X-LINE-Authorization:' . $hmacBase64,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $output = curl_exec($ch);
        return $output;
    }

    public function Linepay_offline_SubmitOrder($productName, $amount, $orderId, $oneTimeKey)
    {
        $channelId = env('LINEPAY_channelId');
        $key = env('LINEPAY_SecretKey');
        $url = env('LINEPAY_URL') . '/v2/payments/oneTimeKeys/pay';

        $headers = array(
            'Content-Type:application/json',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-ChannelSecret:' . $key,
        );

        $body = [
            'productName' => $productName,
            'amount' => $amount,
            'currency' => 'TWD',
            'orderId' => $orderId, //不可重複
            'oneTimeKey' => $oneTimeKey, //20分鐘失效需更新
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $output = curl_exec($ch);
        return $output;
    }

    public function Linepay_offline_CheckOrder($orderId)
    {
        $channelId = env('LINEPAY_channelId');
        $key = env('LINEPAY_SecretKey');
        $url = env('LINEPAY_URL') . '/v2/payments/orders/' . $orderId . '/check';

        $headers = array(
            'Content-Type:application/json; charset=UTF-8',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-ChannelSecret:' . $key,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        return $output;
    }

    public function Linepay_offline_ConfirmOrder($orderId, $transactionId)
    {
        $channelId = env('LINEPAY_channelId');
        $key = env('LINEPAY_SecretKey');
        $url = env('LINEPAY_URL') . '/v2/payments?orderId=' . $orderId . '&transactionId=' . $transactionId;

        $headers = array(
            'Content-Type:application/json; charset=UTF-8',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-ChannelSecret:' . $key,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        return $output;
    }

    public static function create_uuid($prefix = "")
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    //計算訂單合計
    public static function calc_linepay_online_productsAmount($products)
    {
        $amount = 0;
        foreach ($products as $key => $product) {
            $amount += $product['price'] * $product['quantity'];
        }
        return $amount;
    }
}
