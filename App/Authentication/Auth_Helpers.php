<?php

namespace App\Authentication;

use App\Authentication\Providers\OAuthProvider;
use App\Config;
use App\Database\Database;
use App\Response;
use App\Router;
use Models\User;

/**
 * Middleware function for checking if the user is authenticated and if not redirect to the configured login page
 * @see Config::authentication[login_path]
 * @return void
 */
function authenticated()
{
    if(Authenticate::authed())
        return;

    $_SESSION['session_id'] = null;
    Response::redirect(Config::authentication['login_path']);
}

/**
 * Gets the current logged in user
 * @return Models\User|null
 */
function user()
{
    $session = Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['auth_session'])->first();
    return User::find($session['user_id']);
}

function redirectToAuthProvider(OAuthProvider $provider)
{
    $url = $provider->getAuthUrl();
    header("Location: $url");
    exit;
}

function registerOAuthCallback(Router $router)
{
    $router->get(Config::authentication['providers']['redirect_path'], function() {
        if(empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state']))
            throw new \Exception("Invalid state.");

        if(empty($_GET['code']))
            throw new \Exception("Invalid code");

        if(empty($_SESSION['oauthProvider']) || !isset(Config::authentication['providers'][$_SESSION['oauthProvider']]))
            throw new \Exception("Unknown provider");


        try {
            $provider = new (Config::authentication['providers'][$_SESSION['oauthProvider']]['provider_class'])();
            $data = $provider->getUserData($_GET['code']);
            Authenticate::oauthLogin($data);
            Response::redirect('/');
        } catch(\Exception $e)
        {
            echo 'Unable to log you in. :(';
        }
    });
}

?>