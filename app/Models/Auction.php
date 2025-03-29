<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Auction
 *
 * @property int $id
 * @property int $expert_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $start_at
 * @property \Illuminate\Support\Carbon|null $expect_end_at
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $expect_end_at_format
 * @property-read mixed $start_at_format
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lot> $lots
 * @property-read int|null $lots_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Auction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Auction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Auction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereExpectEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereExpertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Auction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Auction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = ['start_at', 'expect_end_at','last_lot_end_at'];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('alias');
    }

    protected function lastLotEndAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->lots->sortBy('auction_end_at')->last()->auction_end_at,
        );
    }

    public function getStartAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->start_at)->format('Y-m-d H:i');
    }

    public function getExpectEndAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->expect_end_at)->format('Y-m-d H:i');
    }

}
