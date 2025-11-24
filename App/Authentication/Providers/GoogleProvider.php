<?php

namespace App\Authentication\Providers;

use Exception;
use League\OAuth2\Client\Provider\Google;

use App\Config;
use League\OAuth2\Client\Provider\GoogleUser;

class GoogleProvider implements OAuthProvider{

    protected Google $provider;

    public function __construct() {
        $this->provider = new Google([
            'clientId' => Config::authentication['providers']['google']['clientId'],
            'clientSecret' => Config::authentication['providers']['google']['clientSecret'],
            'redirectUri' => Config::authentication['providers']['google']['redirectUri'],
            'hostedDomain' => Config::domain,
        ]);
    }

    public function getAuthUrl(): string
    {
        $_SESSION['oauth2state'] = $this->provider->getState();
        return $this->provider->getAuthorizationUrl();
    }

    public function getUserData(string $authCode): array
    {
        if(empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state']))
            throw new Exception("Invalid state.");
        
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $authCode
        ]);

        $userData = $this->provider->getResourceOwner($accessToken);

        return [
            'provider' => 'google',
            'provider_id' => $userData->getId(),
            'email' => $userData->getEmail(),
            'name' => $userData->getName()
        ];
    }

}

?>