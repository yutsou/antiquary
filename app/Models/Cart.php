<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'lot_id',
        'quantity',
    ];

    // 關聯 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯 Lot（假設你商品是 lots table）
    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
