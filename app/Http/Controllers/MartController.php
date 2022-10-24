<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Services\AuctionService;
use App\Services\CategoryService;
use App\Services\EcpayService;
use App\Services\LotService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MartController extends Controller
{
    private $lotService, $auctionService, $ecpayService, $orderService, $categoryService;

    public function __construct(
        LotService $lotService,
        AuctionService $auctionService,
        EcpayService $ecpayService,
        OrderService $orderService,
        CategoryService $categoryService,
    ) {
        $this->lotService = $lotService;
        $this->auctionService = $auctionService;
        $this->ecpayService = $ecpayService;
        $this->orderService = $orderService;
        $this->categoryService = $categoryService;
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
        $auctions = $this->auctionService->getAllAuctions()->where('status', '!=', 2);
        return view('home_page')->with('auctions', $auctions)->with('head', 'Home Page')->with('title', 'Jason Auction');
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

    public function test()
    {
        $this->lotService->test();
        #$this->transactionRecordService->test();
    }
}
