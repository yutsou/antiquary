<?php

namespace App\Presenters;

class MethodPresenter
{
    public function transferDeliveryMethod($deliveryMethod)
    {
        return match ($deliveryMethod) {
            0 => '面交',
            1 => '宅配',
            2 => '境外物流',
            default => ''
        };

    }

    public function transferPaymentMethod($paymentMethod)
    {
        return match ($paymentMethod) {
            0 => '信用卡付款',
            1 => 'ATM轉帳',
            default => ''
        };
    }
}
