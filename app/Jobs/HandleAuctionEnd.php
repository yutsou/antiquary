<?php

namespace App\Jobs;

use App\CustomFacades\CustomClass;
use App\Models\Auction;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleAuctionEnd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $auction;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($auction)
    {
        $this->auction = $auction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lots = $this->auction->lots;
        $now = Carbon::now();
        $count = 0;
        $cartService = app(CartService::class);

        foreach($lots as $lot) {
            if($now->gt(Carbon::createFromFormat('Y-m-d H:i:s', $lot->auction_end_at))){
                if($lot->status == 21) {
                    if($lot->current_bid >= $lot->reserve_price && $lot->current_bid != 0) {
                        $winnerId = $lot->bidRecords->first()->bidder_id;

                        $lot->update([
                            'status'=>22,#競標成功
                            'winner_id'=>$winnerId,
                            'type'=>0 # 標記為競標商品
                        ]);

                        // 將競標成功的商品加入得標者的購物車
                        $cartService->addToCart($winnerId, $lot->id, 1);

                        if(config('app.env') == 'production') {
                            HandlePaymentNotice::dispatch($lot, 0)->delay(Carbon::now()->addDays(3));
                        } else {
                            HandlePaymentNotice::dispatch($lot, 0)->delay(Carbon::now()->addSeconds(90));
                        }

                        $lot->refresh();
                        CustomClass::sendTemplateNotice($lot->owner_id, 2, 5, $lot->id, 1);
                        CustomClass::sendTemplateNotice($winnerId, 2, 4, $lot->id, 1, 1);
                    } elseif ($lot->current_bid == 0){
                        $lot->update([
                            'status'=>23#無人競標流標
                        ]);
                        CustomClass::sendTemplateNotice($lot->owner_id, 2, 1, $lot->id);
                    } else {
                        $lot->update([
                            'status'=>24#未達底價流標
                        ]);
                        CustomClass::sendTemplateNotice($lot->owner_id, 2, 2, $lot->id);
                    }
                }
            } else {
                $count += 1;
            }
        }
        if($count === 0) {
            $this->auction->update(['status'=>2]);#拍賣會結束
        }
    }
}
