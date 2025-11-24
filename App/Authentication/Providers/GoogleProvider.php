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
            'redirectUri' => Config::domain.Config::authentication['providers']['redirect_path']
        ]);
    }

    public function getAuthUrl(): string
    {
        $url = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        $_SESSION['oauthProvider'] = 'google';
        return $url;
    }

    public function getUserData(string $authCode): array
    {
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