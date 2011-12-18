<?php

namespace Knp\OAuthBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuthToken extends AbstractToken
{
    private $accessToken;

    public function __construct($accessToken, array $roles = array())
    {
        $this->accessToken = $accessToken;

        parent::__construct($roles);
    }

    public function getCredentials()
    {
        return $this->accessToken;
    }

    public function getUsername()
    {
        return 'OAUTH_USER';
    }
}