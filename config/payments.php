<?php

return [
    'currency' => 'UZS',

    'paycom' => [
        'enabled' => true,
        'api_login' => env('PAYCOM_API_LOGIN', 'Paycom'),
        'api_password' => env('PAYCOM_API_PASSWORD'),
        'login' => env('PAYCOM_LOGIN'),
        'password_hash' => env('PAYCOM_PASSWORD_HASH'),
        'merchant_id' => env('PAYCOM_MERCHANT_ID'),
        'merchant_key' => env('PAYCOM_MERCHANT_KEY'),
        'payment_url' => env('PAYCOM_PAYMENT_URL', 'https://payme.uz/fallback/merchant/'),
        'return_url' => env('PAYCOM_RETURN_URL', '/'),
        'min_amount' => 100,
        'max_amount' => 100000000,
        'timeout' => 43200000,
    ],

    'click' => [
        'enabled' => true,
        'merchant_id' => env('CLICK_MERCHANT_ID'),
        'service_id' => env('CLICK_SERVICE_ID'),
        'merchant_user_id' => env('CLICK_USER_ID'),
        'secret_key' => env('CLICK_SECRET_KEY'),
        'payment_url' => 'https://my.click.uz/services/pay/',
        'invoice_url' => 'https://merchant.click.uz/api/',
        'return_url' => env('CLICK_RETURN_URL', '/'),
        'min_amount' => 500,
        'max_amount' => 100000000,
        'check_signature' => false,
    ],

    'paynet' => [
        'enabled' => env('PAYNET_ENABLED', true),
        'live_mode' => env('PAYNET_LIVE_MODE', true),

        'username' => env('PAYNET_USERNAME'),
        'password_hash' => env('PAYNET_PASSWORD_HASH'),

        'service_id' => (int) env('PAYNET_SERVICE_ID', 1),

        'min_amount' => (float) env('PAYNET_MIN_AMOUNT', 500),
        'max_amount' => (float) env('PAYNET_MAX_AMOUNT', 100000000),

        'allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('PAYNET_ALLOWED_IPS', '')))),

        'service_location' => env('PAYNET_SERVICE_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api/v1/billing/paynet'),
        'wsdl_location' => env('PAYNET_WSDL_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/paynet/ProviderWebService.wsdl'),
        'xsd_location' => env('PAYNET_XSD_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/paynet/ProviderWebService.xsd'),
    ],

    'uzumbank' => [
        'enabled' => env('UZUMBANK_ENABLED', true),
        'live_mode' => env('UZUMBANK_LIVE_MODE', true),

        'username' => env('UZUMBANK_USERNAME'),
        'password_hash' => env('UZUMBANK_PASSWORD_HASH'),

        'service_id' => (int) env('UZUMBANK_SERVICE_ID', 1),

        'min_amount' => (float) env('UZUMBANK_MIN_AMOUNT', 500),
        'max_amount' => (float) env('UZUMBANK_MAX_AMOUNT', 100000000),

        'allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('UZUMBANK_ALLOWED_IPS', '')))),

        'service_location' => env('UZUMBANK_SERVICE_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api/v1/billing/uzumbank'),
        'wsdl_location' => env('UZUMBANK_WSDL_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/uzumbank/ProviderWebService.wsdl'),
        'xsd_location' => env('UZUMBANK_XSD_LOCATION', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/uzumbank/ProviderWebService.xsd'),
    ],
];
