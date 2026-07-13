<?php

namespace App\Presenters;

use Illuminate\Support\Facades\Auth;

class AuctioneerOrderActionPresenter
{
    public function modal($buttonName, $content, $modalName, $orderId, $actionUrl, $redirectUrl, $type=null)
    {
        if ($type == null) {
            $color = "custom-button-1";
        } else {
            $color = "custom-button-2";
        }
        return
        '
        <div id="'.$modalName.'-'.$orderId.'" class="modal">
            <h2>訂單'.$orderId.'，'.$content.'</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="custom-button uk-button uk-button-default">取消</a>
                <button class="uk-button custom-button-1 uk-button-primary '.$modalName.'" type="button" orderId="'.$orderId.'" actionUrl="'.$actionUrl.'" redirectUrl="'.$redirectUrl.'">確定</button>
            </p>
        </div>
        <a href="#'.$modalName.'-'.$orderId.'" rel="modal:open" class="uk-button '.$color.'">'.$buttonName.'</a>
        ';
    }

    public function refundModal($orderId, $paymentMethod)
    {
        $linePayOption = '';
        if ($paymentMethod == 2) {
            $linePayOption = '
                <div class="uk-margin">
                    <label class="uk-form-label">退款方式</label>
                    <div class="uk-form-controls">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="refund_method" value="line_pay" checked>
                            LINE Pay 退款
                        </label>
                        <br>
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="refund_method" value="bank_transfer">
                            銀行轉帳退款
                        </label>
                    </div>
                </div>';
        } else {
            $linePayOption = '
                <div class="uk-margin">
                    <label class="uk-form-label">退款方式</label>
                    <div class="uk-form-controls">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="refund_method" value="bank_transfer" checked>
                            銀行轉帳退款
                        </label>
                    </div>
                </div>';
        }

        return
        '
        <div id="refund-modal-'.$orderId.'" class="modal">
            <h2>訂單'.$orderId.'，選擇退款方式</h2>

                <div class="uk-margin">
                    <label class="uk-form-label">退款金額 (NT$)</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="number" name="refund_amount" step="0.01" min="0" required placeholder="請輸入退款金額">
                    </div>
                </div>
                '.$linePayOption.'
                <div class="uk-margin">
                    <label class="uk-form-label">備注 (選填)</label>
                    <div class="uk-form-controls">
                        <textarea class="uk-textarea" name="refund_remark" rows="3" placeholder="請輸入退款備注，例如：客戶要求退款原因、特殊處理說明等"></textarea>
                    </div>
                </div>
                <p class="uk-text-right">
                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                    <a class="uk-button custom-button-1 uk-button-primary confirm-refund" orderId="'.$orderId.'" actionUrl="'.route('auctioneer.orders.confirm_refund', $orderId).'" redirectUrl="'.route('auctioneer.orders.index').'">確定退款</a>
                </p>

        </div>
        <a href="#refund-modal-'.$orderId.'" rel="modal:open" class="uk-button custom-button-3">處理退款</a>
        ';
    }

