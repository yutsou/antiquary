<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'intro',
        'content',
        'auctioneer_id'
    ];

    public function auctioneer()
    {
        return $this->belongsTo(User::class, 'auctioneer_id');
    }
}
