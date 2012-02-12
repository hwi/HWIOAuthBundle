<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Http\OAuth\Response;

/**
 * UserResponseInterface
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface UserResponseInterface
{
    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Set the api response.
     *
     * @return mixed
     */
    public function getResponse();

    /**
     * Set the raw api response.
     *
     * @param string $response
     */
    public function setResponse($response);
}
