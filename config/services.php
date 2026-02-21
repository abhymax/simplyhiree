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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'aisensy' => [
        'enabled' => env('AISENSY_ENABLED', false),
        'endpoint' => env('AISENSY_ENDPOINT', 'https://backend.aisensy.com/campaign/t1/api/v2'),
        'api_key' => env('AISENSY_API_KEY'),
        'default_template' => env('AISENSY_TEMPLATE_DEFAULT'),
        'superadmin_phones' => env('AISENSY_SUPERADMIN_PHONES', ''),
        'templates' => [
            'auth.forgot_password' => env('AISENSY_TEMPLATE_FORGOT_PASSWORD'),
            'candidate.interview_scheduled' => env('AISENSY_TEMPLATE_CANDIDATE_INTERVIEW_SCHEDULED'),
            'candidate.selected' => env('AISENSY_TEMPLATE_CANDIDATE_SELECTED'),
            'profile.approved.partner' => env('AISENSY_TEMPLATE_PARTNER_APPROVED'),
            'profile.approved.client' => env('AISENSY_TEMPLATE_CLIENT_APPROVED'),
            'partner.daily_pulse' => env('AISENSY_TEMPLATE_PARTNER_DAILY_PULSE'),
            'billing.period_hit' => env('AISENSY_TEMPLATE_BILLING_PERIOD_HIT'),
        ],
    ],

];
