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

    /*
    |--------------------------------------------------------------------------
    | External PIX API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração para integração com APIs PIX externas de outros websites.
    | Você pode configurar múltiplos provedores aqui.
    |
    | Exemplo de uso:
    |   $pixService = ExternalPixService::fromConfig('provedor1');
    |
    */

    'external_pix' => [
        'default' => [
            'api_url' => env('EXTERNAL_PIX_API_URL', 'https://api.exemplo.com'),
            'api_token' => env('EXTERNAL_PIX_API_TOKEN', ''),
            'auth_type' => env('EXTERNAL_PIX_AUTH_TYPE', 'bearer'), // 'bearer', 'basic', 'header'
            'token_header' => env('EXTERNAL_PIX_TOKEN_HEADER', 'Authorization'), // Para auth_type = 'header'
            'api_secret' => env('EXTERNAL_PIX_API_SECRET', null), // Para Basic Auth
            'timeout' => env('EXTERNAL_PIX_TIMEOUT', 30),
            'verify_ssl' => env('EXTERNAL_PIX_VERIFY_SSL', true),
        ],

        // Exemplo de configuração para outro provedor
        // 'provedor2' => [
        //     'api_url' => env('PROVEDOR2_PIX_API_URL', 'https://api.provedor2.com'),
        //     'api_token' => env('PROVEDOR2_PIX_API_TOKEN', ''),
        //     'auth_type' => 'bearer',
        //     'timeout' => 30,
        //     'verify_ssl' => true,
        // ],
    ],

];
