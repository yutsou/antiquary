<?php

namespace App\Http\Controllers;

use App\Services\AuctionService;
use Illuminate\Http\Request;
use App\CustomFacades\CustomClass;

use App\Services\UserService;
use App\Services\CategoryService;

use App\Services\DomainService;
use App\Services\DefaultSpecificationTitleService;
use App\Services\LotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{
    private $userService, $categoryService, $domainService, $defaultSpecificationTitleService, $lotService, $auctionService;

    public function __construct(
        UserService $userService,
        CategoryService $categoryService,
        DomainService $domainService,
        DefaultSpecificationTitleService $defaultSpecificationTitleService,
        LotService $lotService,
        AuctionService $auctionService
    ) {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->domainService = $domainService;
        $this->defaultSpecificationTitleService = $defaultSpecificationTitleService;
        $this->lotService = $lotService;
        $this->auctionService = $auctionService;
    }

    public function showDashboard()
    {
        $userId = Auth::user()->id;
        $user = $this->userService->getUser($userId);
        $domains = $this->domainService->expertGetDomains($user);
        $customView = CustomClass::viewWithTitle(view('expert.dashboard')->with('domains', $domains), '主分類管理');
        return $customView;
    }

    /*public function editDomain($domainId)
    {
        $domain = $this->domainService->getDomain($domainId);
        $customView = CustomClass::viewWithTitle(view('expert.edit_domain')->with('domain', $domain), '專家介紹');
        return $customView;
    }

    public function updateDomain(Request $request, $expertId)
    {
        $this->domainService->expertUpdateDomain($request, $expertId);
        $file = $request->image;
        $folderName = '/expert';
        $alt = Auth::user()->name;
        $imageable_id = $expertId;
        $imageable_type = 'App\Models\Domain';
        $this->imageService->handleStoreOrUpdateImage($file, $folderName, $alt, $imageable_id, $imageable_type);
        return back()->with('notification', '修改成功');
    }*/

    public function indexSubCategory($mainCategoryId)
    {
        $user = Auth::user();
        $subCategories = $user->domains->pluck('category')->where('id', $mainCategoryId)->pluck('children')->flatten();

        $customView = CustomClass::viewWithTitle(view('expert.sub_categories.index')->with('mainCategoryId', $mainCategoryId)->with('subCategories', $subCategories), '子分類管理');
        return $customView;
    }

    public function createSubCategory($mainCategoryId)
    {
        $customView = CustomClass::viewWithTitle(view('expert.sub_categories.create')->with('mainCategoryId', $mainCategoryId), '子分類建立');
        return $customView;
    }

    public function storeSubCategory(Request $request, $mainCategoryId)
    {
        $request->merge(["parent_id"=>$mainCategoryId]);
        $request->merge(["color_hex"=>$this->categoryService->getParentColorHex($mainCategoryId)]);
        $categoryId = $this->categoryService->createCategory($request);

        /*$file = $request->image;
        $folderName = '/category';
        $alt = Auth::user()->name;
        $imageable_id = $categoryId;
        $imageable_type = 'App\Models\Category';
        $this->imageService->handleStoreOrUpdateImage($file, $folderName, $alt, $imageable_id, $imageable_type);*/

        return back()->with('notification', '建立成功');
    }

    public function editSubCategory($mainCategoryId, $subCategoryId)
    {
        $subcategory = $this->categoryService->getCategory($subCategoryId);
        $customView = CustomClass::viewWithTitle(view('expert.sub_categories.edit')->with('mainCategoryId', $mainCategoryId)->with('subCategory', $subcategory), '子分類修改');
        return $customView;
    }

    public function updateSubCategory(Request $request, $mainCategoryId, $subCategoryId)
    {
        $this->categoryService->updateCategory($request, $subCategoryId);
        return back()->with('notification', '修改成功');
    }

    public function manageDefaultSpecificationTitles($mainCategoryId)
    {
        $defaultSpecificationTitles = $this->categoryService->getCategory($mainCategoryId)->defaultSpecificationTitles;
        return CustomClass::viewWithTitle(view('expert.default_specifications.manage')->with('mainCategoryId', $mainCategoryId)->with('defaultSpecificationTitles', $defaultSpecificationTitles), '預設規格建立');
    }

    public function storeDefaultSpecificationTitles(Request $request, $mainCategoryId)
    {
        $this->defaultSpecificationTitleService->createDefaultSpecificationTitles($request, $mainCategoryId);
        return back()->with('notification', '建立成功');
    }

    public function indexLots($mainCategoryId)
    {
        return CustomClass::viewWithTitle(view('expert.lots.index')->with('mainCategoryId', $mainCategoryId), '物品管理');
    }

    public function ajaxReviewGetLots($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        return $this->lotService->ajaxReviewGetLots($mainCategory);
    }

    public function reviewLot($mainCategoryId, $lotId)
    {
        #需要一個判斷專家能審核這個物品
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        $subCategories = $mainCategory->children;
        $lot = $this->lotService->getLot($lotId);
        $owner = $lot->owner;
        $with = [
            'mainCategory' => $mainCategory,
            'subCategories' => $subCategories,
            'lot' => $lot,
            'owner' => $owner
        ];

        return CustomClass::viewWithTitle(view('expert.lots.review')->with($with), '審核');
    }

    public function handleLot(Request $request, $mainCategoryId, $lotId)
    {
        if ($request->action == 'requestRevision') {
            $input = $request->all();

            $rules = [
                "suggestion" => 'required',
            ];

            $messages = [
                'suggestion.required'=>'未填寫修改建議',
            ];
            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $this->lotService->updateLotName($lotId, $request);

            $lot = $this->lotService->updateApplication($lotId, $request);

            CustomClass::sendTemplateNotice($lot->owner_id, 1, 3, $lot->id, 1);
        } else if ($request->action == 'acceptApplication') {
            $input = $request->all();

            $rules = [
                "name" => 'required',
                "subCategoryId" => 'required'
            ];

            $messages = [
                'name.required'=>'未填寫商品名稱',
                "subCategoryId.required" => '為選擇商品分類'
            ];
            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $this->lotService->updateLotName($lotId, $request);
            $this->lotService->updateApplication($lotId, $request);
            $noticeData = $this->lotService->grantLot($lotId);

            CustomClass::sendTemplateNotice($noticeData[0]->owner_id, 1, $noticeData[1], $noticeData[0]->id, 1);

        }
    }

    public function showAuctions($mainCategoryId)
    {
        return CustomClass::viewWithTitle(view('expert.auctions.index')->with('mainCategoryId', $mainCategoryId), '拍賣會管理');
    }

    public function createAuction($mainCategoryId)
    {
        return CustomClass::viewWithTitle(view('expert.auctions.create')->with('mainCategoryId', $mainCategoryId), '建立拍賣會');
    }

    public function ajaxCreateAuctionGetLots($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        return $this->lotService->ajaxCreateAuctionGetLots($mainCategory);
    }

    public function storeAuction(Request $request)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
            'auction_start_at' => 'date|before:auction_end_at|after:now|required',
            'auction_end_at'=>'date|after:auction_start_at|required',
        ];

        if(isset($input['lots']) === false) {
            $rules['lots'] = 'required';
        }

        $messages = [
            'name.required'=>'未填寫拍賣會名稱',
            'auction_start_at.before'=>'開始時間需要在結束時間之前',
            'auction_start_at.after'=>'開始時間需要超過現在現在時間',
            'auction_start_at.required'=>'未填寫開始時間',
            'auction_end_at.after'=>'結束時間需要超過開始時間',
            'auction_end_at.required'=>'未填寫結束時間',
            'lots.required'=>'需選擇至少一個物品'
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }


        $auction = $this->auctionService->createAuction($request);
        $this->lotService->setLotAuction($request->lots, $auction, $request->auction_start_at, $request->auction_end_at);
        return response('success', 200);
    }

    public function receiveLot(Request $request, $mainCategoryId, $lotId)
    {
        $lot = $this->lotService->receiveLot($lotId);
        CustomClass::sendTemplateNotice($lot->owner_id, 1, 4, $lotId);
        return back();
    }

    public function ajaxGetAuctions($expertId)
    {
        return $this->lotService->ajaxExpertGetAuctions(Auth::user());
    }

    public function takeDownLot(Request $request)
    {
        $this->lotService->takeDownLot($request->lotId);
        return redirect()->back();
    }

    public function createUnsoldLotLogisticInfo($mainCategoryId, $lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        if( $lot->status == 30) {
            $type = 2;
        } else { #35
            $type = 3;
        }
        $logisticInfo = $this->lotService->getLogisticInfo($lot, $type);
        $with = [
            'mainCategoryId'=>$mainCategoryId,
            'lot'=>$lot,
            'logisticInfo'=>$logisticInfo
        ];
        return CustomClass::viewWithTitle(view('expert.unsold_lot_logistic_infos.create')->with($with), '查看 / 填寫運送資訊');
    }

    public function storeUnsoldLotLogisticInfo(Request $request, $mainCategoryId, $lotId)
    {
        $input = $request->all();

        $rules = [
            "company_name" => 'required',
            "tracking_code" => 'required',
        ];

        $messages = [
            'company_name.required'=>'未填寫物流公司名稱',
            'tracking_code.required'=>'未填寫物流追蹤碼',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $lot = $this->lotService->getLot($lotId);
        if( $lot->status == 30) {
            $type = 2;
        } else { #35
            $type = 3;
        }
        $this->lotService->returnLot($request, $lotId, $type);


        CustomClass::sendTemplateNotice($lot->owner_id, 5, 0, $lot->id, 0);

    }

    public function editReturnedLogisticInfo($mainCategoryId, $lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $logisticInfo = $this->lotService->getLogisticInfo($lot, 1);
        $with = [
            'mainCategoryId'=>$mainCategoryId,
            'lot'=>$lot,
            'logisticInfo'=>$logisticInfo
        ];
        return CustomClass::viewWithTitle(view('expert.returned_lot_logistic_infos.edit')->with($with), '查看 / 填寫運送資訊');
    }

    public function updateReturnedLogisticInfo(Request $request, $mainCategoryId, $lotId)
    {
        $input = $request->all();

        $rules = [
            "company_name" => 'required',
            "tracking_code" => 'required',
        ];

        $messages = [
            'company_name.required'=>'未填寫物流公司名稱',
            'tracking_code.required'=>'未填寫物流追蹤碼',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $this->lotService->returnLot($request, $lotId, 1);

        $lot = $this->lotService->getLot($lotId);
        CustomClass::sendTemplateNotice($lot->owner_id, 4, 0, $lot->id, 0);
    }
}
