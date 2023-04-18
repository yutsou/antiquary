<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageAble');
    }

    public function blImages()
    {
        return $this->belongsToMany(Image::class, 'banner_image', 'banner_id', 'image_id');
    }

    public function getMobileBannerAttribute()
    {
        return  $this->blImages()->wherePivot('mobile', 1)->first();
    }

    public function getDesktopBannerAttribute()
    {
        return  $this->blImages()->wherePivot('mobile', null)->first();
    }
}
