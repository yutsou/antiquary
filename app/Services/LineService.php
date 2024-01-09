<?php

namespace App\Services;

use App\Events\LineBindSuccess;
use App\Presenters\CarbonPresenter;
use Carbon\Carbon;
use GuzzleHttp\Client;
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
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class LineService
{
    private $httpClient, $bot, $richMenuId, $lineAdminId;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('services.line.bot_channel_access_token'));
        $this->bot = new LINEBot($this->httpClient, ['channelSecret' => config('services.line.bot_channel_secret')]);
        $this->richMenuId = config('services.line.line_bot_richmenu_id');
        $this->lineAdminId = config('services.line.line_bot_admin_id');
    }

    public function getLoginUrl()
    {
        // 組成 Line Login Url
        $url = config('services.line.authorize_base_url');
        $url .= '?response_type=code';
        $url .= '&client_id=' . config('services.line.login_channel_id');
        $url .= '&redirect_uri=' . config('app.url') . '/auth/line/callback';
        $url .= '&state=' . csrf_token();
        $url .= '&scope=profile%20openid%20email';

        return $url;
    }

    public function getLineToken($code)
    {
        $client = new Client();
        $response = $client->request('POST', config('services.line.get_token_url'), [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('app.url') . '/auth/line/callback',
                'client_id' => config('services.line.login_channel_id'),
                'client_secret' => config('services.line.login_channel_secret')
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getUserProfile($token)
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        $response = $client->request('GET', config('services.line.get_user_profile_url'), [
            'headers' => $headers
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function createRichMenu()
    {
        $richMenuSizeBuilder = new RichMenuSizeBuilder(843, 2500);#h,w

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("所有拍賣會", "showAllAuction");
        $richMenuAreaBuilder1 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder, $templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(833, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("參與的競標", "showBiddingLot");
        $richMenuAreaBuilder2 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder, $templateActionBuilder);

        $richMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(1666, 0, 833, 843);#w,h
        $templateActionBuilder = new PostbackTemplateActionBuilder("追蹤的物品", "showFavorites");
        $richMenuAreaBuilder3 = new RichMenuAreaBuilder($richMenuAreaBoundsBuilder, $templateActionBuilder);


        $richMenuBuilder = new RichMenuBuilder($richMenuSizeBuilder, true, "Nice richmenu", "Tap here", [$richMenuAreaBuilder1, $richMenuAreaBuilder2, $richMenuAreaBuilder3]);
        return $this->bot->createRichMenu($richMenuBuilder);
    }

    public function uploadRichMenuImage()
    {
        $imagePath = '/var/www/antiquary/public/images/api/linebot/richmenu.png';
        $contentType = 'image/png';
        return $this->bot->uploadRichMenuImage($this->richMenuId, $imagePath, $contentType);
    }

    public function downloadRichMenuImage()
    {
        return $this->bot->downloadRichMenuImage($this->richMenuId);
    }

    public function getRichMenuList()
    {
        return $this->bot->getRichMenuList();
    }

    public function adminLinkRichMenu()
    {
        return $this->bot->linkRichMenu($this->lineAdminId, $this->richMenuId);
    }

    public function getRichMenu()
    {
        return $this->bot->getRichMenu($this->richMenuId);
    }

    public function deleteRichMenu()
    {
        return $this->bot->deleteRichMenu($this->richMenuId);
    }

    public function dealUnknownCommand()
    {
        return new TextMessageBuilder("沒有此指令");
    }

    public function dealLineMode($lineMode, $lot, $user, $messageText)
    {
        $validResult = $this->validBid($lot, $user, $messageText);
        if ($validResult !== true) {
            return $validResult;
        } else {
            $bodyContents =
                [
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('20px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('設定自動出價')
                                ->setWrap(false)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::MD)
                                ->setColor('#003a6c')
                                ->setAction(new UriTemplateActionBuilder($lot->name, route('mart.lots.show', $lot)))
                        ]),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('10px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('確定對"' . $lot->name . '" 設置自動出價 NT$' . number_format($messageText) . ' 嗎？')
                                ->setWrap(true)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::SM),
                        ]),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('10px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('出價金額不包含運費及拍賣服務費用。')
                                ->setWrap(true)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::XXS)
                                ->setAlign('end')
                        ]),
                    SeparatorComponentBuilder::builder(),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::HORIZONTAL)
                        #->setPaddingBottom('10px')
                        ->setContents([
                            ButtonComponentBuilder::builder()
                                ->setStyle(ComponentButtonStyle::LINK)
                                ->setHeight(ComponentButtonHeight::SM)
                                ->setAction(
                                    new PostbackTemplateActionBuilder('取消', 'initMode')
                                )
                                ->setFlex(1),
                            ButtonComponentBuilder::builder()
                                ->setStyle(ComponentButtonStyle::LINK)
                                ->setHeight(ComponentButtonHeight::SM)
                                ->setAction(
                                    new PostbackTemplateActionBuilder('確定', 'setAutoBid,' . $lineMode->extra_info . ',' . $messageText),
                                )
                                ->setFlex(1),
                        ]),
                ];
            $body = BoxComponentBuilder::builder()
                ->setLayout(ComponentLayout::VERTICAL)
                ->setSpacing(ComponentSpacing::SM)
                ->setContents($bodyContents);

            return FlexMessageBuilder::builder()
                ->setAltText('確定設置自動出價嗎？')
                ->setContents(
                    BubbleContainerBuilder::builder()
                        ->setBody(
                            $body
                        )
                );
        }
    }

    public function deleteLineMode($lineMode)
    {
        $lineMode->delete();
    }

    public function buildLinkToken($lineUserId)
    {
        $response = $this->bot->createLinkToken($lineUserId);
        $linkToken = $response->getJSONDecodedBody()['linkToken'];
        return $linkToken;
        #return new TextMessageBuilder(env('APP_URL') . '/auth/line/verify-bind?linkToken=' . $lineToken);
        #return new TextMessageBuilder(route('auth.line.verify_bind', ['linkToken'=>$lineToken]));
    }

    public function generateLineBidLink($linkToken, $nonce)
    {
        return "https://access.line.me/dialog/bot/accountLink?linkToken=".$linkToken."&nonce=".$nonce;
    }

    public function confirmLineBid($lotId, $bidderId, $bid, $lot, $nextBid)
    {
        if ($bid >= $nextBid) {
            $bodyContents =
                [
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('20px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('手動出價')
                                ->setWrap(false)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::MD)
                                ->setColor('#003a6c')
                                ->setAction(new UriTemplateActionBuilder($lot->name, route('mart.lots.show', $lot)))
                        ]),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('10px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('是否對 "'.$lot->name.'" 出價NT$'.number_format($bid))
                                ->setWrap(true)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::SM),
                        ]),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setPaddingBottom('10px')
                        ->setContents([
                            TextComponentBuilder::builder()
                                ->setText('出價金額不包含運費及拍賣服務費用。')
                                ->setWrap(true)
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::XXS)
                                ->setAlign('end')
                        ]),
                    SeparatorComponentBuilder::builder(),
                    BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::HORIZONTAL)
                        #->setPaddingBottom('10px')
                        ->setContents([
                            ButtonComponentBuilder::builder()
                                ->setStyle(ComponentButtonStyle::LINK)
                                ->setHeight(ComponentButtonHeight::SM)
                                ->setAction(
                                    new PostbackTemplateActionBuilder('取消', 'initMode')
                                )
                                ->setFlex(1),
                            ButtonComponentBuilder::builder()
                                ->setStyle(ComponentButtonStyle::LINK)
                                ->setHeight(ComponentButtonHeight::SM)
                                ->setAction(
                                    new PostbackTemplateActionBuilder("確認", 'lineBid,' . $lotId . ',' . $bidderId . ',' . $bid),
                                )
                                ->setFlex(1),
                        ]),
                ];
            $body = BoxComponentBuilder::builder()
                ->setLayout(ComponentLayout::VERTICAL)
                ->setSpacing(ComponentSpacing::SM)
                ->setContents($bodyContents);

            return FlexMessageBuilder::builder()
                ->setAltText('確認是否出價')
                ->setContents(
                    BubbleContainerBuilder::builder()
                        ->setBody(
                            $body
                        )
                );
        } else {
            $content = '價格已經變動';
            $nextBids = app(BidService::class)->getNextBids($lot->current_bid);
            $actions = [];
            foreach ($nextBids as $nextBid) {
                array_push($actions, new PostbackTemplateActionBuilder("出價NT$" . number_format($nextBid), 'lineBidConfirm,' . $lot->id . ',' . $bidderId . ',' . $nextBid));
            }
            $buttonTemplateBuilder = new ButtonTemplateBuilder($lot->name, $content . '，目前NT$' . number_format($lot->current_bid) . '，需要繼續競標嗎？', config('app.url') . $lot->images->first()->url, $actions);
            return new TemplateMessageBuilder($content, $buttonTemplateBuilder);
        }
    }

    public function buildMessage($text)
    {
        return new TextMessageBuilder($text);
    }

    public function buildMultiLotsTemplate($user, $lots, $altText, $contentHeadText='')
    {
        $lotsCount = count($lots);
        if ($lotsCount != 0) {
            $messageBuilder = new MultiMessageBuilder();
            $contents = array();
            foreach ($lots as $lot) {
                $content = $this->createLotTemplate($lot, $user);
                $contents[] = $content;
            }

            $contents_chunks = array_chunk($contents, 10);

            foreach ($contents_chunks as $contents_chunk) {
                $tmpMessageBuilder =
                    FlexMessageBuilder::builder()
                        ->setAltText($altText)
                        ->setContents(
                            CarouselContainerBuilder::builder()->setContents($contents_chunk)
                        );
                $messageBuilder->add($tmpMessageBuilder);
            }

            return $messageBuilder;
        } else {
            return new TextMessageBuilder("沒有".$altText);
        }
    }

    public function buildMultiAuctionsTemplate($user, $auctions, $altText)
    {
        $auctionsCount = count($auctions);
        if ($auctionsCount != 0) {
            $messageBuilder = new MultiMessageBuilder();
            $contents = array();
            foreach ($auctions as $auction) {
                $content = $this->createAuctionTemplate($auction, $user);
                $contents[] = $content;
            }

            $contents_chunks = array_chunk($contents, 10);

            foreach ($contents_chunks as $contents_chunk) {
                $tmpMessageBuilder =
                    FlexMessageBuilder::builder()
                        ->setAltText($altText)
                        ->setContents(
                            CarouselContainerBuilder::builder()->setContents($contents_chunk)
                        );
                $messageBuilder->add($tmpMessageBuilder);
            }

            return $messageBuilder;
        } else {
            return new TextMessageBuilder("沒有拍賣會");
        }
    }

    public function confirmSetAutoBid($lotId, $user, $bidderId)
    {
        $user->lineMode()->updateOrCreate([
            'user_id' => $bidderId,
            'mode' => 0,
            'step' => 0,
            'extra_info' => $lotId . ',' . $bidderId
        ]);

        return new TextMessageBuilder('請輸入您的自動出價，我們將會在接下來的步驟與您確認出價。');
    }

    public function initMode($user)
    {
        $user->lineMode()->delete();
    }

    public function linkRichMenu($lineUserId)
    {
        return $this->bot->linkRichMenu($lineUserId, $this->richMenuId);
    }

    public function replyMessage($request, $messageBuilder)
    {
        return $this->bot->replyMessage($request['events'][0]['replyToken'], $messageBuilder);
    }

    public function webhookVerify()
    {
        return response()->json(['success' => true, 'timestamp' => time(), 'statusCode' => 200, 'reason' => 'OK', 'detail' => '200']);
    }

    private function createLotTemplate($lot, $user, $text='')
    {

        $nextBids = app('App\Services\BidService')->getNextBids($lot->current_bid);
        $carbonPresenter = new CarbonPresenter;

        if($lot->bidRecords->count() != 0) {
            $topBidderId = $lot->bidRecords()->latest()->first()->bidder_id;

            if($topBidderId == $user->id) {
                $topBidderPresent = '您目前為最高出價者';
            } else {
                $topBidderPresent = ' ';
            }
        } else {
            $topBidderPresent = ' ';
        }

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
                            ->setColor('#003a6c')
                            ->setAction(new UriTemplateActionBuilder($lot->name, route('mart.lots.show', $lot)))
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::HORIZONTAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($carbonPresenter->lineCardPresent($lot->auction_end_at))
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM)
                            ->setFlex(1),
                        TextComponentBuilder::builder()
                            ->setText($topBidderPresent)
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM)
                            ->setFlex(1),
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($text.' ')
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM),
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText('目前 NT$'.number_format($lot->current_bid).'，需要繼續競標嗎？')
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
                    ->setAction(new UriTemplateActionBuilder($lot->name, route('mart.lots.show', $lot)))
            )
            ->setBody(
                $body
            );

        return $content;
    }

    public function validBid($lot, $user, $bid)
    {
        $now = Carbon::now();
        $startTime = $lot->auction_start_at;
        $endTime = $lot->auction_end_at;

        if ($now->between($startTime, $endTime) === false) {
            return new TextMessageBuilder("必須在拍賣時間內進行出價");
        } elseif(intval($bid) < $lot->next_bid) {
            return new TextMessageBuilder("出價價格需大於 NT$".$lot->next_bid."，請再次輸入出價");
        }  else {
            return true;
        }
    }

    public function buildLotMessage($lot, $user, $text = '')
    {
        return FlexMessageBuilder::builder()
            ->setAltText($lot->name)
            ->setContents(
                $this->createLotTemplate($lot, $user, $text)
            );
    }

    public function pushMessage($userLineId, $messageBuilder)
    {
        return $this->bot->pushMessage($userLineId, $messageBuilder);
    }

    private function createAuctionTemplate($auction, $user)
    {
        $carbonPresenter = new CarbonPresenter;
        $bodyContents =
            [
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('20px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($auction->name)
                            ->setWrap(false)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::MD)
                            ->setColor('#003a6c')
                            ->setAction(new UriTemplateActionBuilder($auction->name, route('mart.auctions.show', $auction)))
                    ]),
                BoxComponentBuilder::builder()
                    ->setLayout(ComponentLayout::VERTICAL)
                    ->setPaddingBottom('10px')
                    ->setContents([
                        TextComponentBuilder::builder()
                            ->setText($carbonPresenter->lineCardPresent($auction->last_lot_end_at))
                            ->setWrap(true)
                            ->setWeight(ComponentFontWeight::BOLD)
                            ->setSize(ComponentFontSize::SM)
                            ->setFlex(1),
                    ]),
                SeparatorComponentBuilder::builder(),
            ];

        $bodyContents[] = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setPaddingTop('10px')
            ->setContents([
                ButtonComponentBuilder::builder()
                    ->setStyle(ComponentButtonStyle::PRIMARY)
                    ->setHeight(ComponentButtonHeight::SM)
                    ->setColor('#003a6c')
                    ->setAction(
                        new PostbackTemplateActionBuilder("查看拍賣會物品", 'showAuctionAllLots,'.$auction->id.','.$user->id)
                    )
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
                    ->setUrl(asset($auction->lots->first()->main_image->url))
                    ->setAction(new UriTemplateActionBuilder($auction->name, route('mart.auctions.show', $auction)))
            )
            ->setBody(
                $body
            );


        return $content;
    }

    public function buildAuctionMessage($auction, $user, $text = '')
    {
        return FlexMessageBuilder::builder()
            ->setAltText($text)
            ->setContents(
                $this->createAuctionTemplate($auction, $user)
            );
    }

    public function buildBindConfirmMessage($user, $link)
    {
        return FlexMessageBuilder::builder()
            ->setAltText('帳號綁定')
            ->setContents(
                BubbleContainerBuilder::builder()
                    ->setBody(
                        BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::VERTICAL)
                            ->setSpacing(ComponentSpacing::SM)
                            ->setContents([
                                BoxComponentBuilder::builder()
                                    ->setLayout(ComponentLayout::VERTICAL)
                                    ->setPaddingBottom('10px')
                                    ->setContents([
                                        TextComponentBuilder::builder()
                                            ->setText('您好 '.$user->name.'，確定綁定帳號嗎？')
                                            ->setWrap(false)
                                            ->setWeight(ComponentFontWeight::BOLD)
                                            ->setSize(ComponentFontSize::MD)
                                            ->setColor('#003a6c')
                                    ]),
                                SeparatorComponentBuilder::builder(),
                                BoxComponentBuilder::builder()
                                    ->setLayout(ComponentLayout::VERTICAL)
                                    ->setPaddingTop('10px')
                                    ->setContents([
                                        ButtonComponentBuilder::builder()
                                            ->setStyle(ComponentButtonStyle::PRIMARY)
                                            ->setHeight(ComponentButtonHeight::SM)
                                            ->setColor('#003a6c')
                                            ->setAction(
                                                new UriTemplateActionBuilder("確定", $link)
                                            )
                                    ])
                            ])
                    )
            );
    }
}
