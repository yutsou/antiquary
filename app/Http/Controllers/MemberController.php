<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\CartService;
use App\Services\CategoryService;
use App\Services\EcpayService;
use App\Services\GomypayService;
use App\Services\ImageService;
use App\Services\LotService;
use App\Services\OrderService;
use App\Services\DeliveryMethodService;
use App\Services\SpecificationService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\MergeShippingRequest;
use App\Models\MergeShippingItem;

class MemberController extends Controller
{
    private $categoryService, $lotService, $specificationService, $deliveryMethodService, $imageService, $userService, $orderService, $ecpayService, $bidService, $gomypayService, $cartService;

    public function __construct(
        CategoryService $categoryService,
        LotService $lotService,
        SpecificationService $specificationService,
        DeliveryMethodService $deliveryMethodService,
        ImageService $imageService,
        UserService $userService,
        OrderService $orderService,
        EcpayService $ecpayService,
        BidService $bidService,
        GomypayService $gomypayService,
        CartService $cartService
    ) {
        $this->categoryService = $categoryService;
        $this->lotService = $lotService;
        $this->specificationService = $specificationService;
        $this->deliveryMethodService = $deliveryMethodService;
        $this->imageService = $imageService;
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->ecpayService = $ecpayService;
        $this->bidService = $bidService;
        $this->gomypayService = $gomypayService;
        $this->cartService = $cartService;
    }

    public function showDashboard()
    {
        $ownerApplicationNoticeCount = $this->userService->getOwnerApplicationNoticeCount(Auth::user()->id);
        $ownerSellingLotNoticeCount = $this->userService->getOwnerSellingLotNoticeCount(Auth::user()->id);
        $ownerOrderNoticeCount = $this->userService->getOrderLotNoticeCount(Auth::user()->id);
        $ownerReturnedLotNoticeCount = $this->userService->getReturnedLotNoticeCount(Auth::user()->id);
        $customView = CustomClass::viewWithTitle(view('account.dashboard')
            ->with('ownerApplicationNoticeCount', $ownerApplicationNoticeCount)
            ->with('ownerSellingLotNoticeCount', $ownerSellingLotNoticeCount)
            ->with('ownerOrderNoticeCount', $ownerOrderNoticeCount)
            ->with('ownerReturnedLotNoticeCount', $ownerReturnedLotNoticeCount), '會員中心');
        return $customView;
    }

    public function showFavorites()
    {
        $user = Auth::user();
        $favorites =$user->favoriteLots()->whereIn('status', [20, 21, 61])->get();
        $customView = CustomClass::viewWithTitle(view('account.favorites.index')->with('lots', $favorites), '感興趣的物品');
        return $customView;
    }

    public function indexOrders()
    {
        $user = Auth::user();
        $orders = $user->orders->sortByDesc('created_at');
        $customView = CustomClass::viewWithTitle(view('account.orders.index')->with('orders', $orders), '訂單');
        return $customView;
    }

    public function showOrder($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $orderItems = $order->orderItems;
        $logisticInfo = $this->orderService->getLogisticInfo($order, 0);
        $with = ['order'=>$order, 'orderItems'=>$orderItems, 'logisticInfo'=>$logisticInfo];
        $customView = CustomClass::viewWithTitle(view('account.orders.show')->with($with), '訂單#'.$orderId);
        return $customView;
    }

    public function editOrder($orderId)
    {
        $customView = CustomClass::viewWithTitle(view('account.orders.payment_method_choice')->with('orderId', $orderId), '選擇付款方式');
        return $customView;
    }

    public function updateOrder(Request $request, $orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        if ($request->paymentMethod !== null && $request->deliveryMethod === null) {
            $paymentMethod = $request->paymentMethod;
            $customView = CustomClass::viewWithTitle(view('account.orders.delivery_method_choice')->with('order', $order)->with('paymentMethod', $paymentMethod), '選擇取貨方式');
        } elseif ($request->paymentMethod !== null && $request->deliveryMethod !== null) {

            $subtotal = $order->orderItems->sum(function($item) { return $item->lot->current_bid; });

            $output = [
                'order'=>$order,
                'paymentMethod'=>intval($request->paymentMethod),
                'deliveryMethod'=>intval($request->deliveryMethod),
                'deliveryCost'=>$request->deliveryCost,
                'recipientName'=>$request->recipient_name,
                'recipientPhone'=>$request->recipient_phone,
                'subtotal'=>$subtotal,
                'total'=>intval($subtotal)+intval($order->premium)+intval($request->deliveryCost)
            ];

            switch ($request->deliveryMethod){
                case 1:
                    $output = array_merge($output, [
                        'zipcode'=>$request->zipcode,
                        'county'=>$request->county,
                        'district'=>$request->district,
                        'address'=>$request->address,
                    ]);
                    break;
                case 2:
                    $output = array_merge($output, [
                        'country'=>$request->country,
                        'countryCode'=>$request->country_selector_code,
                        'crossBoardAddress'=>$request->cross_board_address,
                    ]);
                    break;
            }

            $customView = CustomClass::viewWithTitle(view('account.orders.check')->with($output), '訂單確認');
        } else {
            $customView = CustomClass::viewWithTitle(view('account.orders.payment_method_choice')->with('orderId', $orderId), '選擇支付方式');
        }

        return $customView;
    }

    public function confirmOrder(Request $request, $orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $subtotal = $order->orderItems->sum(function($item) { return $item->lot->current_bid; });
        $this->orderService->confirmOrder($request, $orderId);
        return redirect()->route('account.orders.show', $orderId);
    }

    public function completeOrder($orderId)
    {
        $this->orderService->completeOrder($orderId);
        #return redirect()->route('account.orders.show', $orderId);
    }

    public function pay($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        // 進入付款流程時，先將狀態設為 10
        $this->orderService->updateOrderStatus(10, $order);
        $this->ecpayService->creditCardPay($order);
        exit;
    }

    public function showAtmPayInfo($orderId) {
        $order = $this->orderService->getOrder($orderId);
        return CustomClass::viewWithTitle(view('account.orders.pay_by_atm')->with('order', $order), 'ATM 轉帳資訊');
    }

    public function noticeAtmPay(Request $request, $orderId) {
        $this->orderService->noticeRemit($request, $orderId, 0);
        return redirect()->route('account.orders.show', $orderId);
    }

