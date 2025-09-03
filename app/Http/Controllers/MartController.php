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
use App\Services\LineService;
use App\Services\LotService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class MartController extends Controller
{
    private $lotService, $auctionService, $ecpayService, $orderService, $categoryService, $bannerService, $promotionService, $gomypayService, $lineService;

    public function __construct(
        LotService $lotService,
        AuctionService $auctionService,
        EcpayService $ecpayService,
        OrderService $orderService,
        CategoryService $categoryService,
        BannerService $bannerService,
        PromotionService $promotionService,
        GomypayService $gomypayService,
        LineService $lineService
    ) {
        $this->lotService = $lotService;
        $this->auctionService = $auctionService;
        $this->ecpayService = $ecpayService;
        $this->orderService = $orderService;
        $this->categoryService = $categoryService;
        $this->bannerService = $bannerService;
        $this->promotionService = $promotionService;
        $this->gomypayService = $gomypayService;
        $this->lineService = $lineService;
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
        $productsByCategory = $this->lotService->getPublishedLotsByMainCategories();
        $description = 'Antiquary 是一個綜合古董舊物線上購物平台，我們出售古傢俬、燈飾、瓷器、水晶、藝術品、設計原創物品、裝飾品、收藏品、珍貴物品的平台。';

        return view('home_page')
            ->with('auctions', $auctions)
            ->with('head', 'Home Page')
            ->with('title', 'Antiquary')
            ->with('banners', $banners)
            ->with('productsByCategory', $productsByCategory)
            ->with('description', $description);
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
        $lot = $order->orderItems->first() ? $order->orderItems->first()->lot : null;
        $messages = $order->messages()->with('user')->orderBy('created_at', 'asc')->get();
        $this->orderService->messagesHaveRead($messages);
        $with = ['order'=>$order, 'lot'=>$lot, 'messages'=>$messages];

        return CustomClass::viewWithTitle(view('mart.chatroom.show')->with($with), $lot ? $lot->name : '聊天室');
    }

    public function sendMessage(Request $request, $orderId)
    {
        $this->orderService->sendMessage($request, $orderId);
    }

    public function haveRead(Request $request, $messageId)
    {
        $this->orderService->haveRead($messageId);
    }

    public function showWarning(Request $request)
    {
        $title = $request->session()->get('title', 'Warning');
        $message = $request->session()->get('message', 'An unexpected error occurred.');
        return CustomClass::viewWithTitle(view('warning')->with('message', $message), $title);
    }

    public function searchLots(Request $request)
    {
        // 驗證搜索參數
        $request->validate([
            'q' => 'nullable|string|max:255',
        ]);

        // 如果沒有搜索關鍵字，返回空結果
        if (empty($request->q)) {
            return CustomClass::viewWithTitle(
                view('mart.lots.index')
                    ->with('lots', collect())
                    ->with('searchQuery', ''),
                '搜尋結果'
            );
        }

        $result = $this->lotService->searchLots($request->q);

        // 分離拍賣商品和直賣商品
        $auctionLots = $result->where('auction_end_at', '!=', null);
        $directLots = $result->where('auction_end_at', null);

        // 合併兩個集合並保持排序
        $combinedLots = $auctionLots->concat($directLots);

        // 分頁設置
        $perPage = 20;
        $currentPage = $request->get('page', 1);

        // 手動分頁
        $totalItems = $combinedLots->count();
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedLots = $combinedLots->slice($offset, $perPage);

        // 創建分頁器
        $paginator = new LengthAwarePaginator(
            $paginatedLots,
            $totalItems,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
                'query' => $request->query(),
            ]
        );

        return CustomClass::viewWithTitle(
            view('mart.lots.index')
                ->with('lots', $paginatedLots)
                ->with('paginator', $paginator)
                ->with('searchQuery', $request->q),
            $request->q.' 搜尋結果'
        );
    }

    public function showProduct($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $categories = $this->categoryService->getCategories($lot);
        $description = $lot->description;
        $productsByCategory = $this->lotService->getPublishedLotsBySubCategories($lotId, $lot->sub_category->id);

        return CustomClass::viewWithTitle(view('mart.products.show')->with('lot', $lot)->with('mCategory', $categories[0])->with('sCategory', $categories[1])->with('description', $description)->with('productsByCategory', $productsByCategory), $lot->name);
    }

    public function showMCategory($mCategoryId, Request $request)
    {
        $mCategory = $this->categoryService->getCategory($mCategoryId);
        $sCategories = $mCategory->children()->get();
        $allLots = $mCategory->lots->whereIn('status', [20,21,61])->sortBy('auction_end_at');

        // 分離拍賣商品和直賣商品
        $auctionLots = $allLots->where('auction_end_at', '!=', null);
        $directLots = $allLots->where('auction_end_at', null);

        // 合併兩個集合並保持排序
        $combinedLots = $auctionLots->concat($directLots);

        // 分頁設置
        $perPage = 20;
        $currentPage = $request->get('page', 1);

        // 手動分頁
        $totalItems = $combinedLots->count();
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedLots = $combinedLots->slice($offset, $perPage);

        // 創建分頁器
        $paginator = new LengthAwarePaginator(
            $paginatedLots,
            $totalItems,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        return CustomClass::viewWithTitle(
            view('mart.m_categories.show')
                ->with('mCategory', $mCategory)
                ->with('sCategories', $sCategories)
                ->with('lots', $paginatedLots)
                ->with('paginator', $paginator),
            $mCategory->name
        );
    }

    public function showSCategory($mCategoryId, $sCategoryId, Request $request)
    {
        $mCategory = $this->categoryService->getCategory($mCategoryId);
        $sCategory = $this->categoryService->getCategory($sCategoryId);
        $allLots = $sCategory->lots->whereIn('status', [20,21,61])->sortBy('auction_end_at');

        // 分離拍賣商品和直賣商品
        $auctionLots = $allLots->where('auction_end_at', '!=', null);
        $directLots = $allLots->where('auction_end_at', null);

        // 合併兩個集合並保持排序
        $combinedLots = $auctionLots->concat($directLots);

        // 分頁設置
        $perPage = 20;
        $currentPage = $request->get('page', 1);

        // 手動分頁
        $totalItems = $combinedLots->count();
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedLots = $combinedLots->slice($offset, $perPage);

        // 創建分頁器
        $paginator = new LengthAwarePaginator(
            $paginatedLots,
            $totalItems,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        return CustomClass::viewWithTitle(
            view('mart.s_categories.show')
                ->with('mCategory', $mCategory)
                ->with('sCategory', $sCategory)
                ->with('lots', $paginatedLots)
                ->with('paginator', $paginator),
            $sCategory->name
        );
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

    public function creditCardInfoCheck($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        if(config('app.env') == 'production') {
            $eOrderNum = 'antiquary'.Carbon::now()->getTimestamp().'-'.$orderId;
        } else {
            $eOrderNum = 'test'.Carbon::now()->getTimestamp().'-'.$orderId;
        }
        return CustomClass::viewWithTitle(view('account.orders.pay_by_credit_card')->with('order', $order)->with('eOrderNum', $eOrderNum), '信用卡持有人資訊確認');
    }

    public function payGomypayReturn(Request $request)
    {
        if($request->result === '1') {#paid success
            $eOrderNo = explode("-", $request->e_orderno);
            $orderId = $eOrderNo[1];
            $order = $this->orderService->getOrder($orderId);
            $result = $this->gomypayService->checkTransactionStatus($request, $order);

            if($result === 1) {#check pay is valid
                if($order->status == 10) {
                    $this->orderService->hasPaid($request, $order->id);
                    return redirect()->route('account.orders.show', $order->id)->with('notification', '付款完成');
                } else {
                    $this->orderService->hasPaid($request, $order->id, 53);
                    return redirect()->route('mart.warning.show')->with('title', '爭議')->with('message', '付款逾時，請通知管理員');

                    //return $this->showWarning('爭議', '付款逾時，請通知管理員');
                }

            } else {
                return redirect()->route('mart.warning.show')->with('title', '付款失敗')->with('message', '付款檢查錯誤，請通知管理員');

                //return $this->showWarning('付款失敗', '付款檢查錯誤，請通知管理員');
            }
        } else {
            return redirect()->route('mart.warning.show')->with('title', '付款失敗')->with('message', $request->ret_msg);

            //return $this->showWarning('付款失敗', $request->ret_msg);
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

    public function showReturnAndExchangePolicy()
    {
        return CustomClass::viewWithTitle(view('return_and_exchange_policy'), '退換貨政策');
    }

    public function showTest()
    {
        return CustomClass::viewWithTitle(view('test'), 'test');
    }

    public function postTest(Request $request)
    {
        dd($request->images);
    }

    public function payLineAuthorize(Request $request)
    {
        $orderId = $request->orderId;
        if(config('app.env') == 'production') {
            $lineIdPrefix = config('services.line.line_order_id_prefix');
        } else {
            $lineIdPrefix = 'test-1-';
        }
        $orderId = str_replace($lineIdPrefix, '', $orderId);
        $order = $this->orderService->getOrder($orderId);
        $result = $this->lineService->confirmPayment($request, $order);
        Log::channel('line')->info($request->toArray());
        if ($result) {
            $this->orderService->hasPaid($request, $orderId);
            return redirect()->route('account.orders.show', $orderId)->with('success', '付款完成');
        } else {
            $this->orderService->failPayment($request, $orderId);
            return redirect()->route('account.orders.show', $orderId)->with('warning', '付款失敗，請重新付款');
        }
    }
}
