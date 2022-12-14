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

                #1???????????? ??????richmenu ID 2???????????? 3????????????
                if(mb_substr($messageText, 0, 4, "UTF-8") == "????????????") {#???????????????
                    switch (mb_substr($messageText, 4, 8, "UTF-8")) {
                        case '????????????':
                            $this->createRichMenu($bot);
                            break;
                        case '????????????':
                            $imagePath = '/var/www/jasonAuction/public/images/api/linebot/richmenu.png';
                            $contentType = 'image/png';
                            $response = $bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '????????????':
                            $response = $bot->downloadRichMenuImage($richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '????????????':
                            $response = $bot->getRichMenuList();
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '????????????':
                            $response = $bot->linkRichMenu($lineAdminId, $richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        case '????????????':
                            $response = $bot->getRichMenu($richMenuId);
                            break;
                        case '????????????':
                            $response = $bot->deleteRichMenu($richMenuId);
                            Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                            break;
                        default:
                            $messageBuilder = new TextMessageBuilder("???????????????");
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
                                    new PostbackTemplateActionBuilder('??????', 'initMode'),
                                    new PostbackTemplateActionBuilder('??????', 'setAutoBid,'.$lineMode->extra_info.','.$messageText),
                                ];
                                $confirmTemplateBuilder = new ConfirmTemplateBuilder('?????????"'.$lot->name.'" ?????????????????? NT$'.number_format($messageText).' ??????', $actions);
                                #$confirmTemplateBuilder = new ConfirmTemplateBuilder('?????????', $actions);
                                $messageBuilder = new TemplateMessageBuilder(
                                    '??????????????????????????????',
                                    $confirmTemplateBuilder
                                );
                            }
                        } else {
                            $lineMode->delete();
                        }
                    }
                } else {
                    $messageBuilder = new TextMessageBuilder("????????????????????????");
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
                                new PostbackTemplateActionBuilder("??????", 'lineBid,'.$lotId.','.$bidderId.','.$bid),
                            ];
                            $buttonTemplateBuilder = new ButtonTemplateBuilder('??????????????????', '????????? "'.$lot->name.'" ??????NT$'.number_format($bid), null, $actions);
                            $messageBuilder = new TemplateMessageBuilder('??????????????????NT$'.$bid, $buttonTemplateBuilder);*/
                            $actions = [
                                new PostbackTemplateActionBuilder('??????', 'initMode'),
                                new PostbackTemplateActionBuilder("??????", 'lineBid,'.$lotId.','.$bidderId.','.$bid),
                            ];
                            $confirmTemplateBuilder = new ConfirmTemplateBuilder('????????? "'.$lot->name.'" ??????NT$'.number_format($bid), $actions);
                            $messageBuilder = new TemplateMessageBuilder(
                                '?????????????????? NT$'.$bid,
                                $confirmTemplateBuilder
                            );
                        } else {
                            $content = '??????????????????';
                            $nextBids = $this->bidService->getNextBids($lot->current_bid);
                            $actions = [];
                            foreach($nextBids as $nextBid) {
                                array_push($actions, new PostbackTemplateActionBuilder("??????NT$".number_format($nextBid), 'lineBidConfirm,'.$lot->id.','.$bidderId.','.$nextBid));
                            }
                            $buttonTemplateBuilder = new ButtonTemplateBuilder($lot->name, $content.'?????????NT$'.number_format($lot->current_bid).'???????????????????????????', env('APP__URL').$lot->images->first()->url, $actions);
                            $messageBuilder = new TemplateMessageBuilder($content, $buttonTemplateBuilder);
                        }

                        break;
                    case 'lineBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $bid = $data[3];
                        $this->bidService->manualBidLot($lotId, $bidderId, $bid);
                        $messageBuilder = new TextMessageBuilder('NT$'.number_format($bid).' ????????????');
                        break;

                    case 'showBiddingLot':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $lots = $this->lotService->getBiddingLot($user);
                        if(count($lots) != 0) {
                            $contents = array();
                            foreach($lots as $lot)
                            {
                                $content = $this->createLotTemplate($lot, $user);
                                $contents[] = $content;
                            }

                            $messageBuilder =
                                FlexMessageBuilder::builder()
                                    ->setAltText('???????????????')
                                    ->setContents(
                                        CarouselContainerBuilder::builder()->setContents($contents)
                                    );
                        } else {
                            $messageBuilder = new TextMessageBuilder("?????????????????????");
                        }

                        break;

                    case 'showFavorites':

                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $favorites =$user->favoriteLots()->where('process', 3)->get();
                        if(count($favorites) != 0) {
                            $contents = array();
                            foreach($favorites as $lot)
                            {
                                $content = $this->createLotTemplate($lot, $user);
                                $contents[] = $content;
                            }

                            $messageBuilder =
                                FlexMessageBuilder::builder()
                                    ->setAltText('???????????????')
                                    ->setContents(
                                        CarouselContainerBuilder::builder()->setContents($contents)
                                    );
                        } else {
                            $messageBuilder = new TextMessageBuilder("?????????????????????");
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

                        $messageBuilder = new TextMessageBuilder('????????????????????????????????????????????????????????????????????????????????????');
                        break;
                    case 'initMode':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $this->initMode($user);
                        $messageBuilder = new TextMessageBuilder("?????????");
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
                            $messageBuilder = new TextMessageBuilder('????????????????????? NT$'.number_format($autoBid).'?????????????????? NT$'.number_format($bid));
                        } else {
                            $messageBuilder = new TextMessageBuilder('?????????????????????????????? NT$'.number_format($autoBid));
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
                    $messageBuilder = new TextMessageBuilder('????????????');

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
        $templateActionBuilder = new PostbackTemplateActionBuilder("????????????", "accountLink");
        $richMenuAreaBuilder1 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder,$templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(833, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("???????????????", "showBiddingLot");
        $richMenuAreaBuilder2 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder,$templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(1666, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("???????????????", "showFavorites");
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
                            ->setText($text.'?????? NT$'.number_format($lot->current_bid).'???????????????????????????')
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
                    new PostbackTemplateActionBuilder("??????NT$".number_format($nextBid), 'lineBidConfirm,'.$lot->id.','.$user->id.','.$nextBid)
                );

        }

        $bodyContents[] = ButtonComponentBuilder::builder()
            ->setStyle(ComponentButtonStyle::PRIMARY)
            ->setHeight(ComponentButtonHeight::SM)
            ->setColor('#003a6c')
            ->setAction(
                new PostbackTemplateActionBuilder("??????????????????", 'confirmSetAutoBid,'.$lot->id.','.$user->id)
            );

        $lotAutoBid = $user->getLotAutoBid($lot->id);
        if($lotAutoBid != null) {
            $text = '????????????????????? NT$'.number_format($lotAutoBid->bid);
        } else {
            $text = '?????????????????????';
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
            return new TextMessageBuilder("????????????????????????????????????");
        } elseif(intval($bid) < $lot->next_bid) {
            return new TextMessageBuilder("????????????????????? NT$".$lot->next_bid."????????????????????????");
        }  else {
            return true;
        }
    }

    public function initMode($user)
    {
        $user->lineMode()->delete();
    }
}
