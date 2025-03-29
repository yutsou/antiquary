<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TransactionRecord
 *
 * @property int $id
 * @property int $order_record_id
 * @property int $status
 * @property int $payment_method
 * @property string|null $system_order_id
 * @property string|null $av_code
 * @property int|null $remitter_id
 * @property string|null $remitter_account
 * @property int|null $payee_id
 * @property string|null $payee_account
 * @property string|null $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereAvCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereOrderRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord wherePayeeAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord wherePayeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereRemitterAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereRemitterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereSystemOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransactionRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
