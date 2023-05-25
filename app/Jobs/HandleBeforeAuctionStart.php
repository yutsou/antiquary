<?php

namespace App\Jobs;

use App\Services\LineService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleBeforeAuctionStart implements ShouldQueue
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
        $tmpUserIds = collect();
        foreach ($lots as $lot) {
            $tmpUserIds =  $tmpUserIds->merge($lot->favorites->pluck('user_id'));
        }
        $uniqueUserIds =  $tmpUserIds->unique();

        foreach ($uniqueUserIds as $userId) {
            $user = $userService->getUser($userId);
            if($user->line_id != null) {
                $messageBuilder = $lineService->buildAuctionMessage($this->auction, $user, '有一個您感興趣的拍賣會將於10分鐘後開始');
                $response = $lineService->pushMessage($user->line_id, $messageBuilder);

                if ($response->isSucceeded()) {
                    Log::channel('line')->info('Successes!');
                } else {
                    Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
                }
            }
        }

    }
}