    public function present($order)
    {

        $count = $order->messages->where('read_at', null)->where('user_id','!=', Auth::user()->id)->count();
        if ($count == 0) {
            $comment = '<a href="' . route('auctioneer.orders.chatroom_show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調</a>';
        } else {
            $comment = '<span class="uk-badge" style="background-color: #d62828;">'.$count.'</span><a href="' . route('auctioneer.orders.chatroom_show', $order) . '" class="uk-button custom-button-1"><span uk-icon="commenting"></span> 協調</a>';
        }

        $actions = [];

        if ($order->status < 60) {
            $actions[] = $this->modal('取消訂單', '確認取消訂單嗎？', 'cancel-order', $order->id, route('auctioneer.orders.cancel', $order), route('auctioneer.orders.index'), 2);
        }

        $actions[] = $comment;

        switch (true) {
            case ($order->status == 11):
                array_unshift($actions, $this->modal('通知已收到匯款', '通知已收到匯款嗎？', 'notice-confirm-atm-pay', $order->id, route('auctioneer.orders.notice_confirm_atm_pay', $order), route('auctioneer.orders.index')));
                break;
            case ($order->status == 12):
                $firstItem = $order->orderItems->first();
                if (!$firstItem) {
                    break;
                }
                if($firstItem->lot->entrust == 0){
                    array_unshift($actions, '<a href="'.route('auctioneer.orders.member_chatroom_show', $order).'" class="uk-button custom-button-1">查看對話</a>');
                } else {
                    array_unshift($actions, $this->modal('完成訂單', '確認完成訂單嗎？', 'complete-order', $order->id, route('auctioneer.orders.complete', $order), route('auctioneer.orders.index')));
                }
                break;
            case ($order->status == 13):
                $firstItem = $order->orderItems->first();
                if (!$firstItem) {
                    break;
                }
                if($firstItem->lot->entrust == 0){
                    break;
                } else {
                    $shippingButton = $this->modal('通知出貨', '通知出貨嗎？', 'notice-shipping', $order->id, route('auctioneer.orders.notice_shipping', $order), route('auctioneer.orders.index'));
                    $trackingCodeInput = '<input type="text" class="uk-input uk-form-width-medium" id="tracking-code-'.$order->id.'" placeholder="物流追蹤碼" required>';
                    $logisticsNameInput = '<input type="text" class="uk-input uk-form-width-small" id="logistics-name-'.$order->id.'" placeholder="物流公司" required>';
                    array_unshift($actions, $shippingButton);
                    array_unshift($actions, $trackingCodeInput);
                    array_unshift($actions, $logisticsNameInput);
                }
                break;
            case ($order->status == 20):
                array_unshift($actions, $this->modal('通知到貨', '通知到貨嗎？', 'notice-arrival', $order->id, route('auctioneer.orders.notice_arrival', $order), route('auctioneer.orders.index')));
                break;
            case ($order->status == 21):
                array_unshift($actions, $this->modal('完成訂單', '確認完成訂單嗎？', 'complete-order', $order->id, route('auctioneer.orders.complete', $order), route('auctioneer.orders.index')));
                break;
            case ($order->status == 51):
                if($order->payment_method == 1) {
                    array_unshift($actions, $this->modal('讓賣家填寫匯款資訊', '確認讓賣家填寫匯款資訊嗎？', 'confirm-refill-transfer-info', $order->id, route('auctioneer.orders.confirm_refill_transfer_info', $order), route('auctioneer.orders.index')));
                    array_unshift($actions, $this->modal('設為棄標', '確認設為棄標嗎？', 'set-withdrawal-bid', $order->id, route('auctioneer.orders.set_withdrawal_bid', $order), route('auctioneer.orders.index'), 2));
                }
                break;
            case ($order->status == 53):
                array_unshift($actions, $this->modal('確認已付款', '確認已付款嗎？', 'confirm-paid', $order->id, route('auctioneer.orders.confirm_paid', $order), route('auctioneer.orders.index')));
                array_unshift($actions, $this->modal('設為棄標', '確認設為棄標嗎？', 'set-withdrawal-bid', $order->id, route('auctioneer.orders.set_withdrawal_bid', $order), route('auctioneer.orders.index'), 2));
                break;
            case ($order->status == 52):
                array_unshift($actions, $this->modal('確認已付款', '確認已付款嗎？', 'confirm-paid', $order->id, route('auctioneer.orders.confirm_paid', $order), route('auctioneer.orders.index')));
                array_unshift($actions, $this->modal('設為棄標', '確認設為棄標嗎？', 'set-withdrawal-bid', $order->id, route('auctioneer.orders.set_withdrawal_bid', $order), route('auctioneer.orders.index'), 2));
                break;
            case ($order->status == 60):
                array_unshift($actions, $this->refundModal($order->id, $order->payment_method));
                break;
            default:
                break;
        }

        if (empty($actions)) {
            return '';
        }

        $output = '<div class="uk-grid-small uk-flex-right" uk-grid>';
        foreach ($actions as $action) {
            $output .= '<div>' . $action . '</div>';
        }
        $output .= '</div>';

        return $output;
    }
}
