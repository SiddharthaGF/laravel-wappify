<?php

declare(strict_types=1);

return [
    'client' => [
        'url' => 'https://graph.facebook.com',
        'version' => 'v19.0',
    ],

    'accounts' => [
        'default' => [
            'profile' => 'default',
            'number_id' => env('WHATSAPP_API_PHONE_NUMBER_ID'),
            'token' => env('WHATSAPP_API_TOKEN'),
            'app_secret' => env('WHATSAPP_APP_SECRET'),
            'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
            'queue' => [
                'connection' => 'redis',
                'name' => 'wappify',
                'tries' => 3,
                'timeout' => 5,
            ],
            'download' => [
                'automatic' => true,
                'strategy' => 'spatie',
            ],
        ],
    ],

    'api' => [
        'prefix' => 'api',
        'path' => 'whatsapp',
        'name' => 'wappify',
        'middleware_webhooks' => [
            'facebook',
        ],
        'middleware_resources' => [
            // 'auth',
        ],
    ],

    'middleware' => [
        'facebook' => [
            'name' => 'facebook',
        ],
        'auth' => [
            'name' => 'auth',
            'unauthorized-request' => 'Request rejected because the user is not authorized',
        ],
    ],

    'local' => [
        'disk' => 'public',
        'path' => 'public/wappify',
    ],

    'spatie' => [
        'disk' => 'public',
        'properties' => [],
        'collection' => 'whatsapp',
    ],
];
