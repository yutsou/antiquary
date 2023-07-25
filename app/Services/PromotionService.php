<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
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

    public function getPremiumRate()
    {
        $promotionStatus = Cache::get('promotion.status');

        if($promotionStatus === true) {
            $premiumRate = Cache::get('promotion.premium_rate');
            if($premiumRate < 1) {
                return ($premiumRate * 100).'%';
            } else {
                return 'NT$'.number_format($premiumRate);
            }
        } else {
            if(Auth::check()) {
                return (Auth::user()->premium_rate * 100) . '%';
            } else {
                return '10%';
            }
        }

    }
}
