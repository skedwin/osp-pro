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

    'nckenya' => [
        'token_base_url' => env('NC_KENYA_TOKEN_BASE', 'https://api.nckenya.go.ke'),
        'token_endpoint' => env('NC_KENYA_TOKEN_ENDPOINT', '/login'),
        'sso_base_url' => env('NC_KENYA_SSO_BASE', 'https://api.nckenya.com'),
        'sso_endpoint' => env('NC_KENYA_SSO_ENDPOINT', '/single-sign-on'),
        'portal_base_url' => env('NC_KENYA_PORTAL_BASE', 'https://api.nckenya.go.ke'),
        'client_username' => env('NC_KENYA_CLIENT_USERNAME'),
        'client_key' => env('NC_KENYA_CLIENT_KEY'),
    ],

    'nck_api' => [
        'url' => env('NCK_API_URL', 'https://api.nckenya.go.ke'),
        'timeout' => env('NCK_API_TIMEOUT', 30),
    ],

    'outmigration_api' => [
        'url' => env('OUTMIGRATION_API_URL', env('NCK_API_URL', 'https://api.nckenya.go.ke')),
    ],

];
