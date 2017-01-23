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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResponseInterface
{
    /**
     * Get the api response data.
     *
     * @return array
     */
    public function getData();

    /**
     * Set the raw api response.
     *
     * @param string|array $data
     *
     * @throws AuthenticationException
     */
    public function setData($data);

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
