<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function imageAble()
    {
        return $this->morphTo();
    }

    public function lots()
    {
        return $this->belongsToMany(Lot::class, 'lot_image');
    }
}
