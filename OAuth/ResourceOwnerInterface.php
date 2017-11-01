<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * ResourceOwnerInterface.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResourceOwnerInterface
{
    /**
     * Retrieves the user's information from an access_token.
     *
     * @param array $accessToken     The access token
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @throws \HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException
     *
     * @return UserResponseInterface the wrapped response interface
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array());

    /**
     * Returns the provider's authorization url.
     *
     * @param string $redirectUri     The uri to redirect the client back to
     * @param array  $extraParameters An array of parameters to add to the url
     *
     * @return string The authorization url
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array());

    /**
     * Retrieve an access token for a given code.
     *
     * @param HttpRequest $request         The request object where is going to extract the code from
     * @param string      $redirectUri     The uri to redirect the client back to
     * @param array       $extraParameters An array of parameters to add to the url
     *
     * @throws \HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException
     *
     * @return array The access token
     */
    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = array());

    /**
     * Check whatever CSRF token from request is valid or not.
     *
     * @param string $csrfToken
     *
     * @return bool True if CSRF token is valid
     *
     * @throws AuthenticationException When token is not valid
     */
    public function isCsrfTokenValid($csrfToken);

    /**
     * Return a name for the resource owner.
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieve an option by name.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws \InvalidArgumentException When the option does not exist
     */
    public function getOption($name);

    /**
     * Checks whether the class can handle the request.
     *
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function handles(HttpRequest $request);

    /**
     * Sets a name for the resource owner.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Add extra paths to the configuration.
     *
     * @param array $paths
     */
    public function addPaths(array $paths);

    /**
     * @param string $refreshToken    Refresh token
     * @param array  $extraParameters An array of parameters to add to the url
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = []);
}
