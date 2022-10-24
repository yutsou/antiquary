<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageAble');
    }

    public function defaultSpecificationTitles()
    {
        return $this->hasMany(DefaultSpecificationTitle::class);
    }

    public function lots()
    {
        return $this->belongsToMany(Lot::class);
    }

    protected $fillable = ['parent_id', 'url_name', 'name', 'image_id', 'color_hex'];
}
