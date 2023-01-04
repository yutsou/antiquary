<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PromotionService
{
    public function getPromotion()
    {
         return [Cache::get('promotion.status'), Cache::get('promotion.commission_rate'), Cache::get('promotion.premium_rate')];
    }

    public function updatePromotion($request)
    {
        $this->initPromotion();
        if(isset($request->status)) {
            Cache::forever('promotion.status', true);
            Cache::forever('promotion.commission_rate', $request->commission_rate);
            Cache::forever('promotion.premium_rate', $request->premium_rate);
        }
    }

    private function initPromotion()
    {
        Cache::forget('promotion.status');
        Cache::forget('promotion.commission_rate');
        Cache::forget('promotion.premium_rate');
    }
}
