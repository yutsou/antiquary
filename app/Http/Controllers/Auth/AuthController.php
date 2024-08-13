<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LineService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\CustomFacades\CustomClass;
use App\Services\UserService;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private $userService, $lineService, $noticeService;

    public function __construct(UserService $userService, LineService $lineService)
    {
        $this->userService = $userService;
        $this->lineService = $lineService;
    }

    protected function passwordRules()
    {
        return ['required', 'string', new Password, 'confirmed'];
    }

    static function showRegister()
    {
        $customView = CustomClass::viewWithTitle(view('auth.register'), '註冊會員');
        return $customView;
    }

    public function register(Request $request)
    {
        $input = $request->all();
        $rules = [
            'email' => 'required|unique:users|max:255',
            'password' => $this->passwordRules(),
        ];
        $messages = [
            'email.unique'=>'電子郵件已被使用過',
            'password.confirmed'=>'密碼不一致',
        ];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 422); // 400 being the HTTP code for an invalid request.
        }

        $this->userService->register($request);

        return Response::json(array(
            'success' => route('account'),
            'errors' => false
        ), 200);
    }

    static function showLogin(Request $request)
    {
        $redirectUrl = $request->redirectUrl;
        $customView = CustomClass::viewWithTitle(view('auth.login')->with('redirectUrl', $redirectUrl), '會員登入');
        return $customView;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->userService->login($request, $credentials);

        if (Auth::check()) {
            $redirect = $this->userService->afterLoginRedirect($request->redirectUrl);
            return response()->json([
                'success' => $redirect,
                'errors' => false
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'errors' => ['email' => ['電子郵件或密碼輸入錯誤']]
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        $this->userService->logout($request);
        return redirect('/');
    }

    public function switchRole()
    {
        $redirect = $this->userService->switchRole();
        return redirect($redirect);
    }

    public function generateLineVerifyCode()
    {
        $verifyCode = $this->userService->generateLineVerifyCode();
        return $verifyCode;
    }

    public function showLineVerifyBind()
    {
        $customView = CustomClass::viewWithTitle(view('auth.line_verify_bind'), '帳號綁定');
        return $customView;
    }

    public function lineBind(Request $request)
    {
        if(isset($request->linkToken))
        {
            $linkToken = $request->linkToken;
            $nonce = hash('sha512', Carbon::now());
            $userId = Cache::get('line-'.$request->bind_verify_code);
            if($userId === null) {
                return redirect()->back()->withErrors(['warning' => '驗證碼錯誤']);
            }

            $user = $this->userService->getUser($userId);

            $user->update(['line_nonce'=>$nonce]);

            return redirect()->route('auth.line_bind.confirm', ['linkToken'=>$linkToken, 'nonce'=>$nonce]);
        }
    }

    public function generateLineBidLink($request)
    {
        return "https://access.line.me/dialog/bot/accountLink?linkToken=".$request->linkToken."&nonce=".$request->nonce;
    }

    public function showLineBindConfirm(Request $request)
    {
        $link = $this->generateLineBidLink($request);
        $customView = CustomClass::viewWithTitle(view('auth.line_bind_confirm')->with('link', $link), '綁定確認');
        return $customView;
    }

    public function sendVerifyCode(Request $request)
    {
        $result = $this->userService->sendVerifyCode($request->type);
        return $result;
    }

    public function verifyCode(Request $request)
    {
        $result = $this->userService->codeVerify($request->type, $request->inputCode);
        return $result;
    }

    public function changePassword()
    {
        $customView = CustomClass::viewWithTitle(view('auth.change_password'), '更改密碼');
        return $customView;
    }

    public function updatePassword(Request $request)
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);
        $validator = Validator::make($request->all(), [
            'password' => $this->passwordRules(),
        ], ['password.confirmed'=>'密碼不一致']);

        $validator->after(function ($validator) use ($user, $request) {
            if (! isset($request->current_password) || ! Hash::check($request->current_password, $user->password)) {
                $validator->errors()->add('current_password', __('輸入的密碼與原有的密碼不相符'));
            }
        });

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();
    }

    public function showBind()
    {
        $user = Auth::user();
        $customView = CustomClass::viewWithTitle(view('account.profiles.bind_account')->with('user', $user), '帳號綁定');
        return $customView;
    }

    public function redirectGoogleHandle(Request $request)
    {
        $redirectUrl = $request->redirectUrl;
        $googleLoginUrl = $this->getGoogleLoginUrl($redirectUrl);
        return redirect($googleLoginUrl);
    }

    public function getGoogleLoginUrl($redirectUrl)
    {
        $state = http_build_query([
            'csrfToken' => csrf_token(),
            'redirectUrl' => $redirectUrl,
        ]);
        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ];

        $googleUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

        return $googleUrl;
    }

    public function googleCallback(Request $request)#bind or register
    {
        $state = $request->query('state');

        parse_str($state, $stateParams);

        $csrfToken = $stateParams['csrfToken'] ?? null;
        $redirectUrl = $stateParams['redirectUrl'] ?? null;


        if($csrfToken !== csrf_token())
        {
            return redirect()->route('mart.warning.show');
        }

        $client = new Client();

        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('auth.google.callback'),
                'grant_type' => 'authorization_code',
                'code' => $request->input('code'),
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        $accessToken = $tokenData['access_token'];

        // 使用 Access Token 获取用户信息
        $userResponse = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $userData = json_decode($userResponse->getBody(), true);

        $googleId = $userData['sub'];

        if (Auth::check()) {
            $result = $this->userService->bindAccount(Auth::user()->id, 'google', $googleId);
            if($result) {
                return redirect()->route('account.bind.show')->with('success', '成功綁定 Google');
            } else {
                return redirect()->route('account.bind.show')->with('warning', '此 Google 帳號已綁定另一個帳號');
            }

        } else {
            $user = $this->userService->getUserByOauth('google', $googleId);
            if ($user !== null) {
                Auth::login($user);
                if ($redirectUrl == null) {
                    return redirect()->route('account');
                } else {
                    return redirect($redirectUrl);
                }
            } else {
                return redirect()->route('login')->withErrors([
                    'warning' => '沒有與這個Google帳號綁定的帳號',
                ]);
            }
        }
    }

    public function redirectLineLogin(Request $request)
    {
        $redirectUrl = $request->redirectUrl;
        $lineLoginUrl = $this->lineService->getLoginUrl($redirectUrl);
        return redirect($lineLoginUrl);
    }

    public function lineCallback(Request $request)
    {
        $state = $request->query('state');

        parse_str($state, $stateParams);

        $csrfToken = $stateParams['csrfToken'] ?? null;
        $redirectUrl = $stateParams['redirectUrl'] ?? null;


        if($csrfToken !== csrf_token())
        {
            return redirect()->route('mart.warning.show');
        }
        $code = $request->code;
        $response = $this->lineService->getLineToken($code);
        $oauthUser = $this->lineService->getUserProfile($response['access_token']);

        $user = $this->userService->getUserByOauth('line', $oauthUser['userId']);

        if($user == null) {
            return redirect()->route('login')->withErrors(['warning' => '沒有與這個LINE帳號綁定的帳號']);
        }

        Auth::login($user);

        $redirect = $this->userService->afterLoginRedirect($redirectUrl);

        return redirect($redirect);
    }

    public function showPasswordForgot()
    {
        $customView = CustomClass::viewWithTitle(view('auth.forgot_password'), '忘記密碼');
        return $customView;
    }

    public function sendPasswordResetConfirm(Request $request)
    {
        $result = $this->userService->sendPasswordResetConfirm($request->email);
        if($result) {
            return response()->json(['success' => '']);
        } else {
            return response()->json(['error' => ['沒有此Email帳號，或是格式有誤']]);
        }
    }

    public function showPasswordReset($token)
    {
        $customView = CustomClass::viewWithTitle(view('auth.reset_password')->with('token', $token), '重置密碼');
        return $customView;
    }

    public function resetPassword(Request $request)
    {
        $userId = Cache::get($request->token);

        if ($userId !== null) {
            $user = User::find($userId);
            $validator = Validator::make($request->all(), [
                'password' => $this->passwordRules(),
            ], ['password.confirmed'=>'密碼不一致']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();
        } else {
            return response()->json(['error' => ['密碼重置已失效，請重新要求新的密碼重置信']]);
        }
    }
}
