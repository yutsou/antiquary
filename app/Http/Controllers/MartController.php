<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Services\AuctionService;
use App\Services\BannerService;
use App\Services\CategoryService;
use App\Services\EcpayService;
use App\Services\LotService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MartController extends Controller
{
    private $lotService, $auctionService, $ecpayService, $orderService, $categoryService, $bannerService;

    public function __construct(
        LotService $lotService,
        AuctionService $auctionService,
        EcpayService $ecpayService,
        OrderService $orderService,
        CategoryService $categoryService,
        BannerService $bannerService
    ) {
        $this->lotService = $lotService;
        $this->auctionService = $auctionService;
        $this->ecpayService = $ecpayService;
        $this->orderService = $orderService;
        $this->categoryService = $categoryService;
        $this->bannerService = $bannerService;
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
        $carbon = Carbon::now();

        return CustomClass::viewWithTitle(view('mart.lots.show')->with('lot', $lot)->with('carbon', $carbon), $lot->name);
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

    public function showWarning()
    {
        dd('warning');
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

    public function showSCategory($mCategoryId, $sCategoryId)
    {
        $mCategory = $this->categoryService->getCategory($mCategoryId);
        $sCategory = $this->categoryService->getCategory($sCategoryId);
        $lots = $sCategory->lots->where('process', 3);
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
        #$this->lotService->test();
        #$this->transactionRecordService->test();
        $lot = $this->lotService->getLot(1);
        $carbon = Carbon::now();
        return CustomClass::viewWithTitle(view('test')->with('lot', $lot)->with('carbon', $carbon), 'Test');
    }
}
