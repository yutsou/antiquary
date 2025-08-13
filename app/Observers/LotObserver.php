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
        $this->logSitemapUpdate('created', $lot);
    }

    /**
     * Handle the Lot "updated" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function updated(Lot $lot)
    {
        // 只有當狀態改變為已發布狀態時才記錄
        if (in_array($lot->status, [20, 21, 61]) && $lot->wasChanged('status')) {
            $this->logSitemapUpdate('status_updated', $lot);
        }

        // 如果拍賣開始時間改變，也記錄
        if ($lot->wasChanged('auction_start_at')) {
            $this->logSitemapUpdate('auction_start_updated', $lot);
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
        $this->logSitemapUpdate('deleted', $lot);
    }

    /**
     * Handle the Lot "restored" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function restored(Lot $lot)
    {
        $this->logSitemapUpdate('restored', $lot);
    }

    /**
     * Handle the Lot "force deleted" event.
     *
     * @param  \App\Models\Lot  $lot
     * @return void
     */
    public function forceDeleted(Lot $lot)
    {
        $this->logSitemapUpdate('force_deleted', $lot);
    }

    /**
     * 記錄 sitemap 更新日誌
     */
    private function logSitemapUpdate($action, $lot)
    {
        try {
            \Log::info("Sitemap needs update: Lot {$lot->id} {$action}. Sitemap will be served dynamically.");

            // 可選：如果你想要自動生成靜態文件，取消下面的註釋
            // Artisan::call('sitemap:generate', ['--static' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to log sitemap update: ' . $e->getMessage());
        }
    }
}
