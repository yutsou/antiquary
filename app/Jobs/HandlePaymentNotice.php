<?php

namespace App\Jobs;

use App\CustomFacades\CustomClass;
use App\Repositories\CartRepository;
use App\Services\CartService;
use App\Services\LotService;
use App\Services\OrderService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandlePaymentNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $lot, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lot, $type)
    {
        $this->lot = $lot;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lot = $this->lot;
        $type = $this->type;#0: stage1 notice, 1: stage2 notice

        if ($lot->status === 22) { #競標成功 - 等待買家完成付款
            if ($type === 0) {
                // 發送付款提醒給得標者
                CustomClass::sendTemplateNotice($lot->winner_id, 6, 0, $lot->id, 1, 1);
                if(config('app.env') == 'production') {
                    HandlePaymentNotice::dispatch($lot, 1)->delay(Carbon::now()->addDays(4));
                } else {
                    HandlePaymentNotice::dispatch($lot, 1)->delay(Carbon::now()->addSeconds(120));
                }
            } else {#type 1
                // 獲取得標者用戶資訊
                $winner = app(UserService::class)->getUser($lot->winner_id);
                $cartService = app(CartService::class);

                // 處理用戶懲罰
                if ($winner->status === 0) {
                    HandleUserStatus::dispatch(1, $winner);#第一階短暫封鎖
                    if(config('app.env') == 'production') {
                        HandleUserStatus::dispatch(2, $winner)->delay(Carbon::now()->addDays(7));#第一階封鎖解鎖
                    } else {
                        HandleUserStatus::dispatch(2, $winner)->delay(Carbon::now()->addSeconds(210));#第一階封鎖解鎖
                    }
                    CustomClass::sendTemplateNotice($lot->winner_id, 6, 1, $lot->id, 1, 1);
                } elseif($winner->status === 2 || $winner->status === 3) { #status 2
                    HandleUserStatus::dispatch(3, $winner);#第二階永久封鎖
                    CustomClass::sendTemplateNotice($lot->winner_id, 6, 2, $lot->id, 1, 1);
                }

                // 從得標者購物車中強制移除商品（包括競標商品）
                app(CartRepository::class)->removeCartItem($lot->winner_id, $lot->id);

                // 檢查是否已經產生訂單，如果有則將訂單狀態設為51
                $order = app(OrderService::class)->getOrderByLotAndUser($lot->id, $lot->winner_id);
                if ($order) {
                    app(OrderService::class)->updateOrderStatus(51, $order); // 失效 - 付款逾期
                }

                // 通知賣家商品被棄標
                CustomClass::sendTemplateNotice($lot->owner_id, 2, 3, $lot->id, 1);

                // 將商品狀態設為棄標
                app(LotService::class)->updateLotStatus(26, $lot);
            }
        }
    }
}
