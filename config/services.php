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

    'ai_similarity' => [
        // Auto-switch to HuggingFace when an API key is present; override with AI_SIMILARITY_PROVIDER.
        'provider' => env('AI_SIMILARITY_PROVIDER', env('AI_SIMILARITY_API_KEY') ? 'huggingface-clip' : 'fake-image-embedding'),
        'model' => env('AI_SIMILARITY_MODEL', 'facebook/data2vec-vision-base'),
        'api_key' => env('AI_SIMILARITY_API_KEY'),
        'endpoint' => env('AI_SIMILARITY_ENDPOINT'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v22.0'),
        'marketplace_social_account_id' => env('MARKETPLACE_FACEBOOK_SOCIAL_ACCOUNT_ID'),
    ],

    'tiktok' => [
        'client_id' => env('TIKTOK_CLIENT_ID'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect' => env('TIKTOK_REDIRECT_URI'),
    ],

    'visual_search' => [
        'python' => env('VISUAL_SEARCH_PYTHON', 'python3'),
        'model' => env('VISUAL_SEARCH_MODEL', 'ViT-B-32'),
        'pretrained' => env('VISUAL_SEARCH_PRETRAINED', 'openai'),
        'device' => env('VISUAL_SEARCH_DEVICE', 'cpu'),
        'timeout' => env('VISUAL_SEARCH_TIMEOUT', 900),
    ],

];
