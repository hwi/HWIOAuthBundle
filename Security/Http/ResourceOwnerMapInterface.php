<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResourceOwnerMapInterface
{
    /**
     * Check that resource owner with given name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasResourceOwnerByName($name);

    /**
     * Gets the appropriate resource owner given the name.
     *
     * @param string $name
     *
     * @return null|ResourceOwnerInterface
     */
    public function getResourceOwnerByName($name);

    /**
     * Gets the appropriate resource owner for a request.
     *
     * @param Request $request
     *
     * @return null|array
     */
    public function getResourceOwnerByRequest(Request $request);

    /**
     * Gets the check path for given resource name.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getResourceOwnerCheckPath($name);

    /**
     * Get all the resource owners.
     *
     * @return ResourceOwnerInterface[]
     */
    public function getResourceOwners();
}
