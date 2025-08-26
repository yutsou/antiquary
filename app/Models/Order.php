<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int $lot_id
 * @property int|null $payment_method
 * @property int|null $delivery_method
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $payment_due_at
 * @property string|null $subtotal
 * @property string|null $delivery_cost
 * @property string|null $total
 * @property string|null $remark
 * @property string|null $owner_real_take
 * @property string|null $commission
 * @property string|null $premium
 * @property string|null $earning
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogisticRecord> $logisticRecords
 * @property-read int|null $logistic_records_count
 * @property-read \App\Models\Lot $lot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderRecord> $orderRecords
 * @property-read int|null $order_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransactionRecord> $transactionRecords
 * @property-read int|null $transaction_records_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveryCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveryMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereEarning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOwnerRealTake($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = ['payment_due_at'];

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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function lots()
    {
        return $this->belongsToMany(Lot::class, 'order_items', 'order_id', 'lot_id');
    }
}
