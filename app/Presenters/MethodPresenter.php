<?php

namespace App\Presenters;

class MethodPresenter
{
    public function transferDeliveryMethod($deliveryMethod)
    {
        return match ((int)$deliveryMethod) {
            0 => '面交',
            1 => '宅配',
            2 => '境外物流',
            default => '未知運送方式'
        };
    }

    public function transferPaymentMethod($paymentMethod)
    {
        return match ((int)$paymentMethod) {
            0 => '信用卡付款',
            1 => 'ATM轉帳',
            2 => 'LINE Pay',
            default => '未知付款方式'
        };
    }
}
