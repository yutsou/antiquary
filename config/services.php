<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'ecpay' => [
        'hash_key' => env('ECPAY_HASH_KEY'),
        'hash_iv' => env('ECPAY_HASH_IV'),
        'merchant_id' => env('ECPAY_MERCHANT_ID')
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/google/callback',
    ],

    'line' => [
        'bot_channel_access_token' => env('LINE_BOT_CHANNEL_ACCESS_TOKEN'),
        'bot_channel_secret' => env('LINE_BOT_CHANNEL_SECRET'),
        'login_channel_id' => env('LINE_LOGIN_CHANNEL_ID'),
        'login_channel_secret' => env('LINE_LOGIN_CHANNEL_SECRET'),
        'authorize_base_url' => 'https://access.line.me/oauth2/v2.1/authorize',
        'get_token_url' => 'https://api.line.me/oauth2/v2.1/token',
        'get_user_profile_url' => 'https://api.line.me/v2/profile',
    ],

];
