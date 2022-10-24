<?php

namespace App\Presenters;

use Illuminate\Support\Facades\Auth;

class AuctioneerOrderActionPresenter
{
    public function modal($buttonName, $content, $modalName, $orderId, $actionUrl, $redirectUrl)
    {
        return
        '
        <div id="'.$modalName.'-'.$orderId.'" class="modal">
            <h2>訂單'.$orderId.'，'.$content.'</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <button class="uk-button uk-button-primary '.$modalName.'" type="button" orderId="'.$orderId.'" actionUrl="'.$actionUrl.'" redirectUrl="'.$redirectUrl.'">確定</button>
            </p>
        </div>
        <a href="#'.$modalName.'-'.$orderId.'" rel="modal:open" class="uk-button custom-button-1">'.$buttonName.'</a>
        ';
    }

    public function present($order)
    {
        switch ($order->status) {
            case 11:
                return $this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index'));
            case 12:
                if($order->lot->entrust == 0){
                    return '<p class="uk-text-right"><a href="'.route('auctioneer.orders.member_chatroom_show', $order).'" class="uk-button custom-button-1">查看對話</a></p>';

                } else {
                    return '<a href="' . route('auctioneer.orders.chatroom_show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調</a>';
                }
            case 40:
                return $this->modal('匯款給賣家', '匯款給賣家嗎？', 'notice-remit', $order->id, route('auctioneer.orders.notice_remit', $order), route('auctioneer.orders.index'));
            case 13:
                if($order->lot->entrust == 0){
                    return '';
                } else {
                    return '<div class="uk-grid-small" uk-grid>
                                <div>
                                    <input type="text" class="uk-input uk-form-width-small" id="logistics-name-'.$order->id.'" placeholder="物流公司">
                                </div>
                                <div>
                                    <input type="text" class="uk-input uk-form-width-medium" id="tracking-code-'.$order->id.'" placeholder="物流追蹤碼">
                                </div>
                                <div>
                                    '.$this->modal('通知出貨', '通知出貨嗎？', 'notice-shipping', $order->id, route('auctioneer.orders.notice_shipping', $order), route('auctioneer.orders.index')).'
                                </div>
                            </div>';
                }

            case 20:
                return $this->modal('通知到貨', '通知到貨嗎？', 'notice-arrival', $order->id, route('auctioneer.orders.notice_arrival', $order), route('auctioneer.orders.index'));
            default:
                return '';


        }

        /*if($order->lot->entrust === 0) {
            if($order->delivery_method === 0) {
                return match ($order->status) {
                    11=>$this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index')),
                    12=>'<p class="uk-text-right"><a href="'.route('auctioneer.orders.member_chatroom_show', $order).'" class="uk-button custom-button-1">查看對話</a></p>',
                    40=>$this->modal('匯款給賣家', '匯款給賣家嗎？', 'notice-remit', $order->id, route('auctioneer.orders.notice_remit', $order), route('auctioneer.orders.index')),
                    default => '',
                };
            } elseif($order->delivery_method === null) {
                return match ($order->status) {
                    default => '',
                };
            } else {
                return match ($order->status) {
                    11=>$this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index')),
                    40 =>$this->modal('匯款給賣家', '匯款給賣家嗎？', 'notice-remit', $order->id, route('auctioneer.orders.notice_remit', $order), route('auctioneer.orders.index')),
                    default => '',
                };
            }
        } else {
            if($order->delivery_method === 0) {
                return match ($order->status) {
                    11=>$this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index')),
                    12=>'<a href="' . route('auctioneer.orders.chatroom_show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調</a>',
                    40=>$this->modal('匯款給賣家', '匯款給賣家嗎？', 'notice-remit', $order->id, route('auctioneer.orders.notice_remit', $order), route('auctioneer.orders.index')),
                    default => '',
                };
            } elseif($order->delivery_method === null) {
                return match ($order->status) {
                    default => '',
                };
            } else {
                return match ($order->status) {
                    11=>$this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index')),
                    13 => '<div class="uk-grid-small" uk-grid>
                                <div>
                                    <input type="text" class="uk-input uk-form-width-small" id="logistics-name-'.$order->id.'" placeholder="物流公司">
                                </div>
                                <div>
                                    <input type="text" class="uk-input uk-form-width-medium" id="tracking-code-'.$order->id.'" placeholder="物流追蹤碼">
                                </div>
                                <div>
                                    '.$this->modal('通知出貨', '通知出貨嗎？', 'notice-shipping', $order->id, route('auctioneer.orders.notice_shipping', $order), route('auctioneer.orders.index')).'
                                </div>
                            </div>',
                    20 => $this->modal('通知到貨', '通知到貨嗎？', 'notice-arrival', $order->id, route('auctioneer.orders.notice_arrival', $order), route('auctioneer.orders.index')),
                    40 =>$this->modal('匯款給賣家', '匯款給賣家嗎？', 'notice-remit', $order->id, route('auctioneer.orders.notice_remit', $order), route('auctioneer.orders.index')),
                    default => '',
                };
            }
        }*/
    }
}
