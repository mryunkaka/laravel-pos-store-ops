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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'tmp0' => [
        'upload_url' => env('TMP0_UPLOAD_URL', 'https://tmp0.cc/api/v1/upload'),
        'expiration' => env('TMP0_INVOICE_EXPIRATION', '30d'),
        'timeout' => (int) env('TMP0_TIMEOUT', 30),
        'ca_bundle' => env('TMP0_CA_BUNDLE', PHP_OS_FAMILY === 'Windows' ? 'C:/Server/certs/cacert.pem' : null),
    ],

];
