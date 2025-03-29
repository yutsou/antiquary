<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BidRecord
 *
 * @property int $id
 * @property int $lot_id
 * @property int $bidder_id
 * @property string $bidder_alias
 * @property string $bid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $bidder
 * @property-read \App\Models\Lot $lot
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereBidderAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereBidderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BidRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BidRecord extends Model
{
    use HasFactory;

    public function bidder()
    {
        return $this->belongsTo(User::class, 'bidder_id', 'id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
