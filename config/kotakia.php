<?php

return [
    'payment' => [
        // simulasi (development) | toyyibpay
        'gateway' => env('PAYMENT_GATEWAY', 'simulasi'),
        'currency' => 'MYR',
    ],

    'toyyibpay' => [
        'sandbox' => env('TOYYIBPAY_SANDBOX', true),
        'secret_key' => env('TOYYIBPAY_SECRET_KEY'),
        'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
    ],

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@kotakia.test'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    'uploads' => [
        'image_max_kb' => 4096,
        'music_max_kb' => 15360,
        'video_max_kb' => 51200,
    ],
];
