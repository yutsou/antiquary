<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergeShippingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'merge_shipping_request_id',
        'lot_id',
        'quantity',
        'original_shipping_fee',
    ];

    // 關聯 MergeShippingRequest
    public function mergeShippingRequest()
    {
        return $this->belongsTo(MergeShippingRequest::class);
    }

    // 關聯 Lot
    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
