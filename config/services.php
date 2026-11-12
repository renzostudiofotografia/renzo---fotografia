<?php

return [

    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'token' => env('CLOUDFLARE_IMAGES_TOKEN'),
    ],

    'cloudflare_r2' => [
        'account_id' => env('CLOUDFLARE_R2_ACCOUNT_ID'),
        'bucket' => env('CLOUDFLARE_R2_BUCKET'),
        'access_key_id' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
        'secret_access_key' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
        'public_url' => env('CLOUDFLARE_R2_PUBLIC_URL'),
        'optimize' => env('CLOUDFLARE_R2_OPTIMIZE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
