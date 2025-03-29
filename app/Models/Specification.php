<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Specification
 *
 * @property int $id
 * @property int $lot_id
 * @property string $title
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lot $lot
 * @method static \Illuminate\Database\Eloquent\Builder|Specification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specification whereValue($value)
 * @mixin \Eloquent
 */
class Specification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
