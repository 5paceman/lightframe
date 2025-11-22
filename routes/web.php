<?php

$router->get('/', function () {
    view('test');
}, []);

$router->get('/json-test', function () {
    $results = DB::get()->query()->table('test')->select()->get();
    json($results);
}, []);

?>