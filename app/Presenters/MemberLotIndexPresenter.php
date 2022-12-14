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
            return $this->lotStatusPresenter->present($lot->status).' <span uk-icon="icon: question" uk-tooltip="title: ??????????????????????????????; pos: top-right"></span>';
        } elseif($lot->status == 22) {
            if($lot->entrust == 0) {
                return '???????????? - '.$this->orderStatusPresenter->present($lot->order);
            } else {
                return '???????????? - ????????????????????????';
            }

        }  elseif($lot->status == 31) {
            $logisticInfo = $lot->logisticRecords->where('type', 2)->first();
            return $this->lotStatusPresenter->present($lot->status) . ' - ' . $logisticInfo->company_name . ': ' . $logisticInfo->tracking_code;
        } elseif($lot->status == 34) {
            $logisticInfo = $lot->logisticRecords->where('type', 1)->first();
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
            return '?????????';
        }
    }

    protected function getAction($lot)
    {
        switch (true) {
            case $lot->status == 1:
                return '<button class="uk-button custom-button-1 edit-lot" lotId="'.$lot->id.'">??????</button>';
            case $lot->status == 2:
                return '<button class="uk-button custom-button-1 application-logistic-info uk-text-nowrap" lotId="'.$lot->id.'">??????/??????????????????</button>';
            case $lot->status == 23 || $lot->status == 24 || $lot->status == 25:
                return '<button class="uk-button custom-button-1 unsold-lot-process" lotId="'.$lot->id.'">??????/?????? ??????</button>';
            case $lot->status == 22:
                $order = $lot->order;
                switch ($order->status) {
                    case 12:
                        if($lot->entrust == 0) {
                            $count = $order->messages->where('read_at', null)->where('target_user_id', 3)->count();
                            return '<span class="uk-badge" style="background-color: #d62828;">'.$count.'</span><button class="uk-button custom-button-1 communication" orderId="'.$lot->order->id.'">??????????????????</button>';
                        } else {
                            return '??? NT$'.number_format($lot->current_bid).' ??????';
                        }
                    case 13:
                        if($lot->entrust == 0) {
                            return '<button class="uk-button custom-button-1 show-shipping-info" orderId="'.$lot->order->id.'">??????????????????</button>';
                        } else {
                            return '??? NT$'.number_format($lot->current_bid).' ??????';
                        }
                    case 20:
                        if($lot->entrust == 0) {
                            return '<button class="uk-button custom-button-1 confirm-notice-arrival" lotId="'.$lot->id.'">????????????</button>
                                    <div id="confirm-notice-arrival" class="modal" lotId="'.$lot->id.'">
                                        <h2>?????? '.$lot->id.' ????????????????????????</h2>
                                        <div class="uk-flex uk-flex-right">
                                            <form method="post" action="'.route('account.orders.notice_arrival', $lot->order).'">
                                                <input type="hidden" name="_token" value="'.csrf_token().'" />
                                                <button class="uk-button custom-button-2 close-modal">??????</button>
                                                <button class="uk-button custom-button-1">??????</button>
                                            </form>
                                        </div>
                                    </div>';
                        } else {
                            return '??? NT$'.number_format($lot->current_bid).' ??????';
                        }
                    default:
                        return '??? NT$'.number_format($lot->current_bid).' ??????';
                }
            case $lot->status == 32:
                return '<button class="uk-button custom-button-1 returned-lot-logistic-info uk-text-nowrap" lotId="'.$lot->id.'">????????????????????????</button>';
            case $lot->status == 40:
                return '??? NT$'.number_format($lot->current_bid).' ??????';
            case $lot->status == 41:
                return '??? NT$'.number_format($lot->current_bid).' ?????????????????? NT$'.number_format($lot->order->owner_real_take).'?????????';
            default:
                return '<span>&nbsp;</span>';
        }
    }
}
