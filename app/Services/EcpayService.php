<?php

namespace App\Services;

use Ecpay\Sdk\Exceptions\RtnException;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use Ecpay\Sdk\Services\UrlService;
use Illuminate\Support\Facades\Log;

class EcpayService
{
    public function creditCardPay($order)
    {
        $factory = new Factory([
            'hashKey' => config('services.ecpay.hash_key'),
            'hashIv' => config('services.ecpay.hash_iv'),
        ]);
        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $input = [
            'MerchantID' => config('services.ecpay.merchant_id'),
            'MerchantTradeNo' => 'Sold' . time(),
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'PaymentType' => 'aio',
            'TotalAmount' => intval($order->total),
            'TradeDesc' => UrlService::ecpayUrlEncode('交易描述範例'),
            'ItemName' => $order->lot->name.' NT$'.number_format($order->subtotal).'#'.'運費'.' NT$'.number_format($order->delivery_cost),
            'ReturnURL' => route('shop.pay.ecpay.receive'),
            'ClientBackURL' => route('account.orders.show', $order),
            'OrderResultURL'=> route('shop.pay.ecpay.order_receive'),
            'ChoosePayment' => 'Credit',
            'EncryptType' => 1,
            'CustomField1' => $order->id
        ];
        $action = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';

        echo $autoSubmitFormService->generate($input, $action);
    }

    public function checkMacValue($request, $hashMethod)
    {
        try {
            if($hashMethod === 'md5')#sha256
            {
                $factory = new Factory([
                    'hashKey' => config('services.ecpay.hash_key'),
                    'hashIv' => config('services.ecpay.hash_iv'),
                    'hashMethod' => $hashMethod,
                ]);
            } else {
                $factory = new Factory([
                    'hashKey' => config('services.ecpay.hash_key'),
                    'hashIv' => config('services.ecpay.hash_iv'),
                    'hashMethod' => $hashMethod,
                ]);
            }

            $checkoutResponse = $factory->create(VerifiedArrayResponse::class);

            Log::channel('ecpay')->info($checkoutResponse->get($request->toArray()));
            return true;
        } catch (RtnException $e) {
            Log::channel('ecpay')->warning('(' . $e->getCode() . ')' . $e->getMessage() . PHP_EOL);
            return false;
        }
    }
}
