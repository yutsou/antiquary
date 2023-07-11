<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

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
        return $this->belongsToMany(Image::class, 'lot_image', 'lot_id', 'image_id');
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

    public function getMainCategoryAttribute()
    {
        return  $this->categories()->wherePivot('main', 1)->first();
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
