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
            'MerchantTradeNo' => 'ANT' . date('YmdHis') . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'TotalAmount' => intval($order->total),
            'TradeDesc' => '商品購買',
            'ItemName' => $this->generateItemName($order),
            'ReturnURL' => route('shop.pay.ecpay.receive'),
            'ClientBackURL' => route('shop.pay.ecpay.order_receive'),
            'OrderResultURL' => route('shop.pay.ecpay.order_receive'),
            'CustomField1' => $order->id,
            'PaymentType' => 'aio',
            'ChoosePayment' => 'Credit',
            'EncryptType' => 1,
        ];
        $action = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';

        echo $autoSubmitFormService->generate($input, $action);
    }

    private function generateItemName($order)
    {
        $itemNames = [];
        foreach ($order->orderItems as $orderItem) {
            // 使用 orderItem 的單價和數量資訊
            $itemNames[] = $orderItem->lot->name . ' x' . $orderItem->quantity . ' NT$' . number_format($orderItem->price) . '=' . number_format($orderItem->subtotal);
        }

        $itemNameString = implode('#', $itemNames);
        $itemNameString .= '#運費 NT$' . number_format($order->delivery_cost);

        return $itemNameString;
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
