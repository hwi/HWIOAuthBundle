<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\ResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 *
 * @method string getUserIdentifier() Get the unique user identifier.
 */
interface UserResponseInterface extends ResponseInterface
{
    /**
     * Get the unique user identifier.
     *
     * Note that this is not always common known "username" because of implementation
     * in Symfony framework. For more details follow link below.
     *
     * @see https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Security/Core/User/UserProviderInterface.php#L20-L28
     *
     * @return string|null
     *
     * @deprecated Please use getUserIdentifier
     */
    public function getUsername();

    /**
     * Get the username to display.
     *
     * @return string
     */
    public function getNickname();

    /**
     * Get the first name of user.
     *
     * @return string|null
     */
    public function getFirstName();

    /**
     * Get the last name of user.
     *
     * @return string|null
     */
    public function getLastName();

    /**
     * Get the real name of user.
     *
     * @return string|null
     */
    public function getRealName();

    /**
     * Get the email address.
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Get the url to the profile picture.
     *
     * @return string|null
     */
    public function getProfilePicture();

    /**
     * Get the access token used for the request.
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Get the access token used for the request.
     *
     * @return string|null
     */
    public function getRefreshToken();

    /**
     * Get oauth token secret used for the request.
     *
     * @return string|null
     */
    public function getTokenSecret();

    /**
     * Get the info when token will expire.
     *
     * @return int|null
     */
    public function getExpiresIn();

    /**
     * Set the raw token data from the request.
     */
    public function setOAuthToken(OAuthToken $token);

    /**
     * Get the raw token data from the request.
     *
     * @return OAuthToken
     */
    public function getOAuthToken();
}
