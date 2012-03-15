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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * ResponseInterface
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResponseInterface
{
    /**
     * Get the api response.
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

    /**
     * Get the resource owner responsible for the response.
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner();

    /**
     * Set the resource owner for the response.
     *
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner);
}
