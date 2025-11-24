<?php

namespace App;

use App\Authentication\Providers\GoogleProvider;

enum PDOTYPE {
    case MYSQL;
    case POSTGRES;
}

class Config {

    public const domain = 'http://localhost:8000';

    public const database = [
        'db' => 'lightframe',
        'host' => 'localhost',
        'port' => 5432,
        'pdo' => PDOTYPE::POSTGRES,
        'username' => 'phpuser',
        'password' => 'php secret login',
        'options' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    ];

    public const authentication = [
        'login_path' => '/login',
        'session' => [
            'lifetime' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ],
        'providers' => [
            'redirect_path' => '/oauth-callback',
            'google' => [
                'provider_class' => GoogleProvider::class,
                'clientId' => '',
                'clientSecret' => ''
            ]
        ]
    ];

    public const error_4xx_view = '';
}

?>