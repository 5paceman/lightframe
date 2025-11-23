<?php

namespace App;

enum PDOTYPE {
    case MYSQL;
    case POSTGRES;
}

class Config {

    public const domain = 'localhost';

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
        ]
    ];

    public const error_4xx_view = '';
}

?>