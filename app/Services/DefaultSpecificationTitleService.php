<?php

namespace App\Services;

use App\Repositories\DefaultSpecificationTitleRepository;

class DefaultSpecificationTitleService extends DefaultSpecificationTitleRepository
{
    public function createDefaultSpecificationTitles($request, $categoryId)
    {
        DefaultSpecificationTitleRepository::deleteByCategoryId($categoryId);
        foreach($request->titles as $title)
        {
            DefaultSpecificationTitleRepository::create(['category_id'=>$categoryId, 'title'=>$title]);
        }
    }
}
