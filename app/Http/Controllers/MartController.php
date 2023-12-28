<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Jobs\HandleAuctionStart;
use App\Jobs\HandleBeforeAuctionStart;
use App\Services\AuctionService;
use App\Services\BannerService;
use App\Services\CategoryService;
use App\Services\EcpayService;
use App\Services\GomypayService;
use App\Services\LotService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MartController extends Controller
{
    private $lotService, $auctionService, $ecpayService, $orderService, $categoryService, $bannerService, $promotionService, $gomypayService;

    public function __construct(
        LotService $lotService,
        AuctionService $auctionService,
        EcpayService $ecpayService,
        OrderService $orderService,
        CategoryService $categoryService,
        BannerService $bannerService,
        PromotionService $promotionService,
        GomypayService $gomypayService
    ) {
        $this->lotService = $lotService;
        $this->auctionService = $auctionService;
        $this->ecpayService = $ecpayService;
        $this->orderService = $orderService;
        $this->categoryService = $categoryService;
        $this->bannerService = $bannerService;
        $this->promotionService = $promotionService;
        $this->gomypayService = $gomypayService;
    }

    public function showAuction($auctionId)
    {
        $auction = $this->auctionService->getAuction($auctionId);
        $customView = CustomClass::viewWithTitle(view('mart.auctions.show')->with('auction', $auction), $auction->name);
        return $customView;
    }

    public function showLot($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $categories = $this->categoryService->getCategories($lot);
        $carbon = Carbon::now();
        $premium = $this->promotionService->getPremiumRate(Auth::user());
        $auctions = $this->auctionService->getAllAuctions()->where('status', '!=', 2);
        $auctionId = $lot->auction_id;
        $auction = $this->auctionService->getAuction($auctionId);

        return CustomClass::viewWithTitle(view('mart.lots.show')
            ->with('mCategory', $categories[0])
            ->with('sCategory', $categories[1])
            ->with('lot', $lot)
            ->with('carbon', $carbon)
            ->with('premium', $premium)
            ->with('auctions', $auctions)
            ->with('auction', $auction), $lot->name);
    }

    public function showHomepage()
    {
        $banners = $this->bannerService->getAllBanners()->sortBy('index');
        $auctions = $this->auctionService->getAllAuctions()->where('status', '!=', 2);
        return view('home_page')->with('auctions', $auctions)->with('head', 'Home Page')->with('title', 'Antiquary')->with('banners', $banners);
    }

    public function payEcpayReceive(Request $request)
    {
        if($this->ecpayService->checkMacValue($request, 'sha256')){
            $orderId = $request->CustomField1;
            $this->orderService->hasPaid($request, $orderId);
            return '1|OK';
        }
    }

    public function payEcpayOrderReceive(Request $request)
    {
        $orderId = $request->CustomField1;
        return redirect()->route('account.orders.show', $orderId)->with('success', '付款完成');
    }

    public function indexMessages($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        return CustomClass::viewWithTitle(view('mart.chatroom.show')->with('order', $order), $order->lot->name);
    }

    public function sendMessage(Request $request, $orderId)
    {
        $this->orderService->sendMessage($request, $orderId);
    }

    public function haveRead(Request $request, $messageId)
    {
        $this->orderService->haveRead($messageId);
    }

    public function showWarning($title, $message)
    {
        return CustomClass::viewWithTitle(view('warning')->with('message', $message), $title);
    }

    public function searchLots(Request $request)
    {
        $result = $this->lotService->searchLots($request->q);
        return CustomClass::viewWithTitle(view('mart.lots.index')->with('lots', $result), $request->q.' 搜尋結果');
    }

    public function showMCategory($mCategoryId)
    {
        $mCategory = $this->categoryService->getCategory($mCategoryId);
        $sCategories = $mCategory->children()->get();
        return CustomClass::viewWithTitle(view('mart.m_categories.show')->with('mCategory', $mCategory)->with('sCategories', $sCategories), $mCategory->name);
    }

    public function  showSCategory($mCategoryId, $sCategoryId)
    {
        $mCategory = $this->categoryService->getCategory($mCategoryId);
        $sCategory = $this->categoryService->getCategory($sCategoryId);
        $lots = $sCategory->lots->whereIn('status', [20,21]);
        return CustomClass::viewWithTitle(view('mart.s_categories.show')->with('mCategory', $mCategory)->with('sCategory', $sCategory)->with('lots', $lots), $sCategory->name);
    }

    public function showAbout()
    {
        return CustomClass::viewWithTitle(view('about_us'), '關於我們');
    }

    public function showGuaranty()
    {
        return CustomClass::viewWithTitle(view('antiquary_guaranty'), '我們的保證');
    }

    public function showConsignmentAuctionNotes()
    {
        return CustomClass::viewWithTitle(view('consignment_auction_notes'), '委託拍賣須知');
    }

    public function showConsignmentAuctionTerms()
    {
        return CustomClass::viewWithTitle(view('consignment_auction_terms'), '委託拍賣條款');
    }

    public function showBiddingNotes()
    {
        return CustomClass::viewWithTitle(view('bidding_notes'), '競標須知');
    }

    public function test()
    {
        $order = $this->orderService->getOrder(1);
        dd($order->lot);
    }

    public function creditCardInfoCheck($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        if(config('app.env') == 'production') {
            $eOrderNum = 'antiquary'.$orderId;
        } else {
            $eOrderNum = 'test3'.$orderId;
        }
        return CustomClass::viewWithTitle(view('account.orders.pay_by_credit_card')->with('order', $order)->with('eOrderNum', $eOrderNum), '信用卡持有人資訊確認');
    }

    public function payGomypayReturn(Request $request)
    {

        if($request->result === '1') {#paid success
            if(config('app.env') == 'production') {
                $orderId = str_replace("antiquary", "", $request->e_orderno);
            } else {
                $orderId = str_replace("test3", "", $request->e_orderno);
            }

            $order = $this->orderService->getOrder($orderId);
            $result = $this->gomypayService->checkTransactionStatus($request, $order);

            if($result === 1) {#check pay is valid
                $this->orderService->hasPaid($request, $order->id);
                return redirect()->route('account.orders.show', $order->id)->with('notification', '付款完成');
            } else {
                return $this->showWarning('付款失敗', '付款檢查錯誤，請通知管理員');
            }
        } else {
            return $this->showWarning('付款失敗', $request->ret_msg);
        }
    }

    public function payGomypayCallback(Request $request)
    {
        Log::channel('ecpay')->info($request->toArray());
        return response('success', 200);
    }

    public function showPrivacyPolicy()
    {
        return CustomClass::viewWithTitle(view('privacy_policy'), '隱私政策');
    }

    public function showTerms()
    {
        return CustomClass::viewWithTitle(view('terms'), '使用者條款');
    }

    public function showBiddingRules()
    {
        return CustomClass::viewWithTitle(view('bidding_rules'), '競標增額');
    }
}
