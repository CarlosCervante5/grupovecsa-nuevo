<?php

return [
    'google_sheet' => [
        'default_url' => env('BOUTIQUE_GOOGLE_SHEET_URL', ''),
        'default_gid' => env('BOUTIQUE_GOOGLE_SHEET_GID', '0'),
    ],

    'mail_mailer' => env('BOUTIQUE_MAIL_MAILER', env('MAIL_TRANSACTIONAL_MAILER', 'resend')),

    'mail_from' => [
        'address' => env('BOUTIQUE_MAIL_FROM_ADDRESS', env('MAIL_TRANSACTIONAL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS'))),
        'name' => env('BOUTIQUE_MAIL_FROM_NAME', env('MAIL_TRANSACTIONAL_FROM_NAME', env('MAIL_FROM_NAME', 'Grupo VECSA Boutique'))),
    ],
];
