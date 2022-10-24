<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function lot()
    {
        return $this->belongsTo(Lot::class, 'target_id');
    }

    public function getCreatedAtFormatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('Y-m-d H:i');
    }
}
