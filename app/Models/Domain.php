<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Domain
 *
 * @property int $id
 * @property int $user_id
 * @property int $domain
 * @property string|null $brief
 * @property string|null $introduction
 * @property string|null $year
 * @property string|null $experience
 * @property string|null $scale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read mixed $auction_management_count
 * @property-read mixed $lot_management_count
 * @property-read \App\Models\Image|null $image
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain query()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereBrief($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereYear($value)
 * @mixin \Eloquent
 */
class Domain extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'domain');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function lots()
    {
        return $this->category->lots();
    }

    public function getLotManagementCountAttribute()
    {

        return  $this->lots->whereIn('status', [0, 3, 30, 35, 33])->count();
    }

    public function getAuctionManagementCountAttribute()
    {
        return  $this->lots->whereIn('status', [10, 11, 12, 13])->count();
    }

    protected $fillable = [
        'user_id', 'domain', 'brief', 'introduction', 'experience', 'year', 'scale'
    ];
}
