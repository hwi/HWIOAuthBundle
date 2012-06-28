<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Request;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * OAuthUtils
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthUtils
{
    private $container;
    private $ownerMap;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->ownerMap  = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));
    }

    /**
     * @return array
     */
    public function getResourceOwners()
    {
        $resourceOwners = $this->ownerMap->getResourceOwners();

        return array_keys($resourceOwners);
    }

    /**
     * @param string  $name
     * @param boolean $connect
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getAuthorizationUrl($name, $connect = false)
    {
        $resourceOwner = $this->getResourceOwner($name);
        $checkPath = $this->ownerMap->getResourceOwnerCheckPath($name);

        return $resourceOwner->getAuthorizationUrl(
            $connect
                ? $this->generateUrl('hwi_oauth_connect_service', array('service' => $name), true)
                : $this->generateUri($checkPath)
        );
    }

    /**
     * @param string $name
     *
     * @throws \RuntimeException
     */
    public function getLoginUrl($name)
    {
        $resourceOwner = $this->getResourceOwner($name);

        return $this->generateUrl('hwi_oauth_service_redirect', array('service' => $name));
    }

    /**
     * @param string $name
     *
     * @return ResourceOwnerInterface
     *
     * @throws \RuntimeException
     */
    private function getResourceOwner($name)
    {
        $resourceOwner = $this->ownerMap->getResourceOwnerByName($name);
        if (!$resourceOwner instanceof ResourceOwnerInterface) {
            throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
        }

        return $resourceOwner;
    }

    /**
     * Get the uri for a given path.
     *
     * @param string $path Path or route
     *
     * @return string
     */
    private function generateUri($path)
    {
        if (0 === strpos($path, 'http') || !$path) {
            return $path;
        }

        if ($path && '/' === $path[0]) {
            return $this->container->get('request')->getUriForPath($path);
        }

        return $this->generateUrl($path, true);
    }

    /**
     * @param string  $route
     * @param array   $params
     * @param boolean $absolute
     *
     * @return string
     */
    private function generateUrl($route, array $params = array(), $absolute = false)
    {
        return $this->container->get('router')->generate($route, $params, $absolute);
    }
}
