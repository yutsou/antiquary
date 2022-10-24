<?php

namespace App\Jobs;

use App\Http\Controllers\LineController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId, $lot, $bid, $text, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    //type 0=>出價被超過 1=>自動出價出價
    public function __construct($lot, $userId, $bid, $type)
    {
        $this->userId = $userId;
        $this->lot = $lot;
        $this->bid = $bid;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);
        $lineUser = $user->line_id;

        #'出價已經被超過，'
        if($lineUser !== null) {
            $httpClient = new CurlHTTPClient(env("LINE_BOT_CHANNEL_ACCESS_TOKEN"));
            $bot = new LINEBot($httpClient, ['channelSecret' => env("LINE_BOT_CHANNEL_SECRET")]);

            switch ($this->type) {
                case 0:
                    $messageBuilder = FlexMessageBuilder::builder()
                        ->setAltText($this->lot->name)
                        ->setContents(
                            app(LineController::class)->createLotTemplate($this->lot, $user, '出價已經被超過，')
                        );
                    break;
                case 1:
                    $messageBuilder = new TextMessageBuilder('"'.$this->lot->name.'"，使用自動出價幫您出價 NT$'.number_format($this->bid));
                    break;
            }


            $response = $bot->pushMessage($lineUser, $messageBuilder);
            if ($response->isSucceeded()) {
                Log::channel('line')->info('Succeeded!');
            } else {
                Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
            }
        }
    }
}
