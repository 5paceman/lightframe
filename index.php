<?php

session_start();

require "app/Config.php";
require "app/Router.php";
require "app/database/Database.php";
require "app/Response.php";


$router = new Router();

require "routes/web.php";
require "routes/api.php";

$router->dispatch();

?>