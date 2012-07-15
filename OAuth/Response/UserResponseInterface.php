<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\ResponseInterface;

/**
 * UserResponseInterface
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface UserResponseInterface extends ResponseInterface
{
    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get the name to display.
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Get the access token used for the request.
     *
     * @return mixed
     */
    public function getAccessToken();

    /**
     * Set the access token used for the request.
     *
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken);
}
