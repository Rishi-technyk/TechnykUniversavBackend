<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'razorpay'),
    'currency' => env('PAYMENT_CURRENCY', 'INR'),
    'retry_poll_seconds' => env('PAYMENT_RETRY_POLL_SECONDS', 4),
    'poll_timeout_seconds' => env('PAYMENT_POLL_TIMEOUT_SECONDS', 180),
    'callback_expiry_minutes' => env('PAYMENT_CALLBACK_EXPIRY_MINUTES', 90),
    'gateway_branding' => [
        'razorpay' => [
            'logo' => 'https://razorpay.com/favicon.png',
            'accent' => '#0f5bff',
        ],
        'cashfree' => [
            'logo' => 'https://www.cashfree.com/favicon.ico',
            'accent' => '#5b5fc7',
        ],
        'easebuzz' => [
            'logo' => 'https://easebuzz.in/favicon.ico',
            'accent' => '#1f9d77',
        ],
        'paynimo' => [
            'logo' => 'https://worldline.com/favicon.ico',
            'accent' => '#f97316',
        ],
    ],
];
