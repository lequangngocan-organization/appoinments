<?php
return [
    'default_timezone' => env('APPT_TZ', 'Asia/Ho_Chi_Minh'),

    'google' => [
        'enabled'       => env('APPT_GOOGLE_ENABLED', true),
        'client_id'     => env('APPT_GOOGLE_CLIENT_ID'),
        'client_secret' => env('APPT_GOOGLE_CLIENT_SECRET'),
        'redirect_uri'  => env('APPT_GOOGLE_REDIRECT_URI'),
        'send_updates'  => env('APPT_GOOGLE_SEND_UPDATES', 'all'),
        'organizer_email' => env('APPT_ORGANIZER_EMAIL'),

        'scopes' => [
            'https://www.googleapis.com/auth/calendar.events',
            'openid',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ],
        'access_type' => 'offline',
        'prompt'      => 'consent',
    ],

    'mail' => [
        'enabled' => env('APPT_MAIL_ENABLED', true),
        'from_address' => env('APPT_MAIL_FROM', null),
        'from_name' => env('APPT_MAIL_FROM_NAME', null),

        'attach_ics' => false,        // NO ICS
        'fallback_add_link' => true,  // make "Add to Google Calendar" link if create failed
    ],

    'meet' => [
        'enabled' => env('APPT_GOOGLE_MEET', true), // enable generate link google meet
    ],
];
