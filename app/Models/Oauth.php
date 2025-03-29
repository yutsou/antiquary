<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Oauth
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string|null $oauth_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth query()
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereOauthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereUserId($value)
 * @mixin \Eloquent
 */
class Oauth extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
