<?php

return [

    'jwt_secret' => env('JWT_SECRET'),
    'service_token' => env('SERVICE_TOKEN'),

    // Public (browser-facing) origins for cross-service navigation links.
    // localhost ports for dev; override per service for a gateway/public deploy.
    'public' => [
        'auth' => env('AUTH_PUBLIC_URL', 'http://localhost:8001'),
        'fleet' => env('FLEET_PUBLIC_URL', 'http://localhost:8002'),
        'rental' => env('RENTAL_PUBLIC_URL', 'http://localhost:8003'),
        'crm' => env('CRM_PUBLIC_URL', 'http://localhost:8004'),
        'billing' => env('BILLING_PUBLIC_URL', 'http://localhost:8005'),
        'maintenance' => env('MAINTENANCE_PUBLIC_URL', 'http://localhost:8006'),
        'analytics' => env('ANALYTICS_PUBLIC_URL', 'http://localhost:8007'),
        'client' => env('CLIENT_PUBLIC_URL', 'http://localhost:8008'),
    ],

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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'auth' => ['url' => env('AUTH_SERVICE_URL', 'http://localhost:8001')],
    'fleet' => ['url' => env('FLEET_SERVICE_URL', 'http://localhost:8002')],
    'rental' => ['url' => env('RENTAL_SERVICE_URL', 'http://localhost:8003')],
    'crm' => ['url' => env('CRM_SERVICE_URL', 'http://localhost:8004')],
    'maintenance' => ['url' => env('MAINTENANCE_SERVICE_URL', 'http://localhost:8006')],
];
