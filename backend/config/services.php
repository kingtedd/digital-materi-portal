<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'allowed_domains' => explode(',', env('GOOGLE_ALLOWED_DOMAINS', '')),

        // Google API Credentials
        'service_account_credentials' => env('GOOGLE_SERVICE_ACCOUNT_CREDENTIALS'),

        // Google Drive Configuration
        'drive_folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),

        // Google Sheets Configuration
        'sheets_catalog_materi_id' => env('GOOGLE_SHEETS_CATALOG_MATERI_ID'),
        'sheets_catalog_digital_id' => env('GOOGLE_SHEETS_CATALOG_DIGITAL_ID'),
        'sheets_catalog_classroom_id' => env('GOOGLE_SHEETS_CATALOG_CLASSROOM_ID'),
        'sheets_schedule_automation_id' => env('GOOGLE_SHEETS_SCHEDULE_AUTOMATION_ID'),

        // Google Gemini API
        'gemini_api_key' => env('GEMINI_API_KEY'),

        // Google Classroom API
        'classroom_credentials' => env('GOOGLE_CLASSROOM_CREDENTIALS'),
    ],

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'api_token' => env('N8N_API_TOKEN'),
        'timeout' => env('N8N_TIMEOUT', 300), // 5 minutes default
        'retry_attempts' => env('N8N_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('N8N_RETRY_DELAY', 1000), // milliseconds
    ],

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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];