<?php

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

/**
 * Interface for OAuthTokenFactory.
 */
interface OAuthTokenFactoryInterface
{
    /**
     * @param string|array $accessToken The OAuth access token
     * @param array        $roles       Roles for the token
     *
     * @return \HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken
     */
    public function build($accessToken, array $roles = array());
}
