<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Services\AuctionService;
use App\Services\BidService;
use App\Services\CategoryService;
use App\Services\EcpayService;
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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    private $categoryService, $lotService, $specificationService, $deliveryMethodService, $imageService, $userService, $orderService, $ecpayService, $bidService;

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
        $favorites =$user->favoriteLots()->whereIn('status', [20, 21])->get();
        $customView = CustomClass::viewWithTitle(view('account.favorites.index')->with('favorites', $favorites), '感興趣的物品');
        return $customView;
    }

    public function indexOrders()
    {
        $user = Auth::user();
        $orders = $user->orders->sortByDesc('created_at');
        $customView = CustomClass::viewWithTitle(view('account.orders.index')->with('orders', $orders), '已得標的物品');
        return $customView;
    }

    public function showOrder($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $lot = $order->lot;
        $logisticInfo = $this->orderService->getLogisticInfo($order, 0);
        $with = ['order'=>$order, 'lot'=>$lot, 'logisticInfo'=>$logisticInfo];
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

            $subtotal = $order->lot->current_bid;

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
        $this->orderService->confirmOrder($request, $orderId);
        return redirect()->route('account.orders.pay', $orderId);
    }

    public function completeOrder($orderId)
    {
        $this->orderService->completeOrder($orderId);
        #return redirect()->route('account.orders.show', $orderId);
    }

    public function pay($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        switch ($order->payment_method) {
            case 0:#信用卡付款
                $this->ecpayService->creditCardPay($order);
                break;
            case 1:#ATM轉帳
                return redirect()->route('account.atm_pay_info.show', $orderId);
        }
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
            $rules['mainImage'] = 'required';
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
            'mainImage.required' => '未上傳主要圖片',
            'images.required' => '未上傳其他圖片',
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

        $file = $request->mainImage;
        $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
        $alt = null;
        $imageable_id = $lotId;
        $imageable_type = 'App\Models\Lot';

        $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);

        $syncImageIds = array($imageId=>['main'=>1]);

        foreach ($request->images as $file) {
            $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
            $alt = null;
            $imageable_id = $lotId;
            $imageable_type = 'App\Models\Lot';
            $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
            array_push($syncImageIds, $imageId);
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
        #dd($lot->other_images->pluck('id')->toArray());
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

        if (isset($request->mainImage)) {
            $mianImageId = $lot->main_image->id;
            $lot->blImages()->detach($mianImageId);
            $this->imageService->deleteImage($mianImageId);
            $file = $request->mainImage;
            $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
            $alt = null;
            $imageable_id = $lotId;
            $imageable_type = 'App\Models\Lot';

            $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);

            $this->lotService->attachLotImages($lotId, [$imageId=>['main'=>true]]);
        }

        if (isset($request->images)) {
            $olderImageIds = $lot->other_images->pluck('id')->toArray();
            $lot->blImages()->detach($olderImageIds);
            foreach($olderImageIds as $imageId)
            {
                $this->imageService->deleteImage($imageId);
            }
            $newImageIds = array();
            foreach ($request->images as $file) {
                $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
                $alt = null;
                $imageable_id = $lotId;
                $imageable_type = 'App\Models\Lot';
                $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
                array_push($newImageIds, $imageId);
            }
            $this->lotService->attachLotImages($lotId, $newImageIds);
        }

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
        $lots = $this->indexLots(0);
        return CustomClass::viewWithTitle(view('account.applications.index')->with('lots', $lots), '審核中的申請');
    }


    public function indexSellingLots()
    {
        $lots = $this->indexLots(1);
        return CustomClass::viewWithTitle(view('account.selling_lots.index')->with('lots', $lots), '正在委賣的物品');
    }

    public function indexFinishedLots()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lots = $this->lotService->getFinishedLots($user);
        return CustomClass::viewWithTitle(view('account.finished_lots.index')->with('lots', $lots), '完成委賣的物品');
    }

    public function indexReturnedLots()
    {
        $user = $this->userService->getUser(Auth::user()->id);
        $lots = $this->lotService->getReturnedLots($user);
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

        if($request->bid > $lot->next_bid) {
            $rules['bid'] = 'required|gte:'.$this->bidService->getBidderLotAutoBid($request->bidderId, $lot);
            $messages['bid.gte'] =  '已設定更高的自動出價';
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

    public function testLotDelete($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $lot->delete();
    }

    public function testUserDelete($userId)
    {
        $user = $this->userService->getUser($userId);
        $user->delete();
    }
}
