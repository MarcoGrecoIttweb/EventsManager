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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'tinymce' => [
        'api_key' => env('TINYMCE_API_KEY', ''),
    ],

    /*
    | Google Maps Embed API (opzionale ma consigliato).
    | Console: https://console.cloud.google.com/ — abilita "Maps Embed API"
    | e crea una chiave API. Senza chiave si usa un iframe di fallback.
    */
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

];
