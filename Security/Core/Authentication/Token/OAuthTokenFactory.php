<?php

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

/**
 * Builds an OAuth Token.
 */
class OAuthTokenFactory
{
    /**
     * @param string|array $accessToken The OAuth access token
     * @param array        $roles       Roles for the token
     *
     * @return OAuthToken|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    public function build($accessToken, array $roles = array())
    {
        return new OAuthToken($accessToken, $roles);
    }
}
