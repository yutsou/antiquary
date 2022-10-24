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

    protected $fillable = [
        'user_id', 'domain', 'brief', 'introduction', 'experience', 'year', 'scale'
    ];
}
