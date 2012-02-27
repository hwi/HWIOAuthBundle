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

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param HttpUtils          $httpUtils
     */
    public function __construct(ContainerInterface $container, HttpUtils $httpUtils)
    {
        $this->container = $container;
        $this->httpUtils = $httpUtils;
    }

    /**
     * Add a resource owner to the map.
     *
     * @param string $resourceOwnerId
     * @param array  $configuration
     */
    public function addResourceOwner($resourceOwnerId, array $configuration)
    {
        $configuration['service'] = $resourceOwnerId;
        $this->resourceOwners[$configuration['service']] = $configuration;
    }

    /**
     * Gets the appropriate resource owner given the id.
     *
     * @param string $id
     *
     * @return null|HWI\Bundle\OAuthBundle\OAuthResourceOwnerInterface
     */
    public function getResourceOwnerById($id)
    {
        return isset($this->resourceOwners[$id]) ? $this->container->get($id) : null;
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
        foreach ($this->resourceOwners as $resourceOwner) {
            if ($this->httpUtils->checkRequestPath($request, $resourceOwner['check_path'])) {
                return array($this->container->get($resourceOwner['service']), $resourceOwner['check_path'], $resourceOwner['service']);
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
