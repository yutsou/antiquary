<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidRecord extends Model
{
    use HasFactory;

    public function bidder()
    {
        return $this->belongsTo(User::class, 'bidder_id', 'id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
