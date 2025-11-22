<?php

session_start();

require "app/Config.php";
require "app/Router.php";
require "app/Database.php";
require "app/Page.php";


$router = new Router();

require "routes/web.php";
require "routes/api.php";

$router->dispatch();

?>