<?php

namespace App\Jobs;

use App\Models\Lot;
use App\Services\LineService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
    public function handle(LineService $lineService, UserService $userService)
    {
        $lots = $this->auction->lots;

        $this->auction->update(['status'=>1]);


        foreach($lots as $lot) {
            $lot->update([
                'status'=>21#auction in progress
            ]);
            /*$favoriteUserIds = $lot->favorites()->pluck('user_id');
            foreach ($favoriteUserIds as $favoriteUserId) {
                $user = $userService->getUser($favoriteUserId);
                $messageBuilder = $lineService->buildMultiLotsTemplate($user, $lots, '競標開始', '競標開始');
                $response = $lineService->pushMessage($user->line_id, $messageBuilder);

                if ($response->isSucceeded()) {
                    Log::channel('line')->info('Successes!');
                } else {
                    Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
                }
            }*/
        }
    }
}
