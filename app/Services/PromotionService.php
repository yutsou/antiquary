<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PromotionService
{
    public function getPromotion()
    {
         return [Cache::get('promotion.status'), Cache::get('promotion.discount_rate')];
    }

    public function updatePromotion($request)
    {
        $this->initPromotion();
        if(isset($request->status)) {
            Cache::forever('promotion.status', true);
            Cache::forever('promotion.discount_rate', $request->discount_rate);
        }
    }

    private function initPromotion()
    {
        Cache::forget('promotion.status');
        Cache::forget('promotion.discount_rate');
    }

    public function getDiscountRate()
    {
        $promotionStatus = Cache::get('promotion.status');

        if($promotionStatus === true) {
            $discountRate = Cache::get('promotion.discount_rate');
            return floatval($discountRate);
        } else {
            return null;
        }

    }
}
