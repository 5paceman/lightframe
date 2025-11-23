<?php

require __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\Config;

session_set_cookie_params([
    'lifetime' => Config::authentication['session']['lifetime'],
    'path' => Config::authentication['session']['path'],
    'domain' => Config::domain,
    'secure' => Config::authentication['session']['secure'],
    'httponly' => Config::authentication['session']['httponly'],
    'samesite' => Config::authentication['session']['samesite']
]);

session_start();


$router = new Router();

require "Routes/web.php";
require "Routes/api.php";

$router->dispatch();

?>