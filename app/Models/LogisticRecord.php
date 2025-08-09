<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LogisticRecord
 *
 * @property int $id
 * @property int $type
 * @property string|null $addressee_name
 * @property string|null $addressee_phone
 * @property string|null $addressee_address
 * @property string|null $company_name
 * @property string|null $tracking_code
 * @property string|null $face_to_face_address
 * @property string|null $delivery_zip_code
 * @property string|null $county
 * @property string|null $district
 * @property string|null $delivery_address
 * @property string|null $cross_board_delivery_country
 * @property string|null $cross_board_delivery_country_code
 * @property string|null $cross_board_delivery_address
 * @property string|null $remark
 * @property int $logistic_recordable_id
 * @property string $logistic_recordable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $logistic_recordable
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereAddresseeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereAddresseeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereAddresseePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereCrossBoardDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereCrossBoardDeliveryCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereCrossBoardDeliveryCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereDeliveryZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereFaceToFaceAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereLogisticRecordableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereLogisticRecordableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereTrackingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogisticRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LogisticRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function logistic_recordable()
    {
        return $this->morphTo();
    }
}
