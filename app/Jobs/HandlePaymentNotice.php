<?php

namespace App\Jobs;

use App\CustomFacades\CustomClass;
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
    protected $order, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        $type = $this->type;#0: stage1 notice, 1: stage2 notice

        if ($order->status === 0 or $order->status === 10) {
            if ($type === 0) {
                CustomClass::sendTemplateNotice($order->user_id, 6, 0, $order->id, 1, 1);
                if(config('app.env') == 'production') {
                    HandlePaymentNotice::dispatch($order, 1)->delay(Carbon::now()->addDays(4));
                } else {
                    HandlePaymentNotice::dispatch($order, 1)->delay(Carbon::now()->addSeconds(120));
                }
            } else {#type 1
                if ($order->user->status === 0) {
                    HandleUserStatus::dispatch(1, $order->user);#第一階短暫封鎖
                    if(config('app.env') == 'production') {
                        HandleUserStatus::dispatch(2, $order->user)->delay(Carbon::now()->addDays(7));#第一階封鎖解鎖
                    } else {
                        HandleUserStatus::dispatch(2, $order->user)->delay(Carbon::now()->addSeconds(210));#第一階封鎖解鎖
                    }
                    CustomClass::sendTemplateNotice($order->user_id, 6, 1, $order->id, 1, 1);
                } elseif($order->user->status === 2 || $order->user->status === 3) { #status 2
                    HandleUserStatus::dispatch(3, $order->user);#第二階永久封鎖
                    CustomClass::sendTemplateNotice($order->user_id, 6, 2, $order->id, 1, 1);
                }
                CustomClass::sendTemplateNotice($order->lot->owner->id, 2, 3, $order->lot->id, 1);

                switch (true) {
                    // 等待確認訂單
                    case ($order->status == 0):
                        // 失效 - 未確認訂單
                        $status = 50;
                        // 棄標
                        app(LotService::class)->updateLotStatus(25, $order->lot);
                        break;
                    // 等待付款
                    case ($order->status == 10):
                        $status = 51;
                        break;
                }

                app(OrderService::class)->updateOrderStatus($status, $order);
            }
        }
    }
}
