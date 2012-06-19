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

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Http\HttpUtils;

/**
 * ResourceOwnerMap. Holds several resource owners for a firewall. Lazy
 * loads the appropriate resource owner when requested.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ResourceOwnerMap
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var array
     */
    protected $resourceOwners;

    protected $possibleResourceOwners;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container              Container used to lazy load the resource owners.
     * @param HttpUtils          $httpUtils              HttpUtils
     * @param array              $possibleResourceOwners Array with possible resource owners names.
     * @param array              $resourceOwners         Array with configured resource owners.
     */
    public function __construct(ContainerInterface $container, HttpUtils $httpUtils, array $possibleResourceOwners, $resourceOwners)
    {
        $this->container              = $container;
        $this->httpUtils              = $httpUtils;
        $this->possibleResourceOwners = $possibleResourceOwners;
        $this->resourceOwners         = $resourceOwners;
    }

    /**
     * Gets the appropriate resource owner given the name.
     *
     * @param string $id
     *
     * @return null|\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    public function getResourceOwnerByName($name)
    {
        if (!isset($this->resourceOwners[$name])) {
            return null;
        }
        if (!in_array($name, $this->possibleResourceOwners)) {
            return null;
        }

        $service = $this->container->get('hwi_oauth.resource_owner.'.$name);

        return $service;
    }

    /**
     * Gets the appropriate resource owner for a request.
     *
     * @param Request $request
     *
     * @return null|array
     */
    public function getResourceOwnerByRequest(Request $request)
    {
        foreach ($this->resourceOwners as $name => $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return array($this->getResourceOwnerByName($name), $checkPath);
            }
        }
    }

    /**
     * Get all the resource owners.
     *
     * @return array
     */
    public function getResourceOwners()
    {
        return $this->resourceOwners;
    }
}
