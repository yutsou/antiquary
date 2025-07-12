<?php

namespace App\Presenters;

use Carbon\Carbon;

class CarbonPresenter
{
    public function auctionPresent($startTime, $endTime)
    {
        $startTime = Carbon::create($startTime);
        $endTime = Carbon::create($endTime);

        if(Carbon::now()->lessThan($startTime)){
            return '於 '.$startTime->diffForHumans(Carbon::now()).' 開始';
        } else {
            return '拍賣進行中';
        }
    }

    public function lotPresent($singleLotId, $endTime)
    {
        // 處理商店直賣商品（沒有結束時間）
        if ($endTime === null) {
            return 'Antiquary 精選';
        }

        if(Carbon::now()->lessThan($endTime)){
            if(Carbon::now()->diffInHours($endTime)<24) {
                return '
                    <div class="lot-card-countdowns" id="countdown-'.$singleLotId.'" end-at="'.$endTime->toIso8601ZuluString("millisecond").'"></div>
                ';
            } else {
                return '於 '.$endTime->diffForHumans(Carbon::now(), ['parts' => 3, 'join' => true]).' 結束';
            }
        } else {
            return '拍賣已結束';
        }
    }

    public function lineCardPresent($endTime)
    {
        if(Carbon::now()->lessThan($endTime)){
            return '於 '.$endTime->diffForHumans(Carbon::now(), ['parts' => 3, 'join' => true]).' 結束';
        } else {
            return '拍賣已結束';
        }
    }
}
