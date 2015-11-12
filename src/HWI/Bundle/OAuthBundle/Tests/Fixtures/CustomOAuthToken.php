<?php

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class CustomOAuthToken extends OAuthToken
{
    public function __construct()
    {
        parent::__construct(array(
            'access_token'      => 'access_token_data',
        ), array(
            'ROLE_USER',
        ));

        $this->setUser(new User());
    }
}
