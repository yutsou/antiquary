<?php

namespace App\Http\Controllers;

use App\Services\LotService;
use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\CustomFacades\CustomClass;
use App\Services\UserService;
use App\Services\CategoryService;
use App\Services\ImageService;
use App\Services\DomainService;

class AuctioneerController extends Controller
{
    private $userService, $categoryService, $imageService, $domainService, $orderService, $lotService;

    public function __construct(UserService $userService, CategoryService $categoryService, ImageService $imageService, DomainService $domainService, OrderService $orderService, LotService $lotService, PromotionService $promotionService)
    {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->imageService = $imageService;
        $this->domainService = $domainService;
        $this->orderService = $orderService;
        $this->lotService = $lotService;
        $this->promotionService = $promotionService;
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
        $lot = $order->lot;
        $logisticInfo = $this->orderService->getLogisticInfo($order, 0);
        $with = ['order'=>$order, 'lot'=>$lot, 'logisticInfo'=>$logisticInfo];
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

    public function noticeRemit($orderId)
    {
        $this->orderService->noticeRemit(null, $orderId, 1);
        #return back()->with('notification', '通知成功');
    }

    public function noticeConfirmAtmPay($orderId)
    {
        $this->orderService->noticeConfirmAtmPay($orderId);
        return back()->with('notification', '通知成功');
    }

    public function indexMessages($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        return CustomClass::viewWithTitle(view('auctioneer.orders.chatroom')->with('order', $order), $order->lot->name);
    }

    public function indexMemberMessages($orderId)
    {
        $order = $this->orderService->getOrder($orderId);
        return CustomClass::viewWithTitle(view('auctioneer.orders.member_chatroom')->with('order', $order), $order->lot->name);
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
}
