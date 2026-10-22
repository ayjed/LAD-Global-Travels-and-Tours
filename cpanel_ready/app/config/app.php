<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'LAD Global Travels and Tours'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => (bool)Env::get('APP_DEBUG', false),
    'url'      => Env::get('APP_URL', 'http://localhost:8000'),

    'company'  => [
        'phone'    => Env::get('COMPANY_PHONE', '07017597650'),
        'whatsapp' => Env::get('COMPANY_WHATSAPP', 'https://wa.me/2347017597650'),
        'email'    => Env::get('COMPANY_EMAIL', 'info@ladglobaltravel.com'),
        'address'  => Env::get('COMPANY_ADDRESS', 'SF41, Ikorodu Complex, Ita Elewa, adjacent to High Court, Ikorodu, Lagos, Nigeria'),
    ],

    'session'  => [
        'lifetime' => (int)Env::get('SESSION_LIFETIME', 7200),
        'secure'   => (bool)Env::get('SESSION_SECURE_COOKIE', false),
    ],
];
