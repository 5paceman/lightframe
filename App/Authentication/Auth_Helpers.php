<?php

namespace App\Authentication;

use App\Config;
use App\Database\Database;
use App\Response;
use Models\User;

/**
 * Middleware function for checking if the user is authentication and if not redirect to the configured login page
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
    $session = Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['session_id'])->first();
    return User::find($session['user_id']);
}

?>