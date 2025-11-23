<?php

namespace App\Authentication;

use App\Database\Database;
use \Models\User;

class Authenticate {
    public static function login($email, $password)
    {
        $user = User::where('email', '=', $email)->first();
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
        Database::get()->query()->table('sessions')->insert([
            'session_id' => $session_id,
            'user_id' => $user->id
        ]);

        $_SESSION['session_id'] = $session_id;
    }

    public static function register($email, $password)
    {
        $user = User::where('email', '=', $email)->first();
        if($user)
        {
            throw new \Exception("User already exists");
        }

        $user = new User([
            'email' => $email,
            'password' => password_hash($password)
        ])->save();
    }
}

?>