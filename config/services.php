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

    'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],

    'azure_doc' => [
            'endpoint' => env('AZURE_DOC_ENDPOINT'),
            'key'      => env('AZURE_DOC_KEY'),
             'model'    => env('AZURE_DOC_MODEL', 'prebuilt-idDocument'),
        ],

        'survey_link' => env('SURVEY_LINK', ''),

  'ngenius' => [
        'base_url'   => env('NGENIUS_API_BASE', 'https://api-gateway.ngenius-payments.com'),
        'basic_auth' => env('NGENIUS_BASIC_AUTH'),
        'outlet_id'  => env('NGENIUS_OUTLET_ID'),
    ],



];
