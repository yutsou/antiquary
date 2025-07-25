<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'lot_id',
        'quantity',
        'price',
        'subtotal',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
