<?php

namespace App\Presenters;


use Illuminate\Support\Facades\Auth;

class MemberLotIndexPresenter
{
    private $orderStatusPresenter, $lotStatusPresenter;

    public function __construct(OrderStatusPresenter $orderStatusPresenter, LotStatusPresenter $lotStatusPresenter) {
        $this->orderStatusPresenter = $orderStatusPresenter;
        $this->lotStatusPresenter = $lotStatusPresenter;
    }

    public function present($lot)
    {
        return '
            <a href="'.route('account.applications.edit', $lot).'" class="uk-card uk-card-default uk-card-hover uk-grid-collapse uk-margin custom-link-mute" lotId="'.$lot->id.'" style="-webkit-tap-highlight-color: transparent;" uk-grid>
                <div class="uk-card-media-left uk-cover-container uk-width-1-3 uk-width-1-5@m ">
                    <img src="'. $lot->images->first()->url .'" alt="" uk-cover>
                </div>
                <div class="uk-width-expand">
                    <div class="uk-card-body" style="padding: 20px 20px">
                        <div class="uk-margin uk-text-right">
                            <label>'.$this->getStatus($lot).'</label>
                        </div>
                        <hr>
                        <h3 class="uk-card-title uk-text-truncate" style="margin: 0 0 0 0">No.'.$lot->id.' - '.$this->getLotName($lot).'  </h3>
                        <hr>
                        <div class="uk-margin uk-flex uk-flex-right">
                            <div class="uk-grid-small" uk-grid>
                                <div>
                                    <div class="uk-margin uk-flex uk-flex-right">
                                        '.$this->getAction($lot).'
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        ';
    }

    protected function getStatus($lot)
    {
        if($lot->status == 1) {
            return $this->lotStatusPresenter->present($lot->status).' <span uk-icon="icon: question" uk-tooltip="title: 點擊修改查看專家建議; pos: top-right"></span>';
        } elseif($lot->status == 22) {
            if($lot->entrust == 0) {
                return '競標成功 - '.$this->orderStatusPresenter->present($lot->order);
            } else {
                return '競標成功 - 等待買家完成交易';
            }

        }  elseif($lot->status == 23) {
            $logisticInfo = $lot->logisticRecords->where('type', 2)->first();
            return $this->lotStatusPresenter->present($lot->status) . ' - ' . $logisticInfo->company_name . ': ' . $logisticInfo->tracking_code;
        }
        else {
            return $this->lotStatusPresenter->present($lot->status);
        }
    }

    protected function getLotName($lot)
    {
        if($lot->name !== null) {
            return $lot->name;
        } else {
            return '未定義';
        }
    }

    protected function getAction($lot)
    {
        switch (true) {
            case $lot->status == 1:
                return '<button class="uk-button custom-button-1 edit-lot" lotId="'.$lot->id.'">修改</button>';
            case $lot->status == 2:
                return '<button class="uk-button custom-button-1 application-logistic-info uk-text-nowrap" lotId="'.$lot->id.'">查看/填寫運送資訊</button>';
            case $lot->status == 23 || $lot->status == 24:
                return '<button class="uk-button custom-button-1 unsold-lot-process" lotId="'.$lot->id.'">流標處理</button>';
            case $lot->status == 22:
                $order = $lot->order;
                switch ($order->status) {
                    case 12:
                        if($lot->entrust == 0) {
                            $count = $order->messages->where('read_at', null)->where('target_user_id', 3)->count();
                            return '<span class="uk-badge" style="background-color: #d62828;">'.$count.'</span><button class="uk-button custom-button-1 communication" orderId="'.$lot->order->id.'">協調面交地點</button>';
                        } else {
                            return '以 NT$'.number_format($lot->current_bid).' 賣出';
                        }
                    case 13:
                        if($lot->entrust == 0) {
                            return '<button class="uk-button custom-button-1 show-shipping-info" orderId="'.$lot->order->id.'">查看運送資料</button>';
                        } else {
                            return '以 NT$'.number_format($lot->current_bid).' 賣出';
                        }
                    case 20:
                        if($lot->entrust == 0) {
                            return '<button class="uk-button custom-button-1 confirm-notice-arrival" lotId="'.$lot->id.'">通知到貨</button>
                                    <div id="confirm-notice-arrival" class="modal" lotId="'.$lot->id.'">
                                        <h2>物品 '.$lot->id.' 確定通知出貨嗎？</h2>
                                        <div class="uk-flex uk-flex-right">
                                            <form method="post" action="'.route('account.orders.notice_arrival', $lot->order).'">
                                                <input type="hidden" name="_token" value="'.csrf_token().'" />
                                                <button class="uk-button custom-button-2 close-modal">取消</button>
                                                <button class="uk-button custom-button-1">確定</button>
                                            </form>
                                        </div>
                                    </div>';
                        } else {
                            return '以 NT$'.number_format($lot->current_bid).' 賣出';
                        }
                    default:
                        return '以 NT$'.number_format($lot->current_bid).' 賣出';
                }
            case $lot->status == 40:
                return '以 NT$'.number_format($lot->current_bid).' 賣出';
            case $lot->status == 41:
                return '以 NT$'.number_format($lot->current_bid).' 賣出，已匯款 NT$'.number_format($lot->order->owner_real_take).'給賣家';
            default:
                return '<span>&nbsp;</span>';
        }
    }
}
