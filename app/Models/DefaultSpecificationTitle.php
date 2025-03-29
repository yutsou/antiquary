<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DefaultSpecificationTitle
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle query()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultSpecificationTitle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DefaultSpecificationTitle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
