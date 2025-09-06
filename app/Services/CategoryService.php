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

    public function getCategories($lot)
    {
        $categories = $lot->categories;
        return $categories;
    }

    public function canDeleteCategory($categoryId)
    {
        $category = $this->getCategory($categoryId);

        // 檢查是否有物品使用該分類
        $hasLots = $category->lots()->count() > 0;

        // 檢查是否有子分類
        $hasChildren = $category->children()->count() > 0;

        // 檢查是否有專家領域使用該分類
        $hasDomains = \App\Models\Domain::where('domain', $categoryId)->count() > 0;

        return [
            'can_delete' => !$hasLots && !$hasChildren && !$hasDomains,
            'has_lots' => $hasLots,
            'has_children' => $hasChildren,
            'has_domains' => $hasDomains,
            'lots_count' => $category->lots()->count(),
            'children_count' => $category->children()->count(),
            'domains_count' => \App\Models\Domain::where('domain', $categoryId)->count()
        ];
    }

    public function deleteCategory($categoryId)
    {
        $canDelete = $this->canDeleteCategory($categoryId);

        if (!$canDelete['can_delete']) {
            $reasons = [];
            if ($canDelete['has_lots']) {
                $reasons[] = "有 {$canDelete['lots_count']} 個物品使用此分類";
            }
            if ($canDelete['has_children']) {
                $reasons[] = "有 {$canDelete['children_count']} 個子分類";
            }
            if ($canDelete['has_domains']) {
                $reasons[] = "有 {$canDelete['domains_count']} 個專家領域使用此分類";
            }

            throw new \Exception('無法刪除分類：' . implode('，', $reasons));
        }

        $category = $this->getCategory($categoryId);

        // 刪除該分類的預設規格標題
        $category->defaultSpecificationTitles()->delete();

        return CategoryRepository::delete($categoryId);
    }
}
