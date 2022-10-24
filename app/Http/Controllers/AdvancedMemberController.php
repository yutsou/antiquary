<?php

namespace App\Http\Controllers;

use App\CustomFacades\CustomClass;
use App\Services\DefaultSpecificationTitleService;
use App\Services\CategoryService;
use App\Services\ImageService;
use App\Services\LotService;
use App\Services\DeliveryMethodService;
use App\Services\SpecificationService;
use Illuminate\Http\Request;

class AdvancedMemberController extends Controller
{
    private $defaultSpecificationTitleService, $categoryService, $lotService, $specificationService, $shippingMethodService, $imageService;

    public function __construct(
        DefaultSpecificationTitleService $defaultSpecificationTitleService,
        CategoryService                  $categoryService,
        LotService                       $lotService,
        SpecificationService             $specificationService,
        DeliveryMethodService            $shippingMethodService,
        ImageService                     $imageService
    ) {
        $this->defaultSpecificationTitleService = $defaultSpecificationTitleService;
        $this->categoryService = $categoryService;
        $this->lotService = $lotService;
        $this->specificationService = $specificationService;
        $this->shippingMethodService = $shippingMethodService;
        $this->imageService = $imageService;
    }

    public function showDashboard()
    {
        $customView = CustomClass::viewWithTitle(view('advanced_account.dashboard'), '會員中心');
        return $customView;
    }

    public function createLot()
    {
        $mainCategories = $this->categoryService->getRoots();
        $customView = CustomClass::viewWithTitle(view('advanced_account.create_lot')->with('mainCategories', $mainCategories), '建立物品');
        return $customView;
    }

    public function storeLot(Request $request)
    {
        $lotId = $this->lotService->createLot($request);

        $this->lotService->syncCategoryLot($request, $lotId);

        $this->specificationService->createSpecifications($request, $lotId);

        $this->shippingMethodService->createShippingMethods($request, $lotId);

        $file = $request->mainImage;
        $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
        $alt = null;
        $imageable_id = $lotId;
        $imageable_type = 'App\Models\Lot';

        $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);

        $this->imageService->syncLotImage($imageId, $lotId);

        foreach ($request->images as $file) {
            $folderName = '/lots'.'/'.$request->mainCategoryId.'/'.strlen($lotId).'/'.$lotId;
            $alt = null;
            $imageable_id = $lotId;
            $imageable_type = 'App\Models\Lot';
            $imageId = $this->imageService->storeImage($file, $folderName, $alt, $imageable_id, $imageable_type);
            $this->imageService->syncLotImage($imageId, $lotId);
        }

        return redirect('/advanced-account/dashboard/lots/'.$lotId);
    }

    public function checkLot($lotId)
    {
        $lot = $this->lotService->getLot($lotId);
        $customView = CustomClass::viewWithTitle(view('advanced_account.check_lot')->with('lot', $lot), '查看物品');
        return $customView;
    }

    public function ajaxSubCategories($mainCategoryId)
    {
        $mainCategory = $this->categoryService->getCategory($mainCategoryId);
        $subCategories = $mainCategory->defaultSpecificationTitles;
        return $subCategories;
    }
}
