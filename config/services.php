<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    'brasil_api' => [
        'base_url' => env('BRASIL_API_BASE_URL', 'https://brasilapi.com.br/api'),
        'timeout' => (int) env('BRASIL_API_TIMEOUT', 10),
    ],

    'viacep' => [
        'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br'),
        'timeout' => (int) env('VIACEP_TIMEOUT', 10),
    ],

];
