<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $line_id
 * @property string|null $line_nonce
 * @property int $role
 * @property int $status
 * @property float|null $commission_rate
 * @property float|null $premium_rate
 * @property \Illuminate\Support\Carbon|null $birthday
 * @property string|null $county
 * @property string|null $district
 * @property string|null $zip_code
 * @property string|null $address
 * @property string|null $bank_name
 * @property string|null $bank_branch_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auction> $auctions
 * @property-read int|null $auctions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BidRecord> $bidRecords
 * @property-read int|null $bid_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Domain> $domains
 * @property-read int|null $domains_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lot> $favoriteLots
 * @property-read int|null $favorite_lots_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read mixed $birthday_format
 * @property-read mixed $favorite
 * @property-read mixed $google_bind_status
 * @property-read mixed $line_bind_status
 * @property-read mixed $verification_status
 * @property-read \App\Models\LineMode|null $lineMode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notice> $notices
 * @property-read int|null $notices_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Oauth> $oauths
 * @property-read int|null $oauths_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lot> $ownLots
 * @property-read int|null $own_lots_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notice> $unreadNotices
 * @property-read int|null $unread_notices_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBankAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBankAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBankBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLineNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePremiumRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereZipCode($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function ownLots()
    {
        return $this->hasMany(Lot::class, 'owner_id', 'id');
    }

    /*public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }*/

    public function favoriteLots()
    {
        return $this->hasManyThrough(Lot::class, Favorite::class, 'user_id', 'id', 'id', 'lot_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function getFavoriteAttribute($lotId)
    {
        return  $this->favorites()->where('lot_id', $lotId)->exists();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function oauths()
    {
        return $this->hasMany(Oauth::class);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    public function unreadNotices()
    {
        return $this->hasMany(Notice::class)->where('read_at', null);
    }

    public function auctions()#for expert
    {
        return $this->hasMany(Auction::class, 'expert_id', 'id');
    }

    public function bidRecords()
    {
        return $this->hasMany(BidRecord::class, 'bidder_id', 'id');
    }

    public function lineMode()
    {
        return $this->hasOne(LineMode::class);
    }

    public function getVerificationStatusAttribute()
    {
        if($this->email_verified_at !== null && $this->phone_verified_at !== null) {
            return 2;
        } else if($this->email_verified_at !== null && $this->phone_verified_at === null) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getBirthdayFormatAttribute()
    {
        return  Carbon::parse($this->birthday)->format('Y-m-d');
    }

    public function getGoogleBindStatusAttribute()
    {
        return $this->oauths->contains('type', 'google');
    }

    public function getLineBindStatusAttribute()
    {
        return $this->oauths->contains('type', 'line');
    }

    public function getLotAutoBid($lotId)
    {
        return DB::table('auto_bids')->where('lot_id', $lotId)->where('user_id', $this->id)->first();
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function mergeShippingRequests()
    {
        return $this->hasMany(MergeShippingRequest::class);
    }

        public function getCartCountAttribute()
    {
        $cartCount = $this->cartItems()->sum('quantity');

        // 加上已處理的合併運費請求數量（每個請求算一個）
        $mergeShippingCount = $this->mergeShippingRequests()
            ->where('status', 1) // 已處理
            ->count();

        return $cartCount + $mergeShippingCount;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'line_nonce',
        'line_id',
        'link_confirm_number',
        'email_verified_at',
        'phone_verified_at',
        'county',
        'district',
        'zip_code',
        'address',
        'bank_name',
        'bank_branch_name',
        'bank_account_name',
        'bank_account_number',
        'bank_account_number',
        'commission_rate',
        'premium_rate',
        'birthday'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'birthday' => 'datetime'
    ];

    protected $with = ['domains'];
}
