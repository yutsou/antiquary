<?php

namespace App\Services;

use App\Jobs\SendEmail;
use App\Jobs\SendSms;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\DataTables;
class UserService extends UserRepository
{
    public function register($request)
    {
        $input = $request->all();
        $input['password'] = Hash::make($request->password);
        $input['commission_rate'] = config('shop.promotion.commission_rate');
        $input['premium_rate'] = config('shop.promotion.premium_rate');

        $newUser = UserRepository::create($input);

        Auth::login($newUser);
    }

    public function login($request, $credentials)
    {
        if (isset($request->remember)) {
            if (Auth::attempt($credentials, $request->remember)) {
                $request->session()->regenerate();
                if(isset($request->redirectUrl)) {
                    $redirectUrl = str_replace('_', '/', $request->redirectUrl);
                    return redirect($redirectUrl);
                } else {
                    return $this->switchRole();
                }
            }
        } else {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                if(isset($request->redirectUrl)) {
                    $redirectUrl = str_replace('_', '/', $request->redirectUrl);
                    return redirect($redirectUrl);
                } else {
                    return $this->switchRole();
                }
            }
        }

        return back()->withErrors([
            'warning' => '電子郵件或密碼錯誤',
        ])->withInput();;
    }

    public function logout($request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function switchRole()
    {
        $role = Auth::user()->role;
        $redirects = ['/auctioneer/dashboard', '/expert/dashboard', '/account', '/account'];
        return redirect($redirects[$role]);
    }

    public function createUser($request, $role)
    {
        $input = $request->all();
        $input['name'] = $request->name;
        $input['password'] = Hash::make($request->password);
        $input['role'] = $role;

        $user = UserRepository::create($input);

        return $user;
    }

    public function getUser($userId)
    {
        return UserRepository::find($userId);
    }

    public function ajaxExperts()
    {
        $usersWhoIsexpert = UserRepository::all()->where('role', 1);

        #$expert = UserRepository::find(3)->experts;
        #dd($expert);
        $datatable = DataTables::of($usersWhoIsexpert)
        ->addColumn('id', function ($user)
        {
            return $user->id;
        })
        ->addColumn('name', function ($user)
        {
            return $user->name;
        })
        ->addColumn('domain', function ($user)
        {
            $domains = $user->domains;
            $domainNames = $domains->pluck('category')->pluck('name')->toArray();

            return implode( '、', $domainNames);
        })
        ->addColumn('action', function ($expert)
        {
            return '<a href="'.route('auctioneer.experts.edit', ['userId'=>$expert->id]).'" class="uk-button custom-button-1">設定</a>';
        })
        ->rawColumns(['id', 'name', 'domain', 'action'])
        ->toJson();
        return  $datatable;
    }

    public function getFavorites($userId)
    {
        $user = $this->getUser($userId);
        return $user->favorites;
    }

    public function generateLineVerifyCode()
    {
        $user = Auth::user();
        $verifyCode = $this->generateVerifyCode();
        if(Cache::get('line-'.$verifyCode) !== null){
            $verifyCode = $this->generateLineVerifyCode();
        }
        Cache::put('line-'.$verifyCode, $user->id, $seconds = 1800);
        return $verifyCode;
    }

    public function generateVerifyCode()
    {
        $rand = str_pad(strval(rand(0,999999)), 6, "0", STR_PAD_LEFT);
        return $rand;
    }

    public function sendVerifyCodeGuard($type, $userId)
    {
        switch($type) {
            case 'email':
                $limit = Cache::get('emailVerifyLimit-'.$userId);
                if(isset($limit)) {
                    return false;
                } else {
                    return true;
                }
            case 'phone':
                $limit = Cache::get('phoneVerifyLimit-'.$userId);
                if(isset($limit)) {
                    return false;
                } else {
                    return true;
                }
        }
    }

    public function sendVerifyCode($type)
    {
        $user = Auth::user();
        $verifyCode = $this->generateVerifyCode();

        if($this->sendVerifyCodeGuard($type, $user->id)) {
            switch($type) {
                case 'email':
                    Cache::forget('emailVerifyCode-'.$user->id);
                    Cache::put('emailVerifyCode-'.$user->id, $verifyCode, $seconds = 1800);
                    Cache::put('emailVerifyLimit-'.$user->id, now()->addMinutes(1), $seconds = 60);
                    $emailContent['type'] = 'verifyCode';
                    $emailContent['user'] = $user;
                    $emailContent['emailAddress'] = $user->email;
                    $emailContent['verifyCode'] = $verifyCode;
                    SendEmail::dispatch($emailContent);
                    break;
                case 'phone':
                    Cache::forget('phoneVerifyCode-'.$user->id);
                    Cache::put('phoneVerifyCode-'.$user->id, $verifyCode, $seconds = 1800);
                    Cache::put('phoneVerifyLimit-'.$user->id, now()->addMinutes(1), $seconds = 60);
                    $message = 'Antiquary 正在進行手機簡訊驗證，驗證碼:'.$verifyCode.'，請在30分鐘內進行驗證';
                    SendSms::dispatch($user->phone, $message);
            }
            return 1;
        } else {
            switch($type) {
                case 'email':
                    return Response::json(['error' => '還需要再等' . Cache::get('emailVerifyLimit-' . $user->id)->diffInSeconds(Carbon::now()) . '秒，才能寄出驗證碼'], 404);
                case 'phone':
                    return Response::json(['error' => '還需要再等'.Cache::get('phoneVerifyLimit-'.$user->id)->diffInSeconds(Carbon::now()).'秒，才能寄出驗證碼'], 404);
            }
        }
    }

    public function codeVerify($type, $inputCode)
    {
        $user = Auth::user();
        $existCode = '';
        switch($type) {
            case 'email':
                $existCode = Cache::get('emailVerifyCode-'.$user->id);

                if($existCode === $inputCode) {
                    $user->update(['email_verified_at'=>now()]);
                } else {
                    return Response::json(['error' => '驗證失敗'], 404);
                }
                break;
            case 'phone':
                $existCode = Cache::get('phoneVerifyCode-'.$user->id);
                if($existCode === $inputCode) {
                    $user->update(['phone_verified_at'=>now()]);
                } else {
                    return Response::json(['error' => '驗證失敗'], 404);
                }
                break;
        }
    }

    public function updateProfile($input)
    {
        UserRepository::fill($input, Auth::user()->id);
    }

    public function bindAccount($userId, $type, $oauthId)
    {
        $user = $this->getUser($userId);
        if(DB::table('oauths')->where('type', $type)->where('oauth_id', $oauthId)->exists()) {
            return false;
        } else {
            $user->oauths()->insert(['user_id'=>$userId, 'type'=>$type, 'oauth_id'=>$oauthId]);
            return true;
        }

    }

    public function confirmBind($nonce, $lineId)
    {
        $user = UserRepository::all()->where('line_nonce', $nonce)->first();
        $user->update(['line_id'=>$lineId]);
        $this->bindAccount($user->id, 'line', $lineId);
    }

    public function getUserByOauth($type, $oauthId)
    {
        $result = DB::table('oauths')->where('type', $type)->where('oauth_id', $oauthId)->first();
        if($result !== null) {
            $user = $this->getUser($result->user_id);
            return $user;
        } else {
            return null;
        }
    }

    public function getUserByEmail($email)
    {
        $result = DB::table('users')->where('email', $email)->first();
        return $result;
    }

    public function sendPasswordResetConfirm($email)
    {
        $user = $this->getUserByEmail($email);
        if($user !== null) {
            $token = sha1(Carbon::now().$user->id);
            Cache::put($token, $user->id, $seconds = 1800);
            $emailContent['type'] = 'passwordResetConfirm';
            $emailContent['user'] = $user;
            $emailContent['emailAddress'] = $email;
            $emailContent['passwordResetLink'] = route('auth.password_reset.show', ['token'=>$token]);
            SendEmail::dispatch($emailContent);
            return true;
        } else {
            return false;
        }

    }

    public function getOwnerApplicationNoticeCount($userId)
    {
        return UserRepository::getLotNoticeCount([1,2], $userId);
    }

    public function getOwnerSellingLotNoticeCount($userId)
    {
        $count = 0;
        $user = $this->getUser($userId);
        foreach($user->ownLots as $lot) {
            if($lot->entrust == 0){
                $order = $lot->order;
                if($order != null) {
                    $orderStatus = $lot->order->status;
                    if(in_array($orderStatus, [12, 13, 20])) {
                        $count += 1;
                    }
                }
            }
        }
        $count += UserRepository::getLotNoticeCount([23, 25], $userId);
        return $count;

    }

    public function getOrderLotNoticeCount($userId)
    {
        return UserRepository::getOrderNoticeCount([0, 12, 21, 10], $userId);
    }

    public function getReturnedLotNoticeCount($userId)
    {
        return UserRepository::getLotNoticeCount([32], $userId);
    }

    public function updateAccountStatus($type, $user)#1: temporary,2: forever
    {
        UserRepository::update(['status'=>$type], $user->id);
    }
}
