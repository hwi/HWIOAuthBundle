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
    public function getUserInformation($accessToken);

    /**
     * Returns the provider's authorization url
     *
     * @param mixed $redirectUri     The uri to redirect the client back to
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @return string The authorization url
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array());

    /**
     * Retrieve an access token for a given code
     *
     * @param Request $request         The request object where is going to extract the code from
     * @param mixed   $redirectUri     The uri to redirect the client back to
     * @param array   $extraParameters An array of parameters to add to the url
     *
     * @return string The access token
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array());

    /**
     * Return a name for the resource owner.
     *
     * @return string
     */
    public function getName();

    /**
     * Checks whether the class can handle the request.
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function handles(Request $request);

    /**
     * Sets a name for the resource owner.
     */
    public function setName($name);
}
