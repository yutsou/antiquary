<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
