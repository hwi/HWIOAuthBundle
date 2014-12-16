<?php

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

/**
 * Builds an OAuth Token.
 */
class OAuthTokenFactory implements OAuthTokenFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function build($accessToken, array $roles = array())
    {
        return new OAuthToken($accessToken, $roles);
    }
}
