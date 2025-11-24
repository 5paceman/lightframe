<?php

use App\Authentication\Authenticate;
use App\Authentication\Providers\GoogleProvider;
use App\Database\Database;
use App\Response;
use function App\Authentication\redirectToAuthProvider;
use function App\Authentication\registerOAuthCallback;

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

$router->get('/google-login', function() {
    redirectToAuthProvider(new GoogleProvider());
});

registerOAuthCallback($router);

?>