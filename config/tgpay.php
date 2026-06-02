<?php

return [
    'base_url' => env('TGPAY_BASE_URL', 'http://nginx'),
    'api_key' => env('TGPAY_API_KEY'),
    'webhook_secret' => env('TGPAY_WEBHOOK_SECRET'),
    'timeout' => (int) env('TGPAY_TIMEOUT', 30),
];
