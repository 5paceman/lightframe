<?php

namespace App\Authentication;

use App\Config;
use App\Database\Database;
use App\Response;
use Models\User;

function authenticated()
{
    if(!isset($_SESSION['session_id']))
    {
        Response::redirect(Config::authentication['login_path']);
    }

    $session = Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['session_id'])->first();

    if(!$session)
    {
        $_SESSION['session_id'] = null;
        Response::redirect(Config::authentication['login_path']);
    }
}

function user()
{
    $session = Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['session_id'])->first();
    return User::find($session['user_id']);
}

?>