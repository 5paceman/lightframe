<?php

require 'QueryBuilder.php';

class DB {
    
    protected ?PDO $pdo = null;

    protected static ?DB $_instance = null;

    public function __construct() {}

    public static function get()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new DB();
        }

        return self::$_instance;
    }

    public function connect()
    {
        if(!isset(Config::database['db'], Config::database['host'], Config::database['port'], Config::database['username'], Config::database['password'], Config::database['pdo']))
        {
            throw new Exception(message: "Config undefined for database");
        }
        
        switch(Config::database['pdo'])
        {
            case PDOTYPE::MYSQL:
                $dsn = "mysql:";
                break;
            case PDOTYPE::POSTGRES:
                $dsn = "pgsql:";
                break;
            default:
                throw new Exception("Unsupported driver");
        }
        
        $dsn .= "host=".Config::database['host'].";port=".Config::database['port'].";dbname=".Config::database['db'];
        $this->pdo = new PDO($dsn, Config::database['username'], Config::database['password'], Config::database['options']);
        return $this->pdo;
    }

    public function query()
    {
        $this->connect();
        return new QueryBuilder($this->pdo);
    }
}


?>