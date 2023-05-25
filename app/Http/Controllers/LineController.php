<?php

namespace App\Http\Controllers;

use App\Events\LineBindSuccess;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\LineService;
use App\Services\LotService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class LineController extends Controller
{
    private $lotService, $userService, $bidService, $lineService, $auctionService;

    public function __construct(
        LotService $lotService,
        UserService $userService,
        BidService $bidService,
        LineService $lineService,
        AuctionService $auctionService
    ) {
        $this->lotService = $lotService;
        $this->userService = $userService;
        $this->bidService = $bidService;
        $this->lineService = $lineService;
        $this->auctionService = $auctionService;
    }

    public function webhook (Request $request)
    {
        switch ($request['events'][0]['type']) {
            case 'message':
                $messageText = $request['events'][0]['message']['text'];

                #1建立目錄 取得richmenu ID 2上傳圖片 3連結帳戶
                if (mb_substr($messageText, 0, 4, "UTF-8") == "高級指令") {#管理員指令
                    switch (mb_substr($messageText, 4, 8, "UTF-8")) {
                        case '建立目錄':
                            $response = $this->lineService->createRichMenu();
                            break;
                        case '上傳目錄圖片':
                            $response = $this->lineService->uploadRichMenuImage();
                            break;
                        case '下載圖片':
                            $response = $this->lineService->downloadRichMenuImage();
                            break;
                        case '列出目錄':
                            $response = $this->lineService->getRichMenuList();
                            break;
                        case '連結帳戶':
                            $response = $this->lineService->adminLinkRichMenu();
                            break;
                        case '取得目錄':
                            $response = $this->lineService->getRichMenu();
                            break;
                        case '刪除目錄':
                            $response = $this->lineService->deleteRichMenu();
                            break;
                        default:
                            $messageBuilder = $this->lineService->dealUnknownCommand();
                            break;
                    }
                    Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());
                } elseif (ctype_digit($messageText)) {
                    $lineUserId = $request['events'][0]['source']['userId'];
                    $user = $this->userService->getUserByOauth('line', $lineUserId);
                    $lineMode = $user->lineMode;

                    if ($lineMode !== null) {
                        if ($lineMode->mode == 0 && $lineMode->step == 0) {

                            $extraInfo = explode(',', $lineMode->extra_info);
                            $lotId = $extraInfo[0];

                            $lot = $this->lotService->getLot($lotId);

                            $messageBuilder = $this->lineService->dealLineMode($lineMode, $lot, $user, $messageText);
                        } else {
                            $this->lineService->deleteLineMode($lineMode);
                        }
                    }
                } elseif (mb_substr($messageText, 0, 4, "UTF-8") == "link") {
                    $lineUserId = $request['events'][0]['source']['userId'];
                    $lineVerifyCode = mb_substr($messageText, 4, 8, "UTF-8");
                    $linkToken = $this->lineService->buildLinkToken($lineUserId);

                    $nonce = hash('sha512', Carbon::now());
                    $userId = Cache::get('line-'.$lineVerifyCode);
                    if($userId === null) {
                        $messageBuilder = $this->lineService->buildMessage('驗證碼錯誤，請到網頁產生新的驗證碼');
                    } else {
                        $user = $this->userService->getUser($userId);

                        $user->update(['line_nonce'=>$nonce]);
                        $link = $this->lineService->generateLineBidLink($linkToken, $nonce);
                        $messageBuilder = $this->lineService->buildBindConfirmMessage($user, $link);
                    }
                }
                else {
                    $messageBuilder = $this->lineService->dealUnknownCommand();
                }
                break;
            case 'postback':
                $data = explode(",", $request['events'][0]['postback']['data']);

                switch ($data[0]) {
                    case 'lineBidConfirm':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $bid = $data[3];
                        $lot = $this->lotService->getLot($lotId);
                        $nextBid = $lot->current_bid + $this->bidService->bidRule($lot->current_bid);

                        $messageBuilder = $this->lineService->confirmLineBid($lotId, $bidderId, $bid, $lot, $nextBid);
                        break;
                    case 'lineBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $bid = $data[3];
                        $this->bidService->manualBidLot($lotId, $bidderId, $bid);#遇上更高的自動出價沒提醒####################
                        $lot = $this->lotService->getLot($lotId);
                        if ($bid < $lot->reserve_price) {
                            $messageBuilder = $this->lineService->buildMessage('NT$' . number_format($bid) . ' 出價成功，出價未達底價，需到達底價物品才會被拍賣。');

                        } else {
                            $messageBuilder = $this->lineService->buildMessage('NT$' . number_format($bid) . ' 出價成功');
                        }
                        break;
                    case 'showBiddingLot':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $lots = $this->lotService->getBiddingLot($user);

                        $messageBuilder = $this->lineService->buildMultiLotsTemplate($user, $lots, '競標的物品');
                        break;
                    case 'showFavorites':

                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $favorites = $user->favoriteLots()->whereIn('status', [20, 21])->get();

                        $messageBuilder = $this->lineService->buildMultiLotsTemplate($user, $favorites, '追蹤的物品');
                        break;
                    case 'confirmSetAutoBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $user = $this->userService->getUser($bidderId);

                        $messageBuilder = $this->lineService->confirmSetAutoBid($lotId, $user, $bidderId);
                        break;
                    case 'initMode':
                        $lineUserId = $request['events'][0]['source']['userId'];
                        $user = $this->userService->getUserByOauth('line', $lineUserId);
                        $this->lineService->initMode($user);
                        $messageBuilder = $this->lineService->buildMessage('"已取消"');

                        break;
                    case 'setAutoBid':
                        $lotId = $data[1];
                        $bidderId = $data[2];
                        $autoBid = $data[3];
                        $bid = $this->bidService->autoBidLot($lotId, $bidderId, $autoBid);
                        $lot = $this->lotService->getLot($lotId);
                        $user = $this->userService->getUser($bidderId);

                        $request = new Request();
                        $request->setMethod('POST');
                        $request->request->add([
                            'lotId' => $lotId,
                            'bidderId' => $bidderId,
                            'bid' => $autoBid
                        ]);

                        $validator = app(MemberController::class)->autoBidValidation($request, $lot);

                        if ($validator->fails()) {
                            $message = $validator->getMessageBag()->first();
                        } else {
                            if ($bid !== false) {
                                $message = '已設置自動出價 NT$' . number_format($autoBid) . '，已幫您出價 NT$' . number_format($bid);

                            } else {
                                $message = '已修改自動出價金額為 NT$' . number_format($autoBid);
                            }

                            if ($bid < $lot->reserve_price) {
                                $message .= '，出價成功，出價未達底價，需到達底價物品才會被拍賣。';
                            } else {
                                $message .= '，出價成功。';
                            }
                        }

                        $messageBuilder = $this->lineService->buildMessage($message);
                        $this->lineService->initMode($user);
                        break;
                    case 'showAuctionAllLots':
                        $auctionId = $data[1];
                        $userId = $data[2];
                        $auction = $this->auctionService->getAuction($auctionId);
                        $user = $this->userService->getUser($userId);
                        $lots = $auction->lots;
                        $messageBuilder = $this->lineService->buildMultiLotsTemplate($user, $lots, $auction->name.' 的物品');
                        break;
                    default:
                        $messageBuilder =  $this->lineService->buildMessage('postback default');
                        break;
                }
                break;


            case 'accountLink':
                $result = $request['events'][0]['link']['result'];
                if ($result == 'ok') {
                    $nonce = $request['events'][0]['link']['nonce'];
                    $line_id = $request['events'][0]['source']['userId'];
                    $this->userService->confirmBind($nonce, $line_id);

                    $response = $this->lineService->linkRichMenu($line_id);
                    Log::channel('line')->info($response->getHTTPStatus() . ' ' . $response->getRawBody());

                    $messageBuilder = $this->lineService->buildMessage('綁定成功，請重新進入聊天室以查看功能');

                    #dynamic change status
                    $user = $this->userService->getUserByOauth('line', $line_id);
                    LineBindSuccess::dispatch($user);
                }
                break;
            case 'follow':
                #$lineUserId = $request['events'][0]['source']['userId'];
                $messageBuilder = $this->lineService->buildMessage('請直接輸入綁定碼綁定帳戶');
                break;
        }
        $response = $this->lineService->replyMessage($request, $messageBuilder);
        if ($response->isSucceeded()) {
            Log::channel('line')->info('Succeeded!');
        } else {
            Log::channel('line')->warning($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }
    }

    public function webhookVerify()
    {
        Log::channel('line')->info(date("Y-m-d h:i:sa", time()));
        Return $this->lineService->webhookVerify();
    }
}
