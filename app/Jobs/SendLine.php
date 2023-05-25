<?php

namespace App\Jobs;

use App\Services\LineService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $lot, $userId, $type, $bid, $text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    //type 0=>出價被超過 1=>自動出價出價
    public function __construct($lot, $userId, $bid, $type, $text)
    {
        $this->lot = $lot;
        $this->userId = $userId;
        $this->bid = $bid;
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LineService $lineService, UserService $userService)
    {
        $user = $userService->getUser($this->userId);
        $lineUser = $user->line_id;

        #'出價已經被超過，'
        if($lineUser !== null) {
            switch ($this->type) {
                case 0:
                    $messageBuilder = $lineService->buildLotMessage($this->lot, $user, $this->text);
                    break;
                case 1:
                    $messageBuilder = $lineService->buildMessage($this->text);
                    break;
            }


            $response = $lineService->pushMessage($lineUser, $messageBuilder);
            if ($response->isSucceeded()) {
                Log::channel('line')->info('Succeeded!');
            } else {
                Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
            }
        }
    }
}
