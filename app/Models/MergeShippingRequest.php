<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergeShippingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_shipping_fee',
        'new_shipping_fee',
        'status',
        'delivery_method',
        'remark',
    ];

    // 關聯 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯 MergeShippingItem
    public function items()
    {
        return $this->hasMany(MergeShippingItem::class);
    }

    // 關聯 LogisticRecord
    public function logisticRecords()
    {
        return $this->morphMany(LogisticRecord::class, 'logistic_recordable');
    }

    // 狀態常數
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_EXPIRED = 4;
    const STATUS_REMOVED = 5;

    // 運送方式常數
    const DELIVERY_HOME = 1;
    const DELIVERY_CROSS_BORDER = 2;

    // 取得狀態文字
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '待處理';
            case self::STATUS_APPROVED:
                return '已處理';
            case self::STATUS_REJECTED:
                return '已拒絕';
            case self::STATUS_COMPLETED:
                return '已完成';
            case self::STATUS_EXPIRED:
                return '已過期';
            case self::STATUS_REMOVED:
                return '已移除';
            default:
                return '未知';
        }
    }

    // 取得運送方式文字
    public function getDeliveryMethodTextAttribute()
    {
        switch ($this->delivery_method) {
            case self::DELIVERY_HOME:
                return '宅配';
            case self::DELIVERY_CROSS_BORDER:
                return '境外物流';
            default:
                return '未知';
        }
    }
}
