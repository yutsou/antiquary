<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function transactionRecord()
    {
        return $this->hasOne(TransactionRecord::class);
    }
}
