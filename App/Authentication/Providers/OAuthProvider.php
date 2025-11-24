<?php

namespace App\Authentication\Providers;

interface OAuthProvider {
    
    /**
     * The providers url to redirect the user to authenticate
     * @return string
     */
    public function getAuthUrl(): string;

    /**
     * Expects an array structered as below
     * return [
     *       'provider' => 'google',
     *       'provider_id' => $user->getId(),
     *       'email' => $user->getEmail(),
     *       'name' => $user->getName()
     *   ];
     * @param string $authCode
     * @return array
     */
    public function getUserData(string $authCode): array;
}

?>