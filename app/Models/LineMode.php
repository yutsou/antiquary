<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LineMode
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $mode
 * @property int|null $step
 * @property string|null $extra_info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereExtraInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMode whereUserId($value)
 * @mixin \Eloquent
 */
class LineMode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
