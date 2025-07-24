<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * App\Models\Lot
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $estimated_price
 * @property string|null $starting_price
 * @property string|null $reserve_price
 * @property string $current_bid
 * @property int $owner_id
 * @property int|null $winner_id
 * @property int|null $auction_id
 * @property \Illuminate\Support\Carbon|null $auction_start_at
 * @property \Illuminate\Support\Carbon|null $auction_end_at
 * @property int $rating
 * @property int $status
 * @property int $entrust
 * @property string|null $suggestion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Auction|null $auction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AutoBid> $autoBids
 * @property-read int|null $auto_bids_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BidRecord> $bidRecords
 * @property-read int|null $bid_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $blImages
 * @property-read int|null $bl_images_count
 * @property-read \Kalnoy\Nestedset\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeliveryMethod> $deliveryMethods
 * @property-read int|null $delivery_methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogisticRecord> $deliveryRecords
 * @property-read int|null $delivery_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read mixed $auction_start_at_format
 * @property-read mixed $cross_border_delivery
 * @property-read mixed $face_to_face
 * @property-read mixed $home_delivery
 * @property-read mixed $main_category
 * @property-read mixed $main_image
 * @property-read mixed $next_bid
 * @property-read mixed $other_images
 * @property-read mixed $top_bidder_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogisticRecord> $logisticRecords
 * @property-read int|null $logistic_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specification> $specifications
 * @property-read int|null $specifications_count
 * @property-read \App\Models\User|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder|Lot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lot query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereAuctionEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereAuctionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereAuctionStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereCurrentBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereEntrust($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereEstimatedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereReservePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereStartingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereSuggestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lot whereWinnerId($value)
 * @mixin \Eloquent
 */
class Lot extends Model
{
    use HasFactory;
    use Searchable;

    protected $guarded = ['id'];
    protected $dates = ['auction_start_at', 'auction_end_at',];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function autoBids()
    {
        return $this->hasMany(AutoBid::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageAble');
    }

    public function blImages()
    {
        return $this->belongsToMany(Image::class, 'lot_image', 'lot_id', 'image_id') ->withPivot('main')->orderBy('lot_image.main');
    }

    public function deliveryMethods()
    {
        return $this->hasMany(DeliveryMethod::class);
    }

    public function deliveryRecords()
    {
        return $this->hasMany(LogisticRecord::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function bidRecords()
    {
        return $this->hasMany(BidRecord::class)->orderBy('id', 'desc');
    }

    public function logisticRecords()
    {
        return $this->morphMany(LogisticRecord::class, 'logistic_recordable');
    }

    public function deleteSpecifications()
    {
        $this->specifications()->delete();
        // return parent::delete();
    }

    protected function order(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orders->sortByDesc('id')->first(),
        );
    }

    public function getMainCategoryAttribute()
    {
        return  $this->categories()->wherePivot('main', 1)->first();
    }

    public function getSubCategoryAttribute()
    {
        return  $this->categories()->wherePivot('main', null)->first();
    }

    public function getFaceToFaceAttribute()
    {
        return  $this->deliveryMethods->firstWhere('code', 0);
    }

    public function getHomeDeliveryAttribute()
    {
        return  $this->deliveryMethods->firstWhere('code', 1);
    }

    public function getCrossBorderDeliveryAttribute()
    {
        return  $this->deliveryMethods->firstWhere('code', 2);
    }

    public function getMainImageAttribute()
    {
        return  $this->blImages()->wherePivot('main', 1)->first();
    }

    public function getOtherImagesAttribute()
    {
        return  $this->blImages()->wherePivot('main', null)->get();
    }

    public function getNextBidAttribute()
    {
        $bid = $this->current_bid;

        if ($bid >= 0 &&  $bid <= 500) {
            return $bid+50;
        } elseif ($bid >= 501 &&  $bid <= 5000) {
            return $bid+250;
        }elseif ($bid >= 5001 &&  $bid <= 10000) {
            return $bid+500;
        } elseif ($bid >= 10001 &&  $bid <= 25000) {
            return $bid+2500;
        } elseif ($bid >= 25001 &&  $bid <= 50000) {
            return $bid+5000;
        } elseif ($bid >= 50001 &&  $bid <= 250000) {
            return $bid+10000;
        } elseif ($bid >= 250001 &&  $bid <= 1000000) {
            return $bid+50000;
        }  elseif ($bid >= 1000001) {
            return $bid+100000;
        }
    }

    public function getAuctionStartAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->auction_start_at)->format('Y-m-d H:i');
    }

    public function getTopBidderIdAttribute()
    {
        if($this->bidRecords->count() != 0) {
            return $this->bidRecords()->latest()->first()->bidder_id;
        } else {
            return null;
        }
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'categories' => $this->categories()->pluck('name')->implode(' '),
            'specifications' => $this->specifications,
        ];
    }
}
