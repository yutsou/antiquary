<?php

namespace App\Services;

use GuzzleHttp\Client;

class LineService
{
    public function getLoginUrl()
    {
        // 組成 Line Login Url
        $url = config('services.line.authorize_base_url');
        $url .= '?response_type=code';
        $url .= '&client_id=' . config('services.line.login_channel_id');
        $url .= '&redirect_uri='  . config('app.url') . '/auth/line/callback';
        $url .= '&state='.csrf_token();
        $url .= '&scope=profile%20openid%20email';

        return $url;
    }

    public function getLineToken($code)
    {
        $client = new Client();
        $response = $client->request('POST', config('services.line.get_token_url'), [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('app.url'). '/auth/line/callback',
                'client_id' => config('services.line.login_channel_id'),
                'client_secret' => config('services.line.login_channel_secret')
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getUserProfile($token)
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $response = $client->request('GET', config('services.line.get_user_profile_url'), [
            'headers' => $headers
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

}
