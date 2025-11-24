<?php

use App\Authentication\Authenticate;
use App\Database\Database;
use App\Response;

$router->get('/', function () {
    Response::view('test');
});

$router->post('/login', function() {
    Authenticate::login($_POST['email'], $_POST['password']);
    Response::redirect('/');
});

$router->post('/register', function() {
    Authenticate::register($_POST['email'], $_POST['password']);
    Response::redirect('/');
});

$router->get('/logout', function() {
    Authenticate::logout();
    Response::redirect('/');
});

?>