<?php

namespace App\Services;

use App\Repositories\ImageRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class ImageService extends ImageRepository
{
    public function storeImage($file, $folderName, $alt, $imageable_id, $imageable_type)
    {
        $fileName = md5($file->getClientOriginalName().strval(now()));
        $extension = $file->extension();
        $path = $file->storeAs(
            '/public/images'.$folderName, $fileName.'.'.$extension
        );

        $input['url'] = '/storage/images'.$folderName.'/'.$fileName.'.'.$extension;
        $input['path'] = $path;
        $input['alt'] = $alt;
        $input['imageable_id'] = $imageable_id;
        $input['imageable_type'] = $imageable_type;
        $newImage = ImageRepository::create($input);
        return $newImage->id;
    }

    public function getImageId($imageable_id, $imageable_type)
    {
        $imageId = ImageRepository::getId($imageable_id, $imageable_type);
        return $imageId;
    }

    public function getImagePath($imageId)
    {
        return ImageRepository::find($imageId)->path;
    }

    private function removeStorageImage($path)
    {
        Storage::delete($path);
    }

    public function updateImage($file, $folderName, $alt, $imageable_id, $imageable_type, $imageId)
    {
        if(isset($file))
        {
            $oldImagePath = $this->getImagePath($imageId);
            $this->removeStorageImage($oldImagePath);

            $fileName = md5($file->getClientOriginalName().strval(now()));
            $extension = $file->extension();
            $path = $file->storeAs(
                '/public/images'.$folderName, $fileName.'.'.$extension
            );

            $input['url'] = '/storage/images'.$folderName.'/'.$fileName.'.'.$extension;
            $input['path'] = $path;
            $input['alt'] = $alt;
            ImageRepository::fill($input, $imageId);
        }
    }

    public function handleStoreOrUpdateImage($request, $folderName, $alt, $imageable_id, $imageable_type)
    {
        if(null === $imageId = $this->getImageId($imageable_id, $imageable_type))
        {
            $this->storeImage($request, $folderName, $alt, $imageable_id, $imageable_type);
        } else {
            $this->updateImage($request, $folderName, $alt, $imageable_id, $imageable_type, $imageId);
        }
    }

    public function deleteImage($imageId)
    {
        $oldImagePath = $this->getImagePath($imageId);
        $this->removeStorageImage($oldImagePath);
        ImageRepository::find($imageId)->delete();
    }

    public function changeImagesOrder($newImagesOrder, $lot)
    {
        foreach ($lot->blImages as $image) {
            // 找到图片的新顺序
            $index = array_search($image->id, $newImagesOrder);

            // 检查 pivot 表中的 'main' 是否与新的顺序不一致
            if ($image->pivot->main != $index) {
                // 使用 updateExistingPivot 方法更新 pivot 表中的 'main' 字段
                $lot->blImages()->updateExistingPivot($image->id, ['main' => $index]);
            }
        }
    }
}
