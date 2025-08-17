<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for OTP (One-Time Password) functionality
    | including expiration times, rate limiting, and message templates.
    |
    */

    // OTP expiration time in minutes
    'expiration_minutes' => env('OTP_EXPIRATION_MINUTES', 10),

    // Rate limiting for sending OTP (minimum time between requests in minutes)
    'rate_limit_minutes' => env('OTP_RATE_LIMIT_MINUTES', 2),

    // Rate limiting for resending OTP (minimum time between resend requests in minutes)
    'resend_rate_limit_minutes' => env('OTP_RESEND_RATE_LIMIT_MINUTES', 2),

    // OTP code length
    'code_length' => env('OTP_CODE_LENGTH', 6),

    // Whether to enable WhatsApp OTP (requires WAMasbro service)
    'enable_whatsapp' => env('OTP_ENABLE_WHATSAPP', true),

    // Fallback to SMS if WhatsApp fails (requires SMS service configuration)
    'enable_sms_fallback' => env('OTP_ENABLE_SMS_FALLBACK', false),

    // Message template for WhatsApp OTP
    'whatsapp_message_template' => env('OTP_WHATSAPP_MESSAGE_TEMPLATE',
        "🔐 *Kode OTP Anda*\n\n" .
        "Kode OTP Anda adalah: *{code}*\n\n" .
        "Kode ini berlaku selama {expiration} menit.\n" .
        "Jangan bagikan kode ini kepada siapapun.\n\n" .
        "Jika Anda tidak meminta kode ini, abaikan pesan ini.\n\n" .
        "Salam,\nTim Sesuai Sunnah"
    ),

    // Message template for SMS OTP (if fallback is enabled)
    'sms_message_template' => env('OTP_SMS_MESSAGE_TEMPLATE',
        "Kode OTP Anda adalah: {code}. Berlaku {expiration} menit. Jangan bagikan kode ini."
    ),

    // Maximum OTP attempts per phone number per day
    'max_daily_attempts' => env('OTP_MAX_DAILY_ATTEMPTS', 10),

    // Whether to log OTP activities for security monitoring
    'log_activities' => env('OTP_LOG_ACTIVITIES', true),

    // Phone number validation regex pattern
    'phone_regex' => env('OTP_PHONE_REGEX', '/^[0-9+\-\s()]+$/'),

    // Minimum phone number length
    'min_phone_length' => env('OTP_MIN_PHONE_LENGTH', 10),

    // Maximum phone number length
    'max_phone_length' => env('OTP_MAX_PHONE_LENGTH', 20),
];
