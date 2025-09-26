<?php

namespace App\Http\Controllers;

use App\Services\BannerService;
use App\Services\LotService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\CustomFacades\CustomClass;
use App\Jobs\ExpireMergeShippingRequest;
use App\Services\UserService;
use App\Services\CategoryService;
use App\Services\DeliveryMethodService;
use App\Services\ImageService;
use App\Services\DomainService;
use App\Services\SpecificationService;
use App\Services\CartService;
use Symfony\Component\ErrorHandler\Debug;
use App\Models\MergeShippingRequest;
use App\Models\MergeShippingItem;
use App\Services\LineService;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuctioneerController extends Controller
{
    private $userService, $categoryService, $imageService, $domainService, $orderService, $bannerService, $lotService, $promotionService, $specificationService, $deliveryMethodService, $cartService, $lineService;

    public function __construct(UserService $userService, CategoryService $categoryService, ImageService $imageService, DomainService $domainService, OrderService $orderService, LotService $lotService, PromotionService $promotionService, BannerService $bannerService, SpecificationService $specificationService, DeliveryMethodService $deliveryMethodService, CartService $cartService, LineService $lineService)
    {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->imageService = $imageService;
        $this->domainService = $domainService;
        $this->orderService = $orderService;
        $this->lotService = $lotService;
        $this->promotionService = $promotionService;
        $this->bannerService = $bannerService;
        $this->specificationService = $specificationService;
        $this->deliveryMethodService = $deliveryMethodService;
        $this->cartService = $cartService;
        $this->lineService = $lineService;
    }

    static function showDashboard()
    {
        $customView = CustomClass::viewWithTitle(view('auctioneer.dashboard'), '管理員中心 - '.env("APP_NAME"));
        return $customView;
    }

    public function createExpert()
    {
        $mainCategories = $this->categoryService->getRoots();
        $customView = CustomClass::viewWithTitle(view('auctioneer.experts.create')->with('mainCategories', $mainCategories), '專家帳號創建 - '.env("APP_NAME"));
        return $customView;
    }

    static function indexExperts()
    {
        $customView = CustomClass::viewWithTitle(view('auctioneer.experts.index'), '專家管理 - '.env("APP_NAME"));
        return $customView;
    }

    public function storeExpert(Request $request)
    {
        $input = $request->all();
        $rules = [
            'email' => 'required|unique:users|max:255',
            'password' => ['required', 'string', new Auth\Password, 'confirmed'],
        ];
        $messages = [
            'email.unique'=>'電子郵件已被使用過',
            'password.confirmed'=>'密碼不一致',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = $this->userService->createUser($request, 1);
        $domains = $request->domains;
        $this->domainService->createDomains($user->id, $domains);

        return back()->with('notification', '創建成功');
    }

    public function editExpert($userId)
    {
        $expert = $this->userService->getUser($userId);
        $mainCategories = $this->categoryService->getRoots();
        $customView = CustomClass::viewWithTitle(view('auctioneer.experts.edit')->with('expert', $expert)->with('mainCategories', $mainCategories), '修改');
        return $customView;
    }

    public function updateUserWhoIsExpert(Request $request, $userId)
    {
        $domains = $request->domains;
        $user = $this->userService->getUser($userId);
        $this->domainService->auctioneerUpdateDomain($user, $domains);

        return back()->with('notification', '修改成功');
    }

    public function ajaxExperts()
    {
        $datatable = $this->userService->ajaxExperts();
        return $datatable;
    }

    static function createMainCategory()
    {
        $customView = CustomClass::viewWithTitle(view('auctioneer.main_categories.create'), '主分類創建 - '.env("APP_NAME"));
        return $customView;
    }

    public function storeMainCategory(Request $request)
    {
        $newCategoryId = $this->categoryService->createCategory($request);
        $file = $request->image;
        $folderName = '/category';
        $alt = $request->name;
        $imageable_id = $newCategoryId;
        $imageable_type = 'App\Models\Category';
        $this->imageService->handleStoreOrUpdateImage($file, $folderName, $alt, $imageable_id, $imageable_type);
        return back()->with('notification', '創建成功');
    }

    public function indexMainCategories()
    {
        $categoryRoots = $this->categoryService->getRoots();
        $customView = CustomClass::viewWithTitle(view('auctioneer.main_categories.index')->with('categoryRoots', $categoryRoots), '主分類管理');
        return $customView;
    }

    public function editMainCategory($categoryId)
    {
        $category = $this->categoryService->getCategory($categoryId);
        $customView = CustomClass::viewWithTitle(view('auctioneer.main_categories.edit')->with('category', $category), $category->name.'修改');
        return $customView;
    }

    public function updateMainCategory(Request $request, $categoryId)
    {
        $this->categoryService->updateCategory($request, $categoryId);
        $file = $request->image;
        $alt = $request->name;
        $imageable_id = $categoryId;
        $imageable_type = 'App\Models\Category';
        $folderName= '/category';
        $this->imageService->handleStoreOrUpdateImage($file, $folderName, $alt, $imageable_id, $imageable_type);
        return back()->with('notification', '修改成功');
    }

    public function deleteMainCategory($categoryId)
    {
        try {
            $this->categoryService->deleteCategory($categoryId);
            return back()->with('notification', '主分類刪除成功');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function indexOrders()
    {
        $customView = CustomClass::viewWithTitle(view('auctioneer.orders.index'), '訂單管理');
        return $customView;
    }

    public function ajaxGetOrders()
    {
        return $this->orderService->ajaxGetOrders();
    }

    public function showOrder($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $logisticInfo = $this->orderService->getLogisticInfo($order, 0);
        $with = ['order'=>$order, 'logisticInfo'=>$logisticInfo];
        $customView = CustomClass::viewWithTitle(view('auctioneer.orders.show')->with($with), '訂單#'.$orderId);
        return $customView;
    }

    public function noticeShipping(Request $request, $orderId)
    {

        $this->orderService->noticeShipping($orderId);
        $this->orderService->storeShippingLogistic($request, $orderId);
        #return back()->with('notification', '通知成功');
    }

    public function noticeArrival($orderId)
    {
        $this->orderService->noticeArrival($orderId);
        return back()->with('notification', '通知成功');
    }

    public function noticeOwnerRemit($orderId, $lotId)
    {
        $this->orderService->noticeOwnerRemit($orderId, $lotId);
    }


    public function noticeConfirmAtmPay($orderId)
    {
        $this->orderService->noticeConfirmAtmPay($orderId);
        return back()->with('notification', '通知成功');
    }

    public function confirmPaid($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        switch (true)
        {
            // 面交
            case ($order->delivery_method == 0):
                $status = 12;
                break;
            case ($order->delivery_method == 1 || $order->delivery_method == 2):
                $status = 13;
                break;
        }
        $transactionRecord = $order->orderRecords->last()->transactionRecord;

        switch (true)
        {
            case ($order->payment_method == 0):
                $input = [
                    'payment_method' => $transactionRecord->payment_method,
                    'system_order_id' => $transactionRecord->system_order_id,
                    'av_code' => $transactionRecord->av_code,
                    'amount' => $transactionRecord->amount
                ];
                break;
            case ($order->payment_method == 1):
                $input = [
                    'payment_method' => $transactionRecord->payment_method,
                    'amount'=>$transactionRecord->amount,
                    'remitter_id'=>$transactionRecord->remitter_id,
                    'remitter_account'=>$transactionRecord->remitter_account,
                    'payee_id'=>$transactionRecord->payee_id
                ];
                break;
        }


        $this->orderService->updateOrderStatusWithTransaction($input, $status, $orderId);
    }

    public function confirmRefillTransferInfo($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $status = 10;
        $this->orderService->updateOrderStatus($status, $order);
    }

    public function setWithdrawalBid($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $this->orderService->updateOrderStatus(51, $order); // 失效 - 付款逾期
        $firstItem = $order->orderItems->first();
        if ($firstItem) {
            $this->lotService->updateLotStatus(25, $firstItem->lot); // 棄標
        }
    }

    public function setLotWithdrawn(Request $request, $orderId)
    {
        try {
            $order = $this->orderService->getOrder($orderId);
            $lotId = $request->input('lot_id');

            // 檢查訂單狀態是否為爭議退款
            if ($order->status != 60) {
                return response()->json(['success' => false, 'message' => '只有爭議退款的訂單才能執行此操作']);
            }

            // 找到對應的 order item
            $orderItem = $order->orderItems->where('lot_id', $lotId)->first();
            if (!$orderItem) {
                return response()->json(['success' => false, 'message' => '找不到指定的商品']);
            }

            // 檢查是否為競標商品
            if ($orderItem->lot->type != 0) {
                return response()->json(['success' => false, 'message' => '只有競標商品才能設為棄標']);
            }

            // 將商品設為棄標
            $this->lotService->updateLotStatus(26, $orderItem->lot); // 26 是棄標狀態

            CustomClass::sendTemplateNotice($orderItem->lot->owner_id, 2, 3, $orderItem->lot->id, 1);

            // 清空 winner_id 和 current_bid
            $orderItem->lot->update([
                'winner_id' => null,
                'current_bid' => 0
            ]);

            return response()->json(['success' => true, 'message' => '商品已設為棄標']);

        } catch (\Exception $e) {
            dd($e);
            return response()->json(['success' => false, 'message' => '操作失敗：' . $e->getMessage()]);
        }
    }

    public function indexMessages($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $lot = $order->orderItems->first() ? $order->orderItems->first()->lot : null;
        $messages = $order->messages()->with('user')->orderBy('created_at', 'asc')->get();
        $this->orderService->messagesHaveRead($messages);
        $with = ['order'=>$order, 'lot'=>$lot, 'messages'=>$messages];
        $customView = CustomClass::viewWithTitle(view('auctioneer.orders.chatroom')->with($with), $lot ? $lot->name : '聊天室');
        return $customView;
    }

    public function indexMemberMessages($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        $lot = $order->orderItems->first() ? $order->orderItems->first()->lot : null;
        $messages = $order->messages()->with('user')->orderBy('created_at', 'asc')->get();
        $with = ['order'=>$order, 'lot'=>$lot, 'messages'=>$messages];
        $customView = CustomClass::viewWithTitle(view('auctioneer.orders.member_chatroom')->with($with), $lot ? $lot->name : '聊天室');
        return $customView;
    }

    public function indexPromotions()
    {
        $promotions = $this->promotionService->getPromotion();
        return CustomClass::viewWithTitle(view('auctioneer.promotions.index')->with('promotions', $promotions), '優惠管理');
    }

    public function updatePromotion(Request $request)
    {
        $input = $request->all();
        $rules = [
            'commission_rate' => 'required',
            'premium_rate' => 'required'
        ];
        $messages = [
            'commission_rate.required'=>'賣家佣金抽成為必填',
            'premium_rate.required'=>'買家額外費用為必填'
        ];

        if($request->commission_rate > 1) {
            $rules['commission_rate'] = $rules['commission_rate'].'|integer';
            $messages['commission_rate.integer'] = '數值大於1的話必須為整數';
        }

        if($request->premium_rate > 1) {
            $rules['premium_rate'] = $rules['premium_rate'].'|integer';
            $messages['premium_rate.integer'] = '數值大於1的話必須為整數';
        }

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400); // 400 being the HTTP code for an invalid request.
        } else {
            $this->promotionService->updatePromotion($request);
            return Response::json(array(
                'success' => route('auctioneer.promotions.index'),
                'errors' => false
            ), 200);
        }
    }

    public function indexBanners()
    {
        $banners = $this->bannerService->getAllBanners()->sortBy('index');
        return CustomClass::viewWithTitle(view('auctioneer.banners.index')->with("banners", $banners), 'Banner管理');
    }

    public function createBanner(Request $request)
    {
        $newBannerId = $this->bannerService->createBanner($request);
        $file = $request->desktopBanner;
        $folderName = '/banners';
        $alt = $request->slogan;
        $imageable_id = $newBannerId;
        $imageable_type = 'App\Models\Banner';
        $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
        $syncImageId1 = array($imageId);

        $file = $request->mobileBanner;
        $folderName = '/banners';
        $alt = $request->slogan;
        $imageable_id = $newBannerId;
        $imageable_type = 'App\Models\Banner';
        $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
        $syncImageId2 = array($imageId => ['mobile'=>1]);

        $syncImageIds = $syncImageId1 + $syncImageId2;

        $this->bannerService->syncBannerImages($newBannerId, $syncImageIds);

        return back()->with('notification', '創建成功');
    }

    public function updateBannerIndexes(Request $request)
    {

        $this->bannerService->updateBannerIndexes($request);
        return back()->with('notification', '保存成功');
    }

    public function deleteBanner ($id)
    {
        $imageIds = $this->bannerService->getBanner($id)->images->pluck('id');
        $this->bannerService->detachImages($id);
        foreach($imageIds as $imageId) {
            $this->imageService->deleteImage($imageId);
        }
        $this->bannerService->deleteBanner($id);
    }

    public function indexMembers()
    {
        return CustomClass::viewWithTitle(view('auctioneer.members.index'), '會員管理');
    }

    public function ajaxMembers()
    {
        $datatable = $this->userService->ajaxMembers();
        return $datatable;
    }

    public function showMember($userId)
    {
        $user = $this->userService->getUser($userId);
        return CustomClass::viewWithTitle(view('auctioneer.members.show')->with('user', $user), '會員管理 - '.$user->name);
    }

    public function ajaxRoleUpgradeMember(Request $request)
    {
        $user = $this->userService->getUser($request->userId);
        $this->userService->roleUpdate($user, 2);
    }

    public function ajaxRoleDowngradeMember(Request $request)
    {
        $user = $this->userService->getUser($request->userId);
        $this->userService->roleUpdate($user, 3);
    }

    public function ajaxBlockMember(Request $request)
    {
        $user = $this->userService->getUser($request->userId);
        $this->userService->statusUpdate($user, 4);
    }

    public function ajaxUnblockMember(Request $request)
    {
        $user = $this->userService->getUser($request->userId);
        $this->userService->statusUpdate($user, 0);
    }

    public function showProducts()
    {
        $customView = CustomClass::viewWithTitle(view('auctioneer.products.index'), '商品管理');
        return $customView;
    }

    public function ajaxGetProducts()
    {
        $datatable = $this->lotService->ajaxGetProducts();
        return $datatable;
    }

    public function createProduct()
    {
        $mainCategories = $this->categoryService->getRoots();

        $customView = CustomClass::viewWithTitle(view('auctioneer.products.create')->with('mainCategories', $mainCategories), '商品創建');
        return $customView;
    }

    public function ajaxDefaultSpecificationTitles($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        $defaultSpecificationTitles = $mainCategory->defaultSpecificationTitles;
        return $defaultSpecificationTitles;
    }

    public function ajaxSubCategories($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        $subCategories = $mainCategory->children;
        return $subCategories;
    }

    protected function lotValidation($request, $imageValid, $lotId = null)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
            'mainCategoryId' => 'required',
            'specificationValues.*' => 'required',
            'description' => 'required',
            'custom_id' => [
            'nullable',
                Rule::unique('lots', 'custom_id')->ignore($lotId),
            ],
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
            'deliveryMethods.required' => '未選擇運送方式',
            'custom_id.unique' => '自訂編號已存在', // Add this line

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

        // Create a new lot with type 60, which represents a direct-sale
        // marketplace item; returns the newly created lot ID
        $lotId = $this->lotService->createLot($request, 60);

        $this->lotService->syncCategoryLot($lotId, [$request->mainCategoryId=>['main'=>1], $request->sub_category_id]);

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


        return Response::json(array(
            'success' => route('auctioneer.products.index'),
            'errors' => false
        ), 200);
    }

    public function editProduct($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $mainCategories = $this->categoryService->getRoots();
        $lotMainCategoryId = $lot->main_category->id;
        $subCategories = $this->categoryService->getCategory($lotMainCategoryId)->children;
        $customView = CustomClass::viewWithTitle(view('auctioneer.products.edit')->with('lot', $lot)->with('mainCategories', $mainCategories)->with('subCategories', $subCategories), '商品修改');
        return $customView;
    }

    public function updateProduct(Request $request, $lotId)
    {
        $validator = $this->lotValidation($request, 1, $lotId);

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
            $lot->specifications()->delete();#清除舊的規格
            $this->specificationService->createSpecifications($request, $lotId);#創建新的規格
        }

        $this->lotService->syncCategoryLot($lotId, [$request->mainCategoryId=>['main'=>1], $request->subCategoryId]);#同步分類



        $this->lotService->updateProduct($request, $lotId);
        $this->deliveryMethodService->syncDeliveryMethods($request, $lot);#同步分類

        return Response::json(array(
            'success' => route('auctioneer.products.edit', $lotId),
            'errors' => false
        ), 200);
    }

    public function publishProduct($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $this->lotService->updateLotStatus(61, $lot); // 上架
        return back()->with('notification', '上架成功');
    }

    public function unpublishProduct($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $this->lotService->updateLotStatus(60, $lot); // 下架
        return back()->with('notification', '下架成功');
    }

    // 合併運費請求管理
    public function indexMergeShippingRequests()
    {
        $requests = MergeShippingRequest::with(['user', 'items.lot.blImages', 'logisticRecords'])
            ->where('status', '!=', MergeShippingRequest::STATUS_REMOVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return CustomClass::viewWithTitle(
            view('auctioneer.merge_shipping_requests.index')->with('requests', $requests),
            '合併運費請求管理'
        );
    }

    public function showMergeShippingRequest($requestId)
    {
        $request = MergeShippingRequest::with(['user', 'items.lot.blImages', 'logisticRecords'])
            ->findOrFail($requestId);

        return CustomClass::viewWithTitle(
            view('auctioneer.merge_shipping_requests.show')->with('request', $request),
            '合併運費請求詳情'
        );
    }

    public function updateMergeShippingRequest(Request $request, $requestId)
    {
        $mergeRequest = MergeShippingRequest::findOrFail($requestId);

        $input = $request->all();
        $status = $request->input('status');

        // 根據狀態設定不同的驗證規則
        if ($status == 1) {
            // 同意合併運費時，新運費為必填
            $rules = [
                'new_shipping_fee' => 'required|numeric|min:0',
                'status' => 'required|in:1,2', // 1: 已處理, 2: 已拒絕
                'remark' => 'nullable|string|max:500',
            ];

            $messages = [
                'new_shipping_fee.required' => '新運費為必填',
                'new_shipping_fee.numeric' => '新運費必須為數字',
                'new_shipping_fee.min' => '新運費不能為負數',
                'status.required' => '狀態為必填',
                'status.in' => '狀態值無效',
                'remark.max' => '備註不能超過500字',
            ];
        } else {
            // 拒絕合併運費時，新運費為可選
            $rules = [
                'new_shipping_fee' => 'nullable|numeric|min:0',
                'status' => 'required|in:1,2', // 1: 已處理, 2: 已拒絕
                'remark' => 'nullable|string|max:500',
            ];

            $messages = [
                'new_shipping_fee.numeric' => '新運費必須為數字',
                'new_shipping_fee.min' => '新運費不能為負數',
                'status.required' => '狀態為必填',
                'status.in' => '狀態值無效',
                'remark.max' => '備註不能超過500字',
            ];
        }

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }

        // 準備更新資料
        $updateData = [
            'status' => $request->status,
            'remark' => $request->remark,
        ];

        // 只有在有提供新運費時才更新
        if ($request->has('new_shipping_fee') && $request->new_shipping_fee !== null && $request->new_shipping_fee !== '') {
            $updateData['new_shipping_fee'] = $request->new_shipping_fee;
        }

        $mergeRequest->update($updateData);

        // 根據處理結果執行相應操作
        if ($request->status == 1) {
            // 核准合併運費請求 - 設置1天後過期的 Job

            if(config('app.env') == 'production') {
                ExpireMergeShippingRequest::dispatch($mergeRequest->id)->delay(now()->addDay());
            } else {
                ExpireMergeShippingRequest::dispatch($mergeRequest->id)->delay(now()->addSeconds(60));
            }

            CustomClass::sendTemplateNotice($mergeRequest->user_id, 8, 1, $mergeRequest->id, 1); // 通知合併運費已通過
        } elseif ($request->status == 2) {
            // 拒絕合併運費請求 - 還原庫存並將物品加回購物車
            $userId = $mergeRequest->user_id;

            foreach ($mergeRequest->items as $item) {
                $lot = $item->lot;
                if ($lot) {
                    // 還原庫存
                    $lot->increment('inventory', $item->quantity);

                    // 如果庫存從0變為有庫存，且商品狀態是下架狀態，則重新上架
                    if ($lot->fresh()->inventory > 0 && $lot->status == 60) { // 60 是下架狀態
                        $lot->update(['status' => 61]); // 61 是正常狀態
                    }
                }

                // 將物品加回購物車
                $this->cartService->addToCart($userId, $item->lot_id, $item->quantity);
            }
            CustomClass::sendTemplateNotice($mergeRequest->user_id, 8, 2, $mergeRequest->id, 1); // 通知合併運費已拒絕
        }

        return Response::json(array(
            'success' => true,
            'message' => $request->status == 2 ? '合併運費請求已拒絕，庫存已還原，物品已加回購物車' : '合併運費請求已更新'
        ), 200);
    }

        public function confirmRefund(Request $request, $orderId)
    {
        // 準備備注內容
        $remark = '退款金額：NT$' . number_format($request->refund_amount);
        if ($request->refund_method == 'line_pay') {
            $remark .= '，退款方式：LINE Pay';
        } else {
            $remark .= '，退款方式：銀行轉帳';
        }

        // 如果有自定義備注，加入其中
        if ($request->filled('refund_remark')) {
            $remark .= '，備注：' . $request->refund_remark;
        }

        if($request->refund_method == 'line_pay') {
            $order = $this->orderService->getOrder($orderId);
            $this->orderService->updateOrderStatus(61, $order, $remark);
            $this->lineService->refund($request, $order);
            $order->save();
        } else {
            $order = $this->orderService->getOrder($orderId);
            $this->orderService->updateOrderStatus(61, $order, $remark);
            $order->save();
        }
    }

    // 文章管理功能
    public function indexArticles()
    {
        $articles = Article::with('auctioneer')->orderBy('created_at', 'desc')->get();
        return CustomClass::viewWithTitle(view('auctioneer.articles.index')->with('articles', $articles), '文章管理');
    }

    public function createArticle()
    {
        return CustomClass::viewWithTitle(view('auctioneer.articles.create'), '創建文章');
    }

    public function storeArticle(Request $request)
    {
        $input = $request->all();
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'required|string',
            'content' => 'required|string',
        ];
        $messages = [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過255字',
            'subtitle.max' => '副標題不能超過255字',
            'intro.required' => '簡介為必填',
            'content.required' => '內容為必填',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Article::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $request->intro,
            'content' => $request->content,
            'auctioneer_id' => Auth::id(),
        ]);

        return back()->with('notification', '文章創建成功');
    }

    public function editArticle($articleId)
    {
        $article = Article::findOrFail($articleId);
        return CustomClass::viewWithTitle(view('auctioneer.articles.edit')->with('article', $article), '編輯文章');
    }

    public function updateArticle(Request $request, $articleId)
    {
        $article = Article::findOrFail($articleId);
        
        $input = $request->all();
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro' => 'required|string',
            'content' => 'required|string',
        ];
        $messages = [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過255字',
            'subtitle.max' => '副標題不能超過255字',
            'intro.required' => '簡介為必填',
            'content.required' => '內容為必填',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $article->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'intro' => $request->intro,
            'content' => $request->content,
        ]);

        return back()->with('notification', '文章更新成功');
    }

    public function deleteArticle($articleId)
    {
        $article = Article::findOrFail($articleId);
        $article->delete();
        
        return back()->with('notification', '文章刪除成功');
    }
}
