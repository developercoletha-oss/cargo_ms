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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'beem' => [
        'key' => env('BEEM_API_KEY'),
        'secret' => env('BEEM_SECRET_KEY'),
        'sender_name' => env('BEEM_SENDER_NAME'),
        'otp_request_url' => env('BEEM_OTP_REQUEST_URL'),
        'otp_verify_url' => env('BEEM_OTP_VERIFY_URL'),
    ],

    'rodline' => [
        'key' => env('RODLINE_API_KEY'),
        'base_url' => env('RODLINE_BASE_URL', 'https://rodline.co.tz/api'),
    ],

];
