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

    public function lotPresent($startTime, $endTime)
    {
        $startTime = Carbon::create($startTime);
        $endTime = Carbon::create($endTime);

        if(Carbon::now()->lessThan($startTime)){
            return '於 '.$startTime->diffForHumans(Carbon::now()).' 開始';
        } else {
            if(Carbon::now()->lessThan($endTime)){
                return '於 '.$endTime->diffForHumans(Carbon::now()).' 結束';
            } else {
                return '拍賣已結束';
            }
        }
    }
}
