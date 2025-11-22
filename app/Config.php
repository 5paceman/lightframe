<?php

enum PDOTYPE {
    case MYSQL;
    case POSTGRES;
}

class Config {
    public const database = [
        'db' => 'lightframe',
        'host' => 'localhost',
        'port' => 5432,
        'pdo' => PDOTYPE::POSTGRES,
        'username' => 'phpuser',
        'password' => 'php secret login',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ];
}

?>