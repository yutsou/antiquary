<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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
            return true;
        } else {
            return false;
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
