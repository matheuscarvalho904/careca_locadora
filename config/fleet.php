<?php

return [
    'vehicle_lookup' => [
        'provider' => env('VEHICLE_LOOKUP_PROVIDER', 'fake'),

        'api_placas' => [
            'base_url' => env('API_PLACAS_BASE_URL', 'https://wdapi2.com.br'),
            'token' => env('API_PLACAS_TOKEN'),
            'timeout' => (int) env('API_PLACAS_TIMEOUT', 15),
        ],

        'puxaplaca' => [
            'base_url' => env('PUXAPLACA_BASE_URL', 'https://api.puxaplaca.app/v2'),
            'token' => env('PUXAPLACA_TOKEN'),
            'timeout' => (int) env('PUXAPLACA_TIMEOUT', 15),
            'check_balance_before_lookup' => env('PUXAPLACA_CHECK_BALANCE', true),
            'minimum_balance' => (float) env('PUXAPLACA_MINIMUM_BALANCE', 0.08),
        ],
    ],
];
