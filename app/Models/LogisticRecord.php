<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function logistic_recordable()
    {
        return $this->morphTo();
    }
}
