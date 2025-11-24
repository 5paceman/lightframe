<?php

namespace App\Authentication;

use App\Database\Database;
use App\Response;
use Models\User;

class Authenticate {
    public static function login($email, $password)
    {
        $user = User::where('email', '=', $email);
        if(!$user)
        {
            throw new \Exception("User doesnt exist");
        }

        if(!password_verify($password, $user->password))
        {
            throw new \Exception("Password invalid.");
        }

        session_regenerate_id(true);
        $session_id = bin2hex(random_bytes(32));
        $result = Database::get()->query()->table('sessions')->insert([
            'session_id' => $session_id,
            'user_id' => $user->id
        ]);

        if(!$result)
        {
            throw new \Exception("Unable to create login session");
        }

        $_SESSION['session_id'] = $session_id;
    }

    public static function register($email, $password)
    {
        $user = User::where('email', '=', $email)->first();
        if($user)
        {
            throw new \Exception("User already exists");
        }

        $result = new User([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ])->save();

        return $result;
    }

    public static function logout()
    {
        if(isset($_SESSION['session_id']))
        {
            Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['session_id'])->delete();
        }

        session_destroy();
    }

    public static function authed(): bool
    {
        return isset($_SESSION['session_id']) && Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['session_id'])->first();
    }
}

?>