<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Banner
 *
 * @property int $id
 * @property int $index
 * @property string|null $slogan
 * @property string|null $link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $blImages
 * @property-read int|null $bl_images_count
 * @property-read mixed $desktop_banner
 * @property-read mixed $mobile_banner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereSlogan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
