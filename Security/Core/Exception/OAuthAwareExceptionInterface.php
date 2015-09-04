<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * OAuthAwareExceptionInterface.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
interface OAuthAwareExceptionInterface
{
    /**
     * Get the access token information.
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Get the raw version of received token.
     *
     * @return array
     */
    public function getRawToken();

    /**
     * Get the refresh token information.
     *
     * @return null|string
     */
    public function getRefreshToken();

    /**
     * Get the info when token will expire.
     *
     * @return null|int
     */
    public function getExpiresIn();

    /**
     * Get the oauth secret token.
     *
     * @return null|string
     */
    public function getTokenSecret();

    /**
     * Set the token.
     *
     * @param TokenInterface $token
     */
    public function setToken(TokenInterface $token);

    /**
     * Set the name of the resource owner responsible for the oauth authentication.
     *
     * @param string $resourceOwnerName
     */
    public function setResourceOwnerName($resourceOwnerName);

    /**
     * Get the name of resource owner.
     *
     * @return string
     */
    public function getResourceOwnerName();
}
