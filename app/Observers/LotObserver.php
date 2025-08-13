<?php

namespace App\Observers;

use App\Models\Lot;
use Illuminate\Support\Facades\Artisan;

class LotObserver
{
    /**
     * Handle the Lot "created" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function created(Lot $lot)
    {
        $this->regenerateSitemap();
    }

    /**
     * Handle the Lot "updated" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function updated(Lot $lot)
    {
        // 只有當狀態改變為已發布狀態時才重新生成 sitemap
        if (in_array($lot->status, [20, 21, 61]) && $lot->wasChanged('status')) {
            $this->regenerateSitemap();
        }

        // 如果拍賣開始時間改變，也重新生成 sitemap
        if ($lot->wasChanged('auction_start_at')) {
            $this->regenerateSitemap();
        }
    }

    /**
     * Handle the Lot "deleted" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function deleted(Lot $lot)
    {
        $this->regenerateSitemap();
    }

    /**
     * Handle the Lot "restored" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function restored(Lot $lot)
    {
        $this->regenerateSitemap();
    }

    /**
     * Handle the Lot "force deleted" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function forceDeleted(Lot $lot)
    {
        $this->regenerateSitemap();
    }

    /**
     * 重新生成 sitemap
     */
    private function regenerateSitemap()
    {
        try {
            Artisan::call('sitemap:generate');
        } catch (\Exception $e) {
            // 記錄錯誤但不中斷流程
            \Log::error('Failed to regenerate sitemap: ' . $e->getMessage());
        }
    }
}
