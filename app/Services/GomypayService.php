<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class GomypayService
{
    public function checkTransactionStatus($request, $order)
    {
        $eOrderNumber = str($request->e_orderno);
        $systemOrderId = $request->OrderID;
        $merchantId = config('services.gomypay.merchant_id');
        $price = str(intval($order->total));
        $key = config('services.gomypay.hash_key');

        $strCheck = $request->str_check;
        $ourStrCheck = md5('1'.$eOrderNumber.$merchantId.$price.$systemOrderId.$key);

        if($strCheck === $ourStrCheck) {
            return 1;
        } else {
            return 0;
        }
    }
    public function payGomypayReturn(Request $request)
    {
        dd($request->all());
    }

    public function payGompayCallback(Request $request)
    {
        Log::channel('gomypay')->info($request->toArray());
        return response('success', 200);
    }
}
