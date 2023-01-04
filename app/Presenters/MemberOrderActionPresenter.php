<?php

namespace App\Presenters;

use Illuminate\Support\Facades\Auth;

class MemberOrderActionPresenter
{
    public function modal($buttonName, $content, $modalName, $orderId, $actionUrl, $redirectUrl)
    {
        return
            '
        <div id="'.$modalName.'-'.$orderId.'" class="modal">
            <h2>訂單'.$orderId.'，'.$content.'</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <button class="uk-button custom-button-1 '.$modalName.'" type="button" orderId="'.$orderId.'" actionUrl="'.$actionUrl.'" redirectUrl="'.$redirectUrl.'">確定</button>
            </p>
        </div>
        <a href="#'.$modalName.'-'.$orderId.'" rel="modal:open" class="uk-button custom-button-1">'.$buttonName.'</a>
        ';
    }

    public function indexPresent($order)
    {
        switch ($order->status) {
            case 0:
                return '<a href="' . route('account.orders.edit', $order) . '" class="uk-button custom-button-1">確認訂單</a>';
            case 10:
                if($order->payment_method == 0) {
                    return '<a href="'.route('account.orders.pay', $order).'" class="uk-button custom-button-1">前往付款</a>';
                } else {
                    return '<a href="'.route('account.atm_pay_info.show', $order).'" class="uk-button custom-button-1">查看匯款資訊</a>';
                }

            case 12:
                return '
                    <div class="uk-grid-small uk-flex uk-flex-right" uk-grid>
                        <div>
                            <a href="' . route('mart.chatroom.show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調面交地點時間</a>
                        </div>
                        <div>
                            <a href="'.route('account.orders.show', $order).'" class="uk-button custom-button-1">完成訂單</a>
                        </div>
                    </div>
                    ';
            case 21:
                return '
                    <div class="uk-grid-small uk-flex uk-flex-right" uk-grid>
                        <div>
                            <a href="'.route('account.orders.show', $order).'" class="uk-button custom-button-1">完成訂單</a>
                        </div>
                    </div>
                    ';
            default:
                return '<a>&nbsp;</a>';
        }
    }

    public function showPresent($order)
    {

        return match ($order->status) {
            0=>'<a href="' . route('account.orders.edit', $order) . '" class="uk-button custom-button-1">確認訂單</a>',
            1 => '
                    <a href="'.route('account.atm_pay_info.show', $order).'" class="uk-button custom-button-1">匯款資訊</a>
                ',
            10=>'<a href="'.route('account.atm_pay_info.show', $order).'" class="uk-button custom-button-1">查看匯款資訊</a>',
            12=>'
                <div class="uk-grid-small" uk-grid>
                    <div>
                        <a href="' . route('mart.chatroom.show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調面交地點時間</a>
                    </div>
                    <div>
                        '.$this->modal('完成訂單', '確認完成訂單嗎？', 'complete-order', $order->id, route('account.orders.complete', $order), route('account.orders.show', $order)).'
                    </div>
                </div>
                ',
            21 =>$this->modal('完成訂單', '確認完成訂單嗎？', 'complete-order', $order->id, route('account.orders.complete', $order), route('account.orders.show', $order)),
            default => '<a>&nbsp;</a>',
        };

    }
}
