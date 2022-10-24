<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = ['payment_due_at'];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionRecords()
    {
        return $this->hasMany(TransactionRecord::class);
    }

    public function orderRecords()
    {
        return $this->hasMany(OrderRecord::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function logisticRecords()
    {
        return $this->morphMany(LogisticRecord::class, 'logistic_recordable');
    }
}
