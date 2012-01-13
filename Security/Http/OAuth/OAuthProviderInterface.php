<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Http\OAuth;

use Symfony\Component\HttpFoundation\Request;

/**
 * OAuthProviderInterface
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
interface OAuthProviderInterface
{
    /**
     * Retrieves the user's username from an access_token
     *
     * @param string $accessToken
     * @return string The username
     */
    function getUsername($accessToken);

    /**
     * Returns the provider's authorization url
     *
     * @param string $loginCheckUrl You should set that as your redirect_uri
     * @param array $extraParameters An array of parameters to add to the url
     * @return string The authorization url
     */
    function getAuthorizationUrl(Request $request, array $extraParameters = array());

    /**
     * Retrieve an access token for a given code
     *
     * @param string $code The code
     * @param array $extraParameters An array of parameters to add to the url
     * @return string The access token
     */
    function getAccessToken(Request $request, array $extraParameters = array());
}