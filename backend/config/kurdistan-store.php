<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    'cors' => [
        'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))),
    ],

    'shipping' => [
        'flat_rate' => (float) env('SHIPPING_FLAT_RATE', 5.00),
        'free_threshold' => (float) env('SHIPPING_FREE_THRESHOLD', 200.00),
    ],

    'rate_limits' => [
        'api'  => env('API_RATE_LIMIT', 120),
        'auth' => env('AUTH_RATE_LIMIT', 10),
    ],

    'payments' => [
        'default' => 'cashondelivery',
        'gateways' => [
            'cashondelivery' => [
                'title'  => 'Cash on Delivery',
                'class'  => \Store\KurdistanStore\Services\Payment\Gateways\CashOnDeliveryGateway::class,
                'active' => true,
            ],
        ],
    ],

    'phone' => [
        'allowed_country_codes' => explode(',', env('PHONE_ALLOWED_COUNTRIES', '+964')),
        'e164_pattern'          => env('PHONE_E164_PATTERN', '^\+9647\d{9}$'),
        'default_country_code'  => env('PHONE_DEFAULT_COUNTRY', '+964'),
    ],

    'seo' => [
        'site_name'           => env('SEO_SITE_NAME', 'Kurdistan Store Demo'),
        'default_description' => env('SEO_DEFAULT_DESCRIPTION', 'Essentials for the modern minimalist.'),
        'twitter_handle'      => env('SEO_TWITTER_HANDLE'),
    ],

    'locations' => [
        'Erbil'         => ['Erbil City', 'Shaqlawa', 'Soran', 'Koya', 'Mergasor'],
        'Sulaymaniyah'  => ['Sulaymaniyah City', 'Halabja', 'Ranya', 'Qaladze', 'Chamchamal'],
        'Duhok'         => ['Duhok City', 'Zakho', 'Amedi', 'Semel', 'Akre'],
        'Halabja'       => ['Halabja City', 'Khurmal', 'Byara'],
    ],
];
