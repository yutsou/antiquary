<?php

namespace App\Jobs;

use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleAuctionStart implements ShouldQueue
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

        $this->auction->update(['status'=>1]);

        foreach($lots as $lot) {
            $lot->update([
                'status'=>21#auction in progress
            ]);
            $favoriteUserIds = $lot->favorites()->pluck('user_id');
            foreach ($favoriteUserIds as $favoriteUserId) {
                LineNotice::dispatch($favoriteUserId, $lot, 0, [1000, 5000, 10000], '競標已經開始');
            }
        }
    }
}
