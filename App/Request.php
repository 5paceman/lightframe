<?php

use App\Validator;

class Request {

    public static function verifyCSRF(): bool 
    {
        assert($_SERVER['REQUEST_METHOD'] !== 'POST', "Verifying CSRF should only be via POST, GET should be idempotent.");

        $verified = $_POST['csrf-token'] === $_SESSION['csrf-token'];
        unset($_SESSION['csrf-token']);
        return $verified;
    }

    public static function getVal(string $key, $default = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method === 'POST')
        {
            return $_POST[$key] ?? $default;
        } else {
            return $_GET[$key] ?? $default;
        }
    }

    public static function getAll()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method === 'POST')
        {
            return $_POST;
        } else {
            return $_GET;
        }
    }

    public static function validate(array $rules)
    {
        Validator::validate(self::getAll(), $rules);
    }

}

?>