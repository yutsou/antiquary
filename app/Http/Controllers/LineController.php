<?php

namespace App\Http\Controllers;

use App\Events\LineBindSuccess;
use App\Presenters\CarbonPresenter;
use App\Services\BidService;
use App\Services\LotService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Constant\Flex\ComponentButtonHeight;
use LINE\LINEBot\Constant\Flex\ComponentButtonStyle;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectMode;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectRatio;
use LINE\LINEBot\Constant\Flex\ComponentImageSize;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Constant\Flex\ComponentSpacing;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;

use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;

use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\RichMenuBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class LineController extends Controller
{
    private $lotService;

    public function __construct(
        LotService $lotService,
        UserService $userService,
        BidService $bidService
    ) {
        $this->lotService = $lotService;
        $this->userService = $userService;
        $this->bidService = $bidService;
    }

    public function webhook (Request $request)
    {
        Log::channel('line')->info($request['events']);
        $httpClient = new CurlHTTPClient(env("LINE_BOT_CHANNEL_ACCESS_TOKEN"));
        $bot = new LINEBot($httpClient, ['channelSecret' => env("LINE_BOT_CHANNEL_SECRET")]);
        $richMenuId = env("LINE_BOT_RICHMENU_ID");
        $lineAdminId = env("LINE_BOT_ADMIN_ID");

        switch ($request['events'][0]['type']) {
            case 'message':
                $messageText = $request['events'][0]['message']['text'];

                #1建立目錄 取得richmenu ID 2上傳圖片 3連結帳戶
                if(mb_substr($messageText, 0, 4, "UTF-8") == "高級指令") {#管理員指令
                    switch (mb_substr($messageText, 4, 8, "UTF-8")) {
                        case '建立目錄':
                            $this->createRichMenu($bot);
                            break;
                        case '上傳圖片':
                            $imagePath = '/var/www/jasonAuction/public/images/api/linebot/richmenu.png';
                            $contentType = 'image/png';
                            $response = $bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '下載圖片':
                            $response = $bot->downloadRichMenuImage($richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '取得列表':
                            $response = $bot->getRichMenuList();
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '連結帳戶':
                            $response = $bot->linkRichMenu($lineAdminId, $richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '取得目錄':
                            $response = $bot->getRichMenu($richMenuId);
                            break;
                        case '刪除目錄':
                            $response = $bot->deleteRichMenu($richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        default:
                            $messageBuilder = new TextMessageBuilder("沒有此指令");
                            break;
                    }
                } elseif (ctype_digit($messageText)) {
                    $lineUserId = $request['events'][0]['source']['userId'];
                    $user = $this->userService->getUserByOauth('line', $lineUserId);
                    $lineMode = $user->lineMode;

                    if($lineMode !== null){
                        if($lineMode->mode == 0 && $lineMode->step == 0) {

                            $extraInfo = explode(',', $lineMode->extra_info);
                            $lotId = $extraInfo[0];

                            $lot = $this->lotService->getLot($lotId);

                            $validResult = $this->validBid($lot, $user, $messageText);
                            if($validResult !== true) {
                                $messageBuilder = $validResult;
                            } else {
                                $actions = [
                                    new PostbackTemplateActionBuilder('取消', 'initMode'),
                                    new PostbackTemplateActionBuilder('確定', 'setAutoBid,'.$lineMode->extra_info.','.$messageText),
                                ];
                                $confirmTemplateBuilder = new ConfirmTemplateBuilder('確定對"'.$lot->name.'" 設置自動出價 NT$'.number_format($messageText).' 嗎？', $actions);
                                #$confirmTemplateBuilder = new ConfirmTemplateBuilder('確定對', $actions);
                                $messageBuilder = new TemplateMessageBuilder(
                                    '確定設置自動出價嗎？',
                                    $confirmTemplateBuilder
                                );
                            }
                        } else {
                            $lineMode->delete();
                        }
                    }
                } else {
                    $messageBuilder = new TextMessageBuilder("不知道你在說什麼");
                }
                break;
            case 'postback':
                $data = explode(",", $request['events'][0]['postback']['data']);

                switch ($data[0]) {
                    case 'accountLink':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $response = $bot->createLinkToken($lineUserId);

                        $lineToken = $response->getJSONDecodedBody()['linkToken'];
                        $messageBuilder = new TextMessageBuilder(env('APP_URL').'/auth/line/verify-bind?linkToken='.$lineToken);
                        #$messageBuilder = new TextMessageBuilder(route('auth.line.verify_bind', ['linkToken'=>$lineToken]));
                        break;
                    case 'lineBidConfirm':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $bid = $data[3];

                        $lot = $this->lotService->getLot($lotId);
                        if($bid >= $lot->current_bid+$this->bidService->bidRule($lot->current_bid)) {
                            /*$actions = [
                                new PostbackTemplateActionBuilder("確認", 'lineBid,'.$lotId.','.$bidderId.','.$bid),
                            ];
                            $buttonTemplateBuilder = new ButtonTemplateBuilder('確認是否出價', '是否對 "'.$lot->name.'" 出價NT$'.number_format($bid), null, $actions);
                            $messageBuilder = new TemplateMessageBuilder('確認是否出價NT$'.$bid, $buttonTemplateBuilder);*/
                            $actions = [
                                new PostbackTemplateActionBuilder('取消', 'initMode'),
                                new PostbackTemplateActionBuilder("確認", 'lineBid,'.$lotId.','.$bidderId.','.$bid),
                            ];
                            $confirmTemplateBuilder = new ConfirmTemplateBuilder('是否對 "'.$lot->name.'" 出價NT$'.number_format($bid), $actions);
                            $messageBuilder = new TemplateMessageBuilder(
                                '確認是否出價 NT$'.$bid,
                                $confirmTemplateBuilder
                            );
                        } else {
                            $content = '價格已經變動';
                            $nextBids = $this->bidService->getNextBids($lot->current_bid);
                            $actions = [];
                            foreach($nextBids as $nextBid) {
                                array_push($actions, new PostbackTemplateActionBuilder("出價NT$".number_format($nextBid), 'lineBidConfirm,'.$lot->id.','.$bidderId.','.$nextBid));
                            }
                            $buttonTemplateBuilder = new ButtonTemplateBuilder($lot->name, $content.'，目前NT$'.number_format($lot->current_bid).'，需要繼續競標嗎？', env('APP__URL').$lot->images->first()->url, $actions);
                            $messageBuilder = new TemplateMessageBuilder($content, $buttonTemplateBuilder);
                        }

                        break;
                    case 'lineBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $bid = $data[3];
                        $this->bidService->manualBidLot($lotId, $bidderId, $bid);
                        $messageBuilder = new TextMessageBuilder('NT$'.number_format($bid).' 出價成功');
                        break;

                    case 'showBiddingLot':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $lots = $this->lotService->getBiddingLot($user);

                        $lotsCount = count($lots);
                        if($lotsCount != 0) {
                            $messageBuilder = new MultiMessageBuilder();
                            $contents = array();
                            foreach($lots as $lot)
                            {
                                $content = $this->createLotTemplate($lot, $user);
                                $contents[] = $content;
                            }

                            $contents_chunks = array_chunk($contents, 10);

                            foreach ($contents_chunks as $contents_chunk) {
                                $tmpMessageBuilder =
                                    FlexMessageBuilder::builder()
                                        ->setAltText('追蹤的物品')
                                        ->setContents(
                                            CarouselContainerBuilder::builder()->setContents($contents_chunk)
                                        );
                                $messageBuilder->add($tmpMessageBuilder);
                            }
                        } else {
                            $messageBuilder = new TextMessageBuilder("沒有參與的競標");
                        }

                        break;

                    case 'showFavorites':

                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $favorites =$user->favoriteLots()->whereIn('status', [20, 21])->get();
                        $favoritesCount = count($favorites);
                        if($favoritesCount != 0) {
                            $messageBuilder = new MultiMessageBuilder();
                            $contents = array();
                            foreach($favorites as $lot)
                            {
                                $content = $this->createLotTemplate($lot, $user);
                                $contents[] = $content;
                            }

                            $contents_chunks = array_chunk($contents, 10);

                            foreach ($contents_chunks as $contents_chunk) {
                                $tmpMessageBuilder =
                                    FlexMessageBuilder::builder()
                                        ->setAltText('追蹤的物品')
                                        ->setContents(
                                            CarouselContainerBuilder::builder()->setContents($contents_chunk)
                                        );
                                $messageBuilder->add($tmpMessageBuilder);
                            }

                        } else {
                            $messageBuilder = new TextMessageBuilder("沒有追蹤的物品");
                        }

                        break;
                    case 'confirmSetAutoBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $user = $this->userService->getUser($bidderId);
                        $user->lineMode()->updateOrCreate([
                            'user_id'=>$bidderId,
                            'mode'=>0,
                            'step'=>0,
                            'extra_info'=>$lotId.','.$bidderId
                        ]);

                        $messageBuilder = new TextMessageBuilder('請輸入您的自動出價，我們將會再接下來的步驟與您確認出價。');
                        break;
                    case 'initMode':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $this->initMode($user);
                        $messageBuilder = new TextMessageBuilder("已取消");
                        break;
                    case 'setAutoBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $autoBid = $data[3];
                        $bid = $this->bidService->autoBidLot($lotId, $bidderId, $autoBid);
                        $lot = $this->lotService->getLot($lotId);
                        $user = $this->userService->getUser($bidderId);

                        /*$messageBuilder = FlexMessageBuilder::builder()
                            ->setAltText($lot->name)
                            ->setContents(
                                $this->createLotTemplate($lot, $user)
                            );*/
                        if($bid !== false){
                            $messageBuilder = new TextMessageBuilder('已設置自動出價 NT$'.number_format($autoBid).'，已幫您出價 NT$'.number_format($bid));
                        } else {
                            $messageBuilder = new TextMessageBuilder('已修改自動出價金額為 NT$'.number_format($autoBid));
                        }
                        $this->initMode($user);
                        break;
                    default:
                        $messageBuilder = new TextMessageBuilder('postback default');
                        break;
                }
                break;


            case 'accountLink':
                $result = $request['events'][0]['link']['result'];
                if ($result == 'ok') {
                    $nonce = $request['events'][0]['link']['nonce'];
                    $line_id = $request['events'][0]['source']['userId'];
                    $this->userService->confirmBind($nonce, $line_id);
                    $messageBuilder = new TextMessageBuilder('綁定成功');

                    #dynamic change status
                    $user = $this->userService->getUserByOauth('line', $line_id);
                    LineBindSuccess::dispatch($user);

                }
                break;
            case 'follow':
                $lineUserId = $request['events'][0]['source']['userId'];
                $response = $bot->linkRichMenu($lineUserId, $richMenuId);
                Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                break;
        }

        $response = $bot->replyMessage($request['events'][0]['replyToken'], $messageBuilder);
        if ($response->isSucceeded()) {
            Log::channel('line')->info('Succeeded!');
        } else {
            Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }
    }

    public function createRichMenu($bot)
    {
        $richMenuSizeBuilder = new RichMenuSizeBuilder(843, 2500);#h,w

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("帳號綁定", "accountLink");
        $richMenuAreaBuilder1 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder,$templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(833, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("參與的競標", "showBiddingLot");
        $richMenuAreaBuilder2 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder,$templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(1666, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("追蹤的物品", "showFavorites");
        $richMenuAreaBuilder3 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder,$templateActionBuilder);


        $richMenuBuilder = new RichMenuBuilder($richMenuSizeBuilder, true, "Nice richmenu", "Tap here", [$richMenuAreaBuilder1,$richMenuAreaBuilder2,$richMenuAreaBuilder3]);
        $response = $bot->createRichMenu($richMenuBuilder);
        Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
    }

    public function webhookVerify()
    {
        Log::channel('line')->info(date("Y-m-d h:i:sa", time()));
        return response()->json(['success' => true, 'timestamp' => time(), 'statusCode' => 200, 'reason' => 'OK', 'detail' => '200']);
    }

    public function test()
    {
        dd('123');
    }

    public function createLotTemplate($lot, $user, $text='')
    {
        $nextBids = $this->bidService->getNextBids($lot->current_bid);
        $carbonPresenter = new CarbonPresenter;

        $bodyContents =
            [
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('20px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($lot->name)
                            ->setWrap(false)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::MD)
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($carbonPresenter->lotPresent($lot->auction_start_at, $lot->auction_end_at))
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM),
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($text.'目前 NT$'.number_format($lot->current_bid).'，需要繼續競標嗎？')
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM),
                    ]),
                SeparatorComponentBuilder::builder(),
            ];
        foreach ($nextBids as $nextBid) {
            $bodyContents[] = ButtonComponentBuilder::builder()
                ->setStyle(ComponentButtonStyle::LINK)
                ->setHeight(ComponentButtonHeight::SM)
                ->setAction(
                    new PostbackTemplateActionBuilder("出價NT$".number_format($nextBid), 'lineBidConfirm,'.$lot->id.','.$user->id.','.$nextBid)
                );

        }

        $bodyContents[] = ButtonComponentBuilder::builder()
            ->setStyle(ComponentButtonStyle::PRIMARY)
            ->setHeight(ComponentButtonHeight::SM)
            ->setColor('#003a6c')
            ->setAction(
                new PostbackTemplateActionBuilder("設定自動出價", 'confirmSetAutoBid,'.$lot->id.','.$user->id)
            );

        $lotAutoBid = $user->getLotAutoBid($lot->id);
        if($lotAutoBid != null) {
            $text = '您的自動出價： NT$'.number_format($lotAutoBid->bid);
        } else {
            $text = '未設定自動出價';
        }
        $bodyContents[] = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setContents([
                TextComponentBuilder::builder()
                    ->setText($text)
                    #->setAlign('center')
                    ->setColor("#999999")
                    ->setWrap(true)
                    ->setWeight(ComponentFontWeight::BOLD)
                    ->setSize(ComponentFontSize::SM),
            ]);


        $body = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents($bodyContents);

        $content = BubbleContainerBuilder::builder()
            ->setHero(
                ImageComponentBuilder::builder()
                    ->setSize(ComponentImageSize::FULL)
                    ->setAspectRatio(ComponentImageAspectRatio::R20TO13)
                    ->setAspectMode(ComponentImageAspectMode::COVER)
                    ->setUrl(asset($lot->main_image->url))
            )
            ->setBody(
                $body
            );

        return $content;
    }

    public function validBid($lot, $user, $bid)
    {
        $now = Carbon::now();
        $startTime = Carbon::create($lot->auction_start_at);
        $endTime = Carbon::create($lot->auction_end_at);

        if ($now->between($startTime, $endTime) === false) {
            return new TextMessageBuilder("必須在拍賣時間內進行出價");
        } elseif(intval($bid) < $lot->next_bid) {
            return new TextMessageBuilder("出價價格需大於 NT$".$lot->next_bid."，請再次輸入出價");
        }  else {
            return true;
        }
    }

    public function initMode($user)
    {
        $user->lineMode()->delete();
    }
}
