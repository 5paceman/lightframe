<?php

namespace App\Authentication;

use App\Database\Database;
use Exception;
use Models\User;

class Authenticate {

    protected static function createLoginSession($userId)
    {
        $session_id = bin2hex(random_bytes(32));
        $result = Database::get()->query()->table('sessions')->insert([
            'session_id' => $session_id,
            'user_id' => $userId
        ]);

        if(!$result)
        {
            throw new \Exception("Unable to create login session");
        }

        $_SESSION['auth_session'] = $session_id;
        session_regenerate_id();
    }

    public static function login(string $email, string $password)
    {
        $data = User::where('email', '=', $email)->first();
        if(!$data)
        {
            throw new \Exception("User doesnt exist");
        }
        $user = new User($data);
        if($user->password === null || !password_verify($password, $user->password))
        {
            throw new \Exception("Password invalid.");
        }

        self::createLoginSession($user->id);
    }

    /**
     * @param array $oauthData Expects an array structered as below
     * return [
     *       'provider' => 'google',
     *       'provider_id' => $user->getId(),
     *       'email' => $user->getEmail(),
     *       'name' => $user->getName()
     *   ];
     * 
     * @return array
     */
    public static function oauthLogin(array $oauthData)
    {
        $knownProviderLink = Database::get()->query()->table('user_providers')->where('provider_id', '=', $oauthData['provider_id'])->first();
        if($knownProviderLink)
        {
            self::createLoginSession($knownProviderLink['user_id']);
        } else {
            $user = User::where('email', '=', $oauthData['email'])->first();
            if(!$user)
            {
                $user = User::create([
                    'email' => $oauthData['email'],
                    'password' => null,
                ]);

                if(!$user)
                    throw new Exception("Unable to create user");

                $linkResult = Database::get()->query()->table('user_providers')->insert([
                    'provider' => $oauthData['provider'],
                    'provider_id' => $oauthData['provider_id'],
                    'user_id' => $user['id'],
                ]);

                if(!$linkResult)
                    throw new Exception("Unable to link session");

            }

            self::createLoginSession($user['id']);
        }
    }

    public static function register(string $email, string $password)
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
        if(isset($_SESSION['auth_session']))
        {
            Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['auth_session'])->delete();
        }

        session_destroy();
    }

    public static function authed(): bool
    {
        return isset($_SESSION['auth_session']) && Database::get()->query()->table('sessions')->where('session_id', '=', $_SESSION['auth_session'])->first();
    }
}

?>