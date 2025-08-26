<?php

namespace App\Jobs;

use App\CustomFacades\CustomClass;
use App\Models\MergeShippingRequest;
use App\Services\CartService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireMergeShippingRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mergeRequestId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mergeRequestId)
    {
        $this->mergeRequestId = $mergeRequestId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mergeRequest = MergeShippingRequest::with(['items.lot'])->find($this->mergeRequestId);

        if (!$mergeRequest) {
            return;
        }

        // 檢查 merge request 是否仍然是已核准狀態
        if ($mergeRequest->status !== MergeShippingRequest::STATUS_APPROVED) {
            return;
        }

        // 將狀態設為過期
        $mergeRequest->update(['status' => MergeShippingRequest::STATUS_EXPIRED]);

        // 還原庫存
        $this->restoreInventory($mergeRequest);
    }

    /**
     * 當 merge request 狀態變更時取消 Job
     */
    public function failed(\Throwable $exception)
    {
        // 記錄失敗日誌
        \Log::error('ExpireMergeShippingRequest failed for merge request ID: ' . $this->mergeRequestId, [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * 還原庫存並將物品加回購物車
     */
    private function restoreInventory($mergeRequest)
    {
        $cartService = app(CartService::class);

        foreach ($mergeRequest->items as $item) {
            $lot = $item->lot;
            if ($lot) {
                // 還原庫存
                $lot->increment('inventory', $item->quantity);

                // 如果庫存從0變為有庫存，且商品狀態是下架狀態，則重新上架
                if ($lot->inventory > 0 && $lot->status == 60) { // 60 是下架狀態
                    $lot->update(['status' => 61]); // 61 是正常狀態
                }

                if ($lot->type == 0) {
                    $lot->update(['status' => 26]); // 26 是棄標狀態
                    CustomClass::sendTemplateNotice($lot->owner_id, 2, 3, $lot->id, 1);
                }
            }

            // 如果 lot type 是 0，將物品從購物車移除，否則加回購物車
            if ($lot->type == 0) {
                $cartService->removeCartItem($mergeRequest->user_id, $item->lot_id);
            } else {
                $cartService->addToCart($mergeRequest->user_id, $item->lot_id, $item->quantity);
            }
        }
    }
}
