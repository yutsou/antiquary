<?php

namespace App\Services;

use App\Repositories\BannerRepository;

class BannerService extends BannerRepository
{
    public function createBanner($request)
    {
        $input = $request->all();
        $input['visible'] = 1;
        $input['index'] = BannerRepository::all()->count();
        $newBanner = BannerRepository::create($input);
        return $newBanner->id;
    }

    public function getAllBanners()
    {
        $banners = BannerRepository::all();
        return $banners;
    }

    public function updateBannerIndexes($request)
    {
        $banners = $this->getAllBanners();
        $ids = $request->ids;

        foreach ($ids as $key => $id) {
            $banner = $banners->find($id);
            $banner->update(['index'=> $key]);
        }
    }

    public function getBanner($id)
    {
        $banner = BannerRepository::find($id);
        return $banner;
    }

    public function deleteBanner($id)
    {
        BannerRepository::delete($id);
        $banners = $this->getAllBanners()->sortBy('index');

        foreach ($banners as $key => $banner) {
            $banner->update(['index'=> $key]);
        }
    }
}
