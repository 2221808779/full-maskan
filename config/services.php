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

    'ai' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:8001'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    ],

    'plutu' => [
        'api_key' => env('PLUTU_API_KEY'),
        'access_token' => env('PLUTU_ACCESS_TOKEN'),
        'secret_key' => env('PLUTU_SECRET_KEY'),
    ],

];
