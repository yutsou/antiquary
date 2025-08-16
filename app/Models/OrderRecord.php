<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderRecord
 *
 * @property int $id
 * @property int $order_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TransactionRecord|null $transactionRecord
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'order_id',
        'status',
        'remark'
    ];

    public function transactionRecord()
    {
        return $this->hasOne(TransactionRecord::class);
    }
}