    public function createLot()
    {
        $mainCategories = $this->categoryService->getRoots();
        $customView = CustomClass::viewWithTitle(view('account.applications.create')->with('mainCategories', $mainCategories), '填寫物品資料');
        return $customView;
    }

    protected function lotValidation($request, $imageValid)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
            'mainCategoryId' => 'required',
            'specificationValues.*' => 'required',
            'description' => 'required',
        ];

        if($imageValid === 0)
        {
            $rules['images'] = 'required';
        }

        if(isset($request->checkReversePrice)) {
            $rules['reserve_price'] = 'required|gte:3000';
        }
        if(isset($request->crossBorderDelivery)) {
            $rules['crossBorderDeliveryCost'] = 'required';
        }
        if(isset($request->homeDelivery)) {
            $rules['homeDeliveryCost'] = 'required';
        }
        if($request->faceToFace === null && $request->crossBorderDelivery === null && $request->homeDelivery === null) {
            $rules['deliveryMethods'] = 'required';
        }
        $messages = [
            'name.required'=>'未填寫商品名稱',
            'mainCategoryId.required'=>'未選擇物品分類',
            'specificationValues.*.required' => '規格未填寫完整',
            'description.required' => '未填寫描述',
            'images.required' => '未上傳圖片',
            'reserve_price.required' => '未填寫底價',
            'reserve_price.gte' => '底價需大於等於3000',
            'homeDeliveryCost.required'=> '未填寫台灣區物流費用',
            'crossBorderDeliveryCost.required' => '未填寫跨境物流費用',
            'deliveryMethods.required' => '未選擇運送方式'
        ];
        $validator = Validator::make($input, $rules, $messages);

        return $validator;
    }

    public function storeLot(Request $request)
    {
        $validator = $this->lotValidation($request, 0);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400); // 400 being the HTTP code for an invalid request.
        }

        $lotId = $this->lotService->createLot($request);

        $this->lotService->syncCategoryLot($lotId, [$request->mainCategoryId=>['main'=>1]]);

        $this->specificationService->createSpecifications($request, $lotId);

        $this->deliveryMethodService->createDeliveryMethods($request, $lotId);

        $syncImageIds = array();

        foreach ($request->images as $index=>$file) {
            $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
            $alt = null;
            $imageable_id = $lotId;
            $imageable_type = 'App\Models\Lot';
            $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
            $syncImageIds[$imageId] = ['main'=>$index];
        }

        $this->lotService->syncLotImages($lotId, $syncImageIds);

        CustomClass::sendTemplateNotice(Auth::user()->id, 1, 0, $lotId);

        return Response::json(array(
            'success' => route('account.applications.index'),
            'errors' => false
        ), 200);
    }

    public function editLot($lotId)
    {
        $mainCategories = $this->categoryService->getRoots();
        $lot = $this->lotService->getLot($lotId);
        $customView = CustomClass::viewWithTitle(view('account.applications.edit')->with('lot', $lot)->with('mainCategories', $mainCategories), $lotId);
        return $customView;
    }

    public function updateLot(Request $request, $lotId)
    {
        $validator = $this->lotValidation($request, 1);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400); // 400 being the HTTP code for an invalid request.
        }

        $lot = $this->lotService->getLot($lotId);

        if (isset($request->images)) {
            $olderImageIds = $lot->blImages->pluck('id')->toArray();
            $lot->blImages()->detach($olderImageIds);
            foreach($olderImageIds as $imageId)
            {
                $this->imageService->deleteImage($imageId);
            }
            $newImageIds = array();
            foreach ($request->images as $index=>$file) {
                $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
                $alt = null;
                $imageable_id = $lotId;
                $imageable_type = 'App\Models\Lot';
                $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
                $newImageIds[$imageId] = ['main'=>$index];

            }
            $this->lotService->attachLotImages($lotId, $newImageIds);
        } else {
            $this->imageService->changeImagesOrder($request->imageOrderArray, $lot);
        }

        if ($request->mainCategoryId == $lot->main_category->id) {
            #判斷是否變更了主分類，沒變更的話則單純修改規格
            $this->specificationService->updateSpecifications($request, $lotId);
        } else {
            #判斷是否變更了主分類，如變更的話則清除規格，並產生新規格
            $this->lotService->syncCategoryLot($lotId, [$request->mainCategoryId]);#同步分類
            $lot->specifications()->delete();#清除舊的規格
            $this->specificationService->createSpecifications($request, $lotId);#創建新的規格
        }
        $this->lotService->updateLot($request, $lotId);
        $this->deliveryMethodService->syncDeliveryMethods($request, $lot);#同步分類



        return Response::json(array(
            'success' => route('account.applications.index'),
            'errors' => false
        ), 200);
    }

    public function ajaxSubCategories($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        $subCategories = $mainCategory->defaultSpecificationTitles;
        return $subCategories;
    }

    public function indexLots($type)
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lots = match ($type) {
            0 => $this->lotService->getApplicationLots($user),
            1 => $this->lotService->getSellingLots($user),
        };
        return $lots;
    }

    public function indexApplications()
    {
        $lots = $this->indexLots(0)->sortByDesc('created_at');
        return CustomClass::viewWithTitle(view('account.applications.index')->with('lots', $lots), '審核中的申請');
    }


    public function indexSellingLots()
    {
        $lots = $this->indexLots(1)->sortByDesc('created_at');
        return CustomClass::viewWithTitle(view('account.selling_lots.index')->with('lots', $lots), '正在委賣的物品');
    }

    public function indexFinishedLots()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lots = $this->lotService->getFinishedLots($user)->sortByDesc('created_at');
        return CustomClass::viewWithTitle(view('account.finished_lots.index')->with('lots', $lots), '完成委賣的物品');
    }

    public function indexReturnedLots()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lots = $this->lotService->getReturnedLots($user)->sortByDesc('created_at');
        return CustomClass::viewWithTitle(view('account.returned_lots.index')->with('lots', $lots), '退回的物品');
    }

    public function editReturnedLot($lotId)
    {
        return CustomClass::viewWithTitle(view('account.returned_lots.edit')->with('lotId', $lotId), '物品退還資訊');
    }

    public function updateReturnedLot(Request $request, $lotId)
    {
        $input = $request->all();

        $rules = [
            "addressee_name" => 'required',
            "addressee_phone" => 'required',
            "county" => 'required',
            "district" => 'required',
            "address" => 'required',
        ];

        $messages = [
            'addressee.required'=>'未填寫收件人姓名',
            'addressee_phone.required'=>'未填寫收件人電話',
            'county.required'=>'未選擇縣市',
            'district.required'=>'未選擇鄉鎮市',
            'address.required'=>'未填寫地址'
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $this->lotService->returnedLotLogistic($request, $lotId);
    }

    public function storeApplicationLogisticInfo(Request $request, $lotId)
    {
        $input = $request->all();

        $rules = [
            'logistic_name' => 'required',
            'tracking_code' => 'required',
        ];

        $messages = [
            'logistic_name.required'=>'未填寫物流公司',
            'tracking_code.required' => '未填寫物流追蹤碼',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $this->lotService->storeApplicationLogisticInfo($request, $lotId);

        return response('success', 200);
    }

    public function manualBidValidation($request, $lot)
    {
        $input = $request->all();
        $input['bidTime'] = Carbon::now();
        $input['bidderStatus'] = $this->userService->getUser($request->bidderId)->status;
        $nextBid = $lot->next_bid;
        $rules = [
            'bidTime' => 'after:'.$lot->auction_start_at.'|before:'.$lot->auction_end_at,
            'bid' => 'required|gte:'.$nextBid,
            'bidderId' => Rule::notIn([$lot->owner_id]),
            'bidderStatus' => Rule::notIn([1,3])
        ];
        $messages = [
            'bid.required'=>'請輸入價格',
            'bid.gte'=>'出價必須大於下一個最小出價金額',
            'bidTime.after' => '必須在拍賣會時間內出價',
            'bidTime.before' => '必須在拍賣會時間內出價',
            'bidderId.not_in' => '您不能對自己的物品出價',
            'bidderStatus.not_in' => '帳號已被封鎖，目前無法競標'
        ];

        ###判斷起標價
        if ($lot->current_bid == 0 &&  $request->bid < $lot->starting_price) {
            $rules['bid'] = 'required|gte:'.$lot->starting_price;
            $messages['bid.gte'] =  '出價必須大於起標價';
        }

        ###判斷maxAutoBid是不是自己
        $lotMaxAutoBid = $this->bidService->getLotMaxAutoBid($lot->id);
        if(isset($lotMaxAutoBid) && $lotMaxAutoBid->user_id == $request->bidderId) {
            $rules['bid'] = 'required|gte:'.$lotMaxAutoBid->bid;
            $messages['bid.gte'] =  '已設定更高的自動出價';
        }

        return Validator::make($input, $rules, $messages);
    }

    public function manualBid(Request $request)
    {
        $lotId = $request->lotId;
        $lot = $this->lotService->getLot($lotId);

        $validator = $this->manualBidValidation($request, $lot);
        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400); // 400 being the HTTP code for an invalid request.
        } else {
            $bidderId = $request->bidderId;
            $bid = $request->bid;
            $this->bidService->manualBidLot($lotId, $bidderId, $bid);

            if($request->bid < $lot->reserve_price) {
                $type = 'warning';
                $successMessage = '出價未達底價，需到達底價物品才會被拍賣。';
            } else {
                $type = 'success';
                $successMessage = '';
            }

            return Response::json(array(
                'type' => $type,
                'text' => $successMessage,
                'errors' => false
            ), 200);
        }
    }

    public function autoBidValidation($request, $lot)
    {
        $input = $request->all();
        $input['bidTime'] = Carbon::now();
        $input['bidderStatus'] = $this->userService->getUser($request->bidderId)->status;
        $nextBid = $lot->current_bid + $this->bidService->bidRule($lot->current_bid);
        $rules = [
            'bidTime' => 'after:'.$lot->auction_start_at.'|before:'.$lot->auction_end_at,
            'bid' => 'required|gte:'.$nextBid,
            'bidderId' => Rule::notIn([$lot->owner_id]),
            'bidderStatus' => Rule::notIn([1,3]),
        ];
        $messages = [
            'bid.required'=>'請輸入價格',
            'bid.gte'=>'出價必須大於下一個最小出價金額',
            'bidTime.after' => '必須在拍賣會時間內出價',
            'bidTime.before' => '必須在拍賣會時間內出價',
            'bidderId.not_in' => '您不能對自己的物品出價',
            'bidderStatus.not_in' => '帳號已被封鎖，目前無法競標',
        ];

        ###判斷起標價
        if ($lot->current_bid == 0 &&  $request->bid < $lot->starting_price) {
            $rules['bid'] = 'required|gte:'.$lot->starting_price;
            $messages['bid.gte'] =  '出價必須大於起標價，';
        }

        $bidderLotAutoBid = $this->bidService->getBidderLotAutoBid($request->bidderId, $lot);
        if($bidderLotAutoBid != 0) {
            $rules['bid'] = 'required|gt:'.intval($bidderLotAutoBid);
            $messages['bid.gt'] =  '已設定更高或相同的自動出價';
        }


        return Validator::make($input, $rules, $messages);
    }

    public function autoBid(Request $request)
    {
        $lotId = $request->lotId;
        $lot = $this->lotService->getLot($lotId);

        $validator = $this->autoBidValidation($request, $lot);
        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400); // 400 being the HTTP code for an invalid request.
        } else {
            $bidderId = $request->bidderId;
            $bid = $request->bid;
            $this->bidService->autoBidLot($lotId, $bidderId, $bid);

            if($request->bid < $lot->reserve_price) {
                $type = 'warning';
                $successMessage = '出價未達底價，需到達底價物品才會被拍賣。';
            } else {
                $type = 'success';
                $successMessage = '';
            }

            return Response::json(array(
                'type' => $type,
                'text' => $successMessage,
                'errors' => false,
                'myAutoBid' => $bid
            ), 200);
        }
    }

    public function ajaxHandleFavorite(Request $request)
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lotId = $request->lotId;
        $status = $this->lotService->handleFavorite($user, $lotId);
        return $status;
    }

    public function editProfile()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $customView = CustomClass::viewWithTitle(view('account.profiles.edit')->with('user', $user), '帳戶設定');
        return $customView;
    }

    public function updateProfile(Request $request)
    {
        $input = $request->all();

        $rules = [
            "name" => 'required',
            "email" => 'required',
            "phone" => 'required',
            "birthday" => 'required',
            "address" => "max:255"
        ];

        $messages = [
            'name.required'=>'未填寫真實姓名',
            'email.required'=>'未填寫電子郵件',
            'phone.required'=>'未填寫手機號碼',
            'birthday.required'=>'未填寫生日',
            'address.max'=>'地址最大位元為255',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $this->userService->updateProfile($input);
    }

    public function noticeShipping(Request $request, $orderId)
    {
        $this->orderService->storeShippingLogistic($request, $orderId);
        $this->orderService->noticeShipping($orderId);
        return back()->with('notification', '通知成功');
    }

    public function showShippingInfo($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $logisticInfo = $this->lotService->getLogisticInfo($order, 0);
        $customView = CustomClass::viewWithTitle(view('account.orders.shipping_info')->with('order', $order)->with('logisticInfo', $logisticInfo), '運送資訊');
        return $customView;
    }

    public function noticeArrival($orderId)
    {
        $this->orderService->noticeArrival($orderId);
        return back()->with('notification', '通知成功');
    }

    public function editEmail()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $customView = CustomClass::viewWithTitle(view('account.profiles.edit_email')->with('user', $user), '信箱驗證');
        return $customView;
    }

    public function editPhone()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $customView = CustomClass::viewWithTitle(view('account.profiles.edit_phone')->with('user', $user), '手機驗證');
        return $customView;
    }

    public function editSeller()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $customView = CustomClass::viewWithTitle(view('account.profiles.edit_seller')->with('user', $user), '賣家設定');
        return $customView;
    }

    public function updateSeller(Request $request)
    {
        $input = $request->all();

        $rules = [
            "bank_name" => 'required',
            "bank_branch_name" => 'required',
            "bank_account_name" => 'required',
            "bank_account_number" => 'required|numeric',
        ];

        $messages = [
            'bank_name.required'=>'未填寫受款銀行名稱',
            'bank_branch_name.required'=>'未填寫分行名稱',
            'bank_account_name.required'=>'未填寫戶名',
            'bank_account_number.required'=>'未填寫帳號',
            'bank_account_number.numeric'=>'帳號只接受數字'
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $this->userService->updateProfile($input);
    }

    public function indexNotices()
    {
        $notices = Auth::user()->notices->sortByDesc('created_at');

        return CustomClass::viewWithTitle(view('account.notices.index')->with('notices', $notices), '通知');
    }

    public function indexUnreadNotices()
    {
        $unreadNotices = Auth::user()->unreadNotices()->get()->sortByDesc('created_at');
        return CustomClass::viewWithTitle(view('account.unread_notices.index')->with('unreadNotices', $unreadNotices), '未讀通知');
    }

    public function readNotices()
    {
        Auth::user()->unreadNotices()->update(['read_at'=>Carbon::now()]);
    }

    public function createApplicationLogisticInfo($lotId)
    {
        return CustomClass::viewWithTitle(view('account.application_logistic_infos.create')->with('lotId', $lotId), '查看/填寫運送資訊');
    }

    public function editUnsoldLot($lotId)
    {
        return CustomClass::viewWithTitle(view('account.unsold_lots.edit')->with('lotId', $lotId), '流標/棄標 處理');
    }

    public function handleUnsoldLot(Request $request, $lotId)
    {
        if($request->unsold_method == 'logistic') {
            if($request->unsold_method == 'logistic') {
                $input = $request->all();

                $rules = [
                    "addressee_name" => 'required',
                    "addressee_phone" => 'required',
                    "county" => 'required',
                    "district" => 'required',
                    "address" => 'required',
                ];

                $messages = [
                    'addressee_name.required'=>'未填寫收件人姓名',
                    'addressee_phone.required'=>'未填寫收件人電話',
                    'county.required'=>'未選擇縣市',
                    'district.required'=>'未選擇鄉鎮市',
                    'address.required'=>'未填寫地址'
                ];
                $validator = Validator::make($input, $rules, $messages);
                if ($validator->fails()) {
                    return Response::json(array(
                        'success' => false,
                        'errors' => $validator->getMessageBag()->toArray()
                    ), 400); // 400 being the HTTP code for an invalid request.
                }
                $this->lotService->unsoldLotLogistic($request, $lotId);
                $url = route('account.returned_lots.index');
            }
        } else {
            $this->lotService->reBiding($lotId);
            $url = route('account.selling_lots.index');
        }

        return Response::json(array(
            'success' => $url,
            'errors' => false
        ), 200);


    }

    public function indexBiddingLots()
    {
        $user = Auth::user();
        $lots = $this->lotService->getBiddingLot($user);
         return CustomClass::viewWithTitle(view('account.bidding_lots.index')->with('lots', $lots), '您的競標');
    }

    public function handleProduct(Request $request, $lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        if ($request->paymentMethod !== null && $request->deliveryMethod === null) {
            $paymentMethod = $request->paymentMethod;
            $customView = CustomClass::viewWithTitle(view('account.products.delivery_method_choice')->with('paymentMethod', $paymentMethod)->with('lot', $lot), '選擇取貨方式');
        } elseif ($request->paymentMethod !== null && $request->deliveryMethod !== null) {

            $output = [
                'lot'=>$lot,
                'paymentMethod'=>intval($request->paymentMethod),
                'deliveryMethod'=>intval($request->deliveryMethod),
                'delivery_cost'=>$request->delivery_cost,
                'recipientName'=>$request->recipient_name,
                'recipientPhone'=>$request->recipient_phone,
                'subtotal'=>$lot->reserve_price,
                'total'=>intval($lot->reserve_price)+intval($request->delivery_cost)
            ];

            switch ($request->deliveryMethod){
                case 1:
                    $output = array_merge($output, [
                        'zipcode'=>$request->zipcode,
                        'county'=>$request->county,
                        'district'=>$request->district,
                        'address'=>$request->address,
                    ]);
                    break;
                case 2:
                    $output = array_merge($output, [
                        'country'=>$request->country,
                        'countryCode'=>$request->country_selector_code,
                        'crossBoardAddress'=>$request->cross_board_address,
                    ]);
                    break;
            }

            $customView = CustomClass::viewWithTitle(view('account.products.check')->with($output), '訂單確認');

        } else {
            $customView = CustomClass::viewWithTitle(view('account.products.payment_method_choice')->with('lot', $lot), '選擇付款方式');
        }
        return $customView;
    }

    public function confirmProduct(Request $request, $lotId)
    {
        try {
            $lot = $this->lotService->getLot($lotId);
            $order = $this->orderService->createProductOrder($lot);
            $this->orderService->confirmOrder($request, $order->id);
            return redirect()->route('account.orders.pay', $order->id);
        } catch (\Exception $e) {
            // 處理庫存不足等錯誤
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cartValidation($request, $lot, $userId)
    {
        $input = $request->all();
        $inventory = $lot->inventory ?? 0;

        // 查目前 cart 已有數量（假設你是 cart_items 設計，或 cart 表中一筆一商品）
        $currentCartQuantity = \App\Models\Cart::where('user_id', $userId)
            ->where('lot_id', $lot->id)
            ->value('quantity') ?? 0;

        // 計算這次加入後的總數量
        $afterAdd = $currentCartQuantity + intval($input['quantity'] ?? 0);

        // 自訂驗證規則
        $rules = [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($inventory, $currentCartQuantity) {
                    // 新增後總數量不能超過庫存
                    if (($currentCartQuantity + $value) > $inventory) {
                        $fail('購物車累計數量不可超過現有庫存（剩餘 '.$inventory.' 件）');
                    }
                }
            ],
        ];
        $messages = [
            'quantity.required' => '請輸入購買數量',
            'quantity.integer'  => '數量必須為整數',
            'quantity.min'      => '購買數量至少 1 件',
        ];

        return Validator::make($input, $rules, $messages);
    }

    public function storeCart(Request $request)
    {
        $user = Auth::user();
        $lot = $this->lotService->findOrFail($request->lot_id);

        // 驗證
        $validator = $this->cartValidation($request, $lot, $user->id);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422); // 400 being the HTTP code for an invalid request.
        }

        // 驗證通過才加入購物車
        $this->cartService->addToCart($user->id, $request->lot_id, $request->quantity);

        return Response::json(array(
            'success' => url()->previous(),
            'errors' => false
        ), 200);
    }

    public function showCart()
    {
        $user = Auth::user();
        $cartItems = $this->cartService->getCartItems($user->id);
        $mergeShippingRequests = $user->mergeShippingRequests()
            ->whereNotIn('status', [MergeShippingRequest::STATUS_COMPLETED, MergeShippingRequest::STATUS_REMOVED])
            ->with('items.lot.blImages')
            ->orderBy('created_at', 'desc')
            ->get();
        $customView = CustomClass::viewWithTitle(
            view('account.cart.show')
                ->with('cartItems', $cartItems)
                ->with('mergeShippingRequests', $mergeShippingRequests),
            '購物車'
        );
        return $customView;
    }

    public function updateCart(Request $request)
    {
        $user = Auth::user();
        $lot = $this->lotService->findOrFail($request->lot_id);

        // 驗證數量
        $input = $request->all();
        $inventory = $lot->inventory ?? 0;

        $rules = [
            'lot_id' => 'required|exists:lots,id',
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($inventory) {
                    if ($value > $inventory) {
                        $fail('購買數量不可超過現有庫存（剩餘 '.$inventory.' 件）');
                    }
                }
            ],
        ];

        $messages = [
            'lot_id.required' => '商品 ID 不能為空',
            'lot_id.exists' => '商品不存在',
            'quantity.required' => '請輸入購買數量',
            'quantity.integer' => '數量必須為整數',
            'quantity.min' => '購買數量至少 1 件',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422);
        }

        // 更新購物車數量
        $cartItem = $this->cartService->updateCartQuantity($user->id, $request->lot_id, $request->quantity);

        if (!$cartItem) {
            return Response::json(array(
                'success' => false,
                'errors' => ['cart' => '購物車中找不到此商品']
            ), 404);
        }

        return Response::json(array(
            'success' => true,
            'message' => '購物車數量更新成功'
        ), 200);
    }

    public function removeCart(Request $request)
    {
        $user = Auth::user();
        $lotId = $request->input('lot_id');

        // 驗證商品是否存在
        $lot = $this->lotService->findOrFail($lotId);

        try {
            // 從購物車中移除商品
            $removed = $this->cartService->removeCartItem($user->id, $lotId);

            if (!$removed) {
                return Response::json(array(
                    'success' => false,
                    'errors' => ['cart' => '購物車中找不到此商品']
                ), 404);
            }

            // 取得更新後的購物車數量
            $cartCount = $this->cartService->getCartCount($user->id);

            return Response::json(array(
                'success' => true,
                'message' => '商品已從購物車中移除',
                'cart_count' => $cartCount
            ), 200);
        } catch (\Exception $e) {
            return Response::json(array(
                'success' => false,
                'errors' => ['cart' => $e->getMessage()]
            ), 422);
        }
    }

    public function cartDeliveryMethodChoice(Request $request)
    {
        $user = Auth::user();
        $selectedLotIds = $request->input('selected_lots', []);

        if (empty($selectedLotIds)) {
            return redirect()->route('account.cart.show')->with('error', '請選擇要結帳的商品');
        }

        // 獲取選中的商品
        $selectedLots = $this->cartService->getSelectedCartItems($user->id, $selectedLotIds);

        if ($selectedLots->isEmpty()) {
            return redirect()->route('account.cart.show')->with('error', '選中的商品不存在於購物車中');
        }

        // 檢查庫存是否足夠
        $lotService = app(LotService::class);
        $inventoryItems = [];
        foreach ($selectedLots as $lot) {
            $inventoryItems[] = [
                'lot_id' => $lot->id,
                'quantity' => $lot->cart_quantity
            ];
        }

        $inventoryResult = $lotService->checkMultipleInventory($inventoryItems);
        if (!$inventoryResult['sufficient']) {
            $errorMessage = '以下商品庫存不足：';
            foreach ($inventoryResult['insufficient_items'] as $item) {
                $errorMessage .= "\n{$item['lot_name']} - 需要 {$item['requested_quantity']} 件，庫存 {$item['available_inventory']} 件";
            }
            return redirect()->route('account.cart.show')->with('error', $errorMessage);
        }

        // 計算所有商品的運送方式交集
        $deliveryMethodsArr = [];
        foreach ($selectedLots as $lot) {
            $deliveryMethodsArr[] = $lot->deliveryMethods->pluck('code')->toArray();
        }
        $commonDeliveryCodes = array_reduce($deliveryMethodsArr, function($carry, $item) {
            return $carry === null ? $item : array_intersect($carry, $item);
        }, null);

        // 計算總數量
        $totalQuantity = $selectedLots->sum('cart_quantity');

        // 計算宅配總費用 (code=1)
        $homeDeliveryTotal = 0;
        if (!empty($commonDeliveryCodes) && in_array(1, $commonDeliveryCodes)) {
            foreach ($selectedLots as $lot) {
                $method = $lot->deliveryMethods->where('code', 1)->first();
                if ($method) {
                    $homeDeliveryTotal += $method->cost;
                }
            }
        }

        // 計算境外物流總費用 (code=2)
        $crossBorderTotal = 0;
        if (!empty($commonDeliveryCodes) && in_array(2, $commonDeliveryCodes)) {
            foreach ($selectedLots as $lot) {
                $method = $lot->deliveryMethods->where('code', 2)->first();
                if ($method) {
                    $crossBorderTotal += $method->cost;
                }
            }
        }

        return CustomClass::viewWithTitle(
            view('account.cart.delivery_method_choice')
                ->with('selectedLots', $selectedLots)
                ->with('selectedLotIds', $selectedLotIds)
                ->with('commonDeliveryCodes', $commonDeliveryCodes)
                ->with('totalQuantity', $totalQuantity)
                ->with('homeDeliveryTotal', $homeDeliveryTotal)
                ->with('crossBorderTotal', $crossBorderTotal),
            '選擇運送方式'
        );
    }

    public function cartPaymentMethodChoice(Request $request)
    {
        $user = Auth::user();
        $selectedLotIds = $request->input('selected_lots', []);
        $deliveryMethod = $request->input('delivery_method');
        $deliveryCost = $request->input('delivery_cost');
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');
        $zipCode = $request->input('zip_code');
        $county = $request->input('county');
        $district = $request->input('district');
        $address = $request->input('address');
        $country = $request->input('country');
        $countrySelectorCode = $request->input('country_selector_code');
        $crossBoardAddress = $request->input('cross_board_address');

        if (empty($selectedLotIds) || !isset($deliveryMethod)) {
            return redirect()->route('account.cart.show')->with('error', '請選擇要結帳的商品和運送方式');
        }

        // 獲取選中的商品
        $selectedLots = $this->cartService->getSelectedCartItems($user->id, $selectedLotIds);

        if ($selectedLots->isEmpty()) {
            return redirect()->route('account.cart.show')->with('error', '選中的商品不存在於購物車中');
        }

        // 再次檢查庫存是否足夠（以防在運送方式選擇期間庫存發生變化）
        $lotService = app(LotService::class);
        $inventoryItems = [];
        foreach ($selectedLots as $lot) {
            $inventoryItems[] = [
                'lot_id' => $lot->id,
                'quantity' => $lot->cart_quantity
            ];
        }

        $inventoryResult = $lotService->checkMultipleInventory($inventoryItems);
        if (!$inventoryResult['sufficient']) {
            $errorMessage = '以下商品庫存不足：';
            foreach ($inventoryResult['insufficient_items'] as $item) {
                $errorMessage .= "\n{$item['lot_name']} - 需要 {$item['requested_quantity']} 件，庫存 {$item['available_inventory']} 件";
            }
            return redirect()->route('account.cart.show')->with('error', $errorMessage);
        }

        return CustomClass::viewWithTitle(
            view('account.cart.payment_method_choice')
                ->with('selectedLots', $selectedLots)
                ->with('selectedLotIds', $selectedLotIds)
                ->with('deliveryMethod', $deliveryMethod)
                ->with('deliveryCost', $deliveryCost)
                ->with('recipientName', $recipientName)
                ->with('recipientPhone', $recipientPhone)
                ->with('zipCode', $zipCode)
                ->with('county', $county)
                ->with('district', $district)
                ->with('address', $address)
                ->with('country', $country)
                ->with('countrySelectorCode', $countrySelectorCode)
                ->with('crossBoardAddress', $crossBoardAddress),
            '選擇付款方式'
        );
    }

    public function cartCheck(Request $request)
    {
        $user = Auth::user();
        $selectedLotIds = $request->input('selected_lots', []);
        $paymentMethod = $request->input('payment_method');
        $deliveryMethod = $request->input('delivery_method');
        $deliveryCost = $request->input('delivery_cost');

        if (empty($selectedLotIds) || !isset($paymentMethod) || !isset($deliveryMethod)) {
            return redirect()->route('account.cart.show')->with('error', '請完成所有必要選擇');
        }

        // 獲取選中的商品
        $selectedLots = $this->cartService->getSelectedCartItems($user->id, $selectedLotIds);

        if ($selectedLots->isEmpty()) {
            return redirect()->route('account.cart.show')->with('error', '選中的商品不存在於購物車中');
        }

        // 計算總計（包含運費）
        $subtotal = $selectedLots->sum('subtotal');
        $total = $subtotal + $deliveryCost;

        // 準備運送資訊
        $deliveryInfo = $this->prepareDeliveryInfo($request, [$deliveryMethod]);

        return CustomClass::viewWithTitle(
            view('account.cart.check')
                ->with('selectedLots', $selectedLots)
                ->with('selectedLotIds', $selectedLotIds)
                ->with('paymentMethod', $paymentMethod)
                ->with('deliveryMethod', $deliveryMethod)
                ->with('deliveryCost', $deliveryCost)
                ->with('subtotal', $subtotal)
                ->with('total', $total)
                ->with($deliveryInfo),
            '訂單確認'
        );
    }

    public function cartConfirm(Request $request)
    {
        $user = Auth::user();
        $selectedLotIds = $request->input('selected_lots', []);
        $paymentMethod = $request->input('payment_method');
        $deliveryMethod = $request->input('delivery_method');
        $deliveryCost = $request->input('delivery_cost');
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');

        if (empty($selectedLotIds) || !isset($paymentMethod) || !isset($deliveryMethod) || !isset($deliveryCost) || !isset($recipientName) || !isset($recipientPhone)) {
            return redirect()->route('account.cart.show')->with('error', '請完成所有必要選擇');
        }

        try {
            // 創建訂單
            $order = $this->orderService->createCartOrder($user->id, $selectedLotIds, $request->all());

            // 從購物車中移除已購買的商品（包括競標商品）
            try {
                $this->cartService->removeSelectedItems($user->id, $selectedLotIds, false);
            } catch (\Exception $e) {
                // 如果移除失敗，記錄錯誤但不中斷流程
                \Log::warning('Failed to remove items from cart after order creation: ' . $e->getMessage());
            }

            // 根據付款方式導向不同頁面
            if ($paymentMethod == 0) {
                // 信用卡付款 - 導向付款頁面
                return redirect()->route('account.orders.pay', $order->id);
            } elseif ($paymentMethod == 1) {
                // ATM轉帳 - 導向ATM付款資訊頁面
                return redirect()->route('account.atm_pay_info.show', $order->id);
            } else {
                // 預設導向付款頁面
                return redirect()->route('account.orders.pay', $order->id);
            }
        } catch (\Exception $e) {
            // 處理庫存不足等錯誤
            return redirect()->route('account.cart.show')->with('error', $e->getMessage());
        }
    }

    private function prepareDeliveryInfo(Request $request, $delivery_methods)
    {
        $info = [
            'recipientName' => $request->input('recipient_name'),
            'recipientPhone' => $request->input('recipient_phone'),
        ];

        // 檢查是否有需要地址的運送方式
        $hasHomeDelivery = in_array(1, $delivery_methods);
        $hasCrossBorder = in_array(2, $delivery_methods);

        if ($hasHomeDelivery) {
            $info['zipCode'] = $request->input('zip_code');
            $info['county'] = $request->input('county');
            $info['district'] = $request->input('district');
            $info['address'] = $request->input('address');
        }

        if ($hasCrossBorder) {
            $info['country'] = $request->input('country');
            $info['countryCode'] = $request->input('country_selector_code');
            $info['crossBoardAddress'] = $request->input('cross_board_address');
        }

        return $info;
    }

    public function createMergeShippingRequest(Request $request)
    {
        $user = Auth::user();
        $selectedLotIds = $request->input('selected_lots', []);
        $delivery_method = $request->input('delivery_method');

        if (empty($selectedLotIds) || !in_array($delivery_method, ['1-merge', '2-merge'])) {
            return redirect()->route('account.cart.show')->with('error', '請選擇要合併運費的商品和運送方式');
        }

        // 獲取選中的商品
        $selectedLots = $this->cartService->getSelectedCartItems($user->id, $selectedLotIds);

        if ($selectedLots->isEmpty()) {
            return redirect()->route('account.cart.show')->with('error', '選中的商品不存在於購物車中');
        }

        // 計算原本運費總計
        $originalShippingFee = 0;
        $deliveryMethodCode = $delivery_method === '1-merge' ? 1 : 2;

        foreach ($selectedLots as $lot) {
            $method = $lot->deliveryMethods->where('code', $deliveryMethodCode)->first();
            if ($method) {
                $originalShippingFee += $method->cost;
            }
        }

        // 建立合併運費請求
        $mergeRequest = MergeShippingRequest::create([
            'user_id' => $user->id,
            'original_shipping_fee' => $originalShippingFee,
            'delivery_method' => $deliveryMethodCode,
            'status' => MergeShippingRequest::STATUS_PENDING,
        ]);

        // 建立合併運費商品記錄並扣減庫存
        foreach ($selectedLots as $lot) {
            $method = $lot->deliveryMethods->where('code', $deliveryMethodCode)->first();
            if ($method) {
                MergeShippingItem::create([
                    'merge_shipping_request_id' => $mergeRequest->id,
                    'lot_id' => $lot->id,
                    'quantity' => $lot->cart_quantity,
                    'original_shipping_fee' => $method->cost,
                ]);

                // 扣減庫存 - 直接操作數據庫，因為 merge request 需要對所有商品扣減庫存
                $lot->decrement('inventory', $lot->cart_quantity);

                // 檢查庫存是否為0，如果是則下架商品
                $updatedLot = $lot->fresh();
                if ($updatedLot->inventory <= 0) {
                    $updatedLot->update(['status' => 60]); // 60 是下架狀態
                }
            }
        }

        // 保存地址信息到 logistic_records
        $this->storeMergeShippingRequestLogisticInfo($request, $mergeRequest->id);

        // 從購物車中移除已申請合併運費的商品（包括競標商品）
        $this->cartService->removeSelectedItems($user->id, $selectedLotIds, false);

        CustomClass::sendTemplateNotice(1, 8, 0, $mergeRequest->id, 1);

        return redirect()->route('account.cart.show')->with('success', '合併運費請求已送出，請等待拍賣師處理');
    }

    public function mergeShippingDeliveryMethodEdit($requestId)
    {
        $user = Auth::user();
        $mergeRequest = MergeShippingRequest::with(['items.lot.blImages', 'items.lot.deliveryMethods', 'logisticRecords'])
            ->where('user_id', $user->id)
            ->where('id', $requestId)
            ->where('status', MergeShippingRequest::STATUS_APPROVED)
            ->firstOrFail();

        return CustomClass::viewWithTitle(
            view('account.cart.merge_shipping.delivery_method_edit')
                ->with('mergeRequest', $mergeRequest),
            '合併運費結帳'
        );
    }

    public function mergeShippingDeliveryUpdate(Request $request, $requestId)
    {
        $user = Auth::user();
        $mergeRequest = MergeShippingRequest::with(['items.lot.blImages', 'items.lot.deliveryMethods', 'logisticRecords'])
            ->where('user_id', $user->id)
            ->where('id', $requestId)
            ->where('status', MergeShippingRequest::STATUS_APPROVED)
            ->firstOrFail();

        $deliveryMethod = $request->input('delivery_method');
        $deliveryCost = $request->input('delivery_cost');
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');
        $zipCode = $request->input('zip_code');
        $county = $request->input('county');
        $district = $request->input('district');
        $address = $request->input('address');
        $country = $request->input('country');
        $countrySelectorCode = $request->input('country_selector_code');
        $crossBoardAddress = $request->input('cross_board_address');


        if (!isset($deliveryMethod)) {
            return redirect()->route('account.cart.merge_shipping.delivery_method.edit', $requestId)->with('error', '請選擇運送方式');
        }

        return CustomClass::viewWithTitle(
            view('account.cart.merge_shipping.payment_choice')
                ->with('mergeRequest', $mergeRequest)
                ->with('deliveryMethod', $deliveryMethod)
                ->with('deliveryCost', $deliveryCost)
                ->with('recipientName', $recipientName)
                ->with('recipientPhone', $recipientPhone)
                ->with('zipCode', $zipCode)
                ->with('county', $county)
                ->with('district', $district)
                ->with('address', $address)
                ->with('country', $country)
                ->with('countrySelectorCode', $countrySelectorCode)
                ->with('crossBoardAddress', $crossBoardAddress),
            '選擇付款方式'
        );
    }

    public function mergeShippingCheck(Request $request, $requestId)
    {
        $user = Auth::user();
        $mergeRequest = MergeShippingRequest::with(['items.lot.blImages', 'items.lot.deliveryMethods', 'logisticRecords'])
            ->where('user_id', $user->id)
            ->where('id', $requestId)
            ->where('status', MergeShippingRequest::STATUS_APPROVED)
            ->firstOrFail();

        $paymentMethod = $request->input('paymentMethod');
        $deliveryMethod = $request->input('delivery_method');
        $deliveryCost = $request->input('delivery_cost');
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');
        $zipCode = $request->input('zip_code');
        $county = $request->input('county');
        $district = $request->input('district');
        $address = $request->input('address');
        $country = $request->input('country');
        $countrySelectorCode = $request->input('country_selector_code');
        $crossBoardAddress = $request->input('cross_board_address');

        if (!isset($paymentMethod) || !isset($deliveryMethod)) {
            return redirect()->route('account.cart.merge_shipping_checkout', $requestId)->with('error', '請完成所有必要選擇');
        }

        // 計算總計
        $subtotal = $mergeRequest->items->sum(function($item) {
            return $item->lot->reserve_price * $item->quantity;
        });
        $total = $subtotal + $mergeRequest->new_shipping_fee;

        // 準備運送資訊
        $deliveryInfo = $this->prepareMergeShippingDeliveryInfo($request);

        return CustomClass::viewWithTitle(
            view('account.cart.merge_shipping.check')
                ->with('mergeRequest', $mergeRequest)
                ->with('paymentMethod', $paymentMethod)
                ->with('deliveryMethod', $deliveryMethod)
                ->with('deliveryCost', $mergeRequest->new_shipping_fee)
                ->with('subtotal', $subtotal)
                ->with('total', $total)
                ->with('recipientName', $recipientName)
                ->with('recipientPhone', $recipientPhone)
                ->with('zipCode', $zipCode)
                ->with('county', $county)
                ->with('district', $district)
                ->with('address', $address)
                ->with('country', $country)
                ->with('countrySelectorCode', $countrySelectorCode)
                ->with('crossBoardAddress', $crossBoardAddress),
            '訂單確認'
        );
    }



    public function mergeShippingConfirm(Request $request, $requestId)
    {
        $user = Auth::user();
        $mergeRequest = MergeShippingRequest::with(['items.lot.blImages', 'items.lot.deliveryMethods', 'logisticRecords'])
            ->where('user_id', $user->id)
            ->where('id', $requestId)
            ->where('status', MergeShippingRequest::STATUS_APPROVED)
            ->firstOrFail();

        try {
            // 創建訂單
            $order = $this->orderService->createMergeShippingOrder($user->id, $mergeRequest, $request->all());

            // 更新合併運費請求狀態為已完成
            $mergeRequest->update(['status' => MergeShippingRequest::STATUS_COMPLETED]);

            // 根據付款方式導向不同頁面
            $paymentMethod = $request->input('payment_method');

            if ($paymentMethod == 0) {
                // 信用卡付款 - 導向付款頁面
                return redirect()->route('account.orders.pay', $order->id);
            } elseif ($paymentMethod == 1) {
                // ATM轉帳 - 導向ATM付款資訊頁面
                return redirect()->route('account.atm_pay_info.show', $order->id);
            } else {
                // 預設導向付款頁面
                return redirect()->route('account.orders.pay', $order->id);
            }
        } catch (\Exception $e) {
            // 處理庫存不足等錯誤
            dd($e);
            return redirect()->route('account.cart.merge_shipping_checkout', $requestId)->with('error', $e->getMessage());
        }
    }

    private function prepareMergeShippingDeliveryInfo(Request $request)
    {
        $info = [
            'recipientName' => $request->input('recipient_name'),
            'recipientPhone' => $request->input('recipient_phone'),
        ];

        $deliveryMethod = $request->input('delivery_method');

        if ($deliveryMethod == 1) {
            $info['zipCode'] = $request->input('zip_code');
            $info['county'] = $request->input('county');
            $info['district'] = $request->input('district');
            $info['address'] = $request->input('address');
        } elseif ($deliveryMethod == 2) {
            $info['country'] = $request->input('country');
            $info['countryCode'] = $request->input('country_selector_code');
            $info['crossBoardAddress'] = $request->input('cross_board_address');
        }

        return $info;
    }

    public function removeMergeShippingRequest(Request $request, $requestId)
    {
        $user = Auth::user();

        // 查找屬於該用戶的合併運費請求
        $mergeRequest = MergeShippingRequest::with(['items'])
            ->where('user_id', $user->id)
            ->where('id', $requestId)
            ->firstOrFail();

        // 不刪除記錄，而是設定狀態為已移除
        $mergeRequest->update(['status' => MergeShippingRequest::STATUS_REMOVED]);

        return Response::json(array(
            'success' => true,
            'message' => '合併運費請求已移除'
        ), 200);
    }

    private function storeMergeShippingRequestLogisticInfo(Request $request, $requestId)
    {
        $input = [
            'type' => 4, // 主物流資訊 - logistic_records type 定義：0=application(正常流程賣場寄給拍賣會), 1=returned(退還競標物品), 2=unsold(競標失敗退還), 3=未付款退回, 4=merge request
            'addressee_name' => $request->input('recipient_name'),
            'addressee_phone' => $request->input('recipient_phone'),
        ];

        $deliveryMethod = $request->input('delivery_method');
        $deliveryMethodCode = $deliveryMethod === '1-merge' ? 1 : 2;

        if ($deliveryMethodCode == 1) {
            $input['delivery_zip_code'] = $request->input('zip_code') ?? null;
            $input['county'] = $request->input('county') ?? null;
            $input['district'] = $request->input('district') ?? null;
            $input['delivery_address'] = $request->input('address') ?? null;
        } elseif ($deliveryMethodCode == 2) {
            $input['cross_board_delivery_country'] = $request->input('country') ?? null;
            $input['cross_board_delivery_country_code'] = $request->input('country_selector_code') ?? null;
            $input['cross_board_delivery_address'] = $request->input('cross_board_address') ?? null;
        }

        // 使用 MergeShippingRequestRepository 創建 logistic record
        $mergeShippingRequestRepository = app(\App\Repositories\MergeShippingRequestRepository::class);
        return $mergeShippingRequestRepository->createLogisticRecord($input, $requestId);
    }

}
