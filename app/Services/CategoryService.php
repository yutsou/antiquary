<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService extends CategoryRepository
{
    public function createCategory($request)
    {
        $input = $request->all();
        $newCategory = CategoryRepository::create($input);
        return $newCategory->id;
    }

    public function getRoots()
    {
        return CategoryRepository::getRoots();
    }

    public function getCategory($categoryId)
    {
        return CategoryRepository::find($categoryId);
    }

    public function updateCategory($request, $categoryId)
    {
        $input = $request->all();
        CategoryRepository::fill($input, $categoryId);
    }

    public function getParentColorHex($categoryId)
    {
        return CategoryRepository::find($categoryId)->color_hex;
    }

    public function getRootId()
    {

    }

    public function getChildId()
    {

    }
}
