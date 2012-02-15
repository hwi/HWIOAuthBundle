<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\OAuth;

use Symfony\Component\HttpFoundation\Request;

/**
 * ResourceOwnerInterface
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResourceOwnerInterface
{
    /**
     * Retrieves the user's information from an access_token
     *
     * @param string $accessToken
     *
     * @return UserResponseInterface The wrapped response interface.
     */
    function getUserInformation($accessToken);

    /**
     * Returns the provider's authorization url
     *
     * @param mixed $redirectUri     The uri to redirect the client back to
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @return string The authorization url
     */
    function getAuthorizationUrl($redirectUri, array $extraParameters = array());

    /**
     * Retrieve an access token for a given code
     *
     * @param mixed $code            The code to use to retrieve the access token
     * @param mixed $redirectUri     The uri to redirect the client back to
     * @param array $extraParameters An array of parameters to add to the url
     * @return string The access token
     */
    function getAccessToken($code, $redirectUri, array $extraParameters = array());
}
