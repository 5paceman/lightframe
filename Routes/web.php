<?php

use App\Database\Database;
use App\Response;

$router->get('/', function () {
    Response::view('test');
}, []);

$router->get('/json-test', function () {
    $results = Database::get()->query()->table('test')->select()->get();
    Response::json($results);
}, []);

?>