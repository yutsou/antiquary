<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = ['start_at', 'expect_end_at',];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('alias');
    }

    public function getEndAtAttribue()
    {
        return $this->lots->sortBy('auction_end_at')->last()->auction_end_at;
    }

    public function getStartAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->start_at)->format('Y-m-d H:i');
    }

    public function getExpectEndAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->expect_end_at)->format('Y-m-d H:i');
    }

}
