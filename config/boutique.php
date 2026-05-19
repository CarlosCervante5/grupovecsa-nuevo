<?php

return [

    /*
    | Correos transaccionales boutique (pedido creado, pago confirmado).
    | Requiere RESEND_KEY y mailer "resend" en config/mail.php.
    */
    'mail_mailer' => env('BOUTIQUE_MAIL_MAILER', 'resend'),

    'mail_from' => [
        'address' => env('BOUTIQUE_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'name' => env('BOUTIQUE_MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Grupo VECSA Boutique')),
    ],

];
