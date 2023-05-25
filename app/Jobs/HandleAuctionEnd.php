<?php

namespace App\Jobs;

use App\CustomFacades\CustomClass;
use App\Models\Auction;
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
        foreach($lots as $lot) {
            if($now->gt(Carbon::createFromFormat('Y-m-d H:i:s', $lot->auction_end_at))){
                if($lot->status == 21) {
                    if($lot->current_bid >= $lot->reserve_price && $lot->current_bid != 0) {
                        $winnerId = $lot->bidRecords->first()->bidder_id;

                        $lot->update([
                            'status'=>22,#競標成功
                            'winner_id'=>$winnerId
                        ]);

                        OrderCreate::dispatch($lot);
                        CustomClass::sendTemplateNotice($lot->owner_id, 2, 1, $lot->id);
                        CustomClass::sendTemplateNotice($winnerId, 3, 0, $lot->id, true, true);
                    } elseif ($lot->current_bid === 0){
                        $lot->update([
                            'status'=>23#無人競標流標
                        ]);
                        CustomClass::sendTemplateNotice($lot->owner_id, 2, 3, $lot->id);
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
