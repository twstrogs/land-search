<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    */

    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),
    ],

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
        'timeout' => env('OLLAMA_TIMEOUT', 120),
        'cloud_mode' => env('OLLAMA_CLOUD_MODE', false),
        'api_key' => env('OLLAMA_API_KEY', ''),
        'available_models' => [
            'llama3.2',
            'llama3.1',
            'mistral',
            'gemma2',
            'phi3',
        ],
        'cloud_models' => [
            'gpt-oss:120b-cloud',
            'llama3.2',
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'timeout' => env('GEMINI_TIMEOUT', 60),
        'available_models' => [
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-pro',
        ],
    ],

    'apify' => [
        'api_token' => env('APIFY_API_TOKEN', ''),
        'timeout' => env('APIFY_TIMEOUT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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

];
