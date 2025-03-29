<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AutoBid
 *
 * @property int $id
 * @property int $user_id
 * @property int $lot_id
 * @property string $bid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid query()
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutoBid whereUserId($value)
 * @mixin \Eloquent
 */
class AutoBid extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
