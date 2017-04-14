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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * ResourceOwnerMap. Holds several resource owners for a firewall. Lazy
 * loads the appropriate resource owner when requested.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ResourceOwnerMap implements ContainerAwareInterface, ResourceOwnerMapInterface
{
    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var array
     */
    protected $resourceOwners;

    /**
     * @var array
     */
    protected $possibleResourceOwners;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param HttpUtils $httpUtils              HttpUtils
     * @param array     $possibleResourceOwners array with possible resource owners names
     * @param array     $resourceOwners         array with configured resource owners
     */
    public function __construct(HttpUtils $httpUtils, array $possibleResourceOwners, $resourceOwners)
    {
        $this->httpUtils = $httpUtils;
        $this->possibleResourceOwners = $possibleResourceOwners;
        $this->resourceOwners = $resourceOwners;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function hasResourceOwnerByName($name)
    {
        return isset($this->resourceOwners[$name], $this->possibleResourceOwners[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerByName($name)
    {
        if (!$this->hasResourceOwnerByName($name)) {
            return null;
        }

        return $this->container->get('hwi_oauth.resource_owner.'.$name);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerByRequest(Request $request)
    {
        foreach ($this->resourceOwners as $name => $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return array($this->getResourceOwnerByName($name), $checkPath);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerCheckPath($name)
    {
        if (isset($this->resourceOwners[$name])) {
            return $this->resourceOwners[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwners()
    {
        return $this->resourceOwners;
    }
}
