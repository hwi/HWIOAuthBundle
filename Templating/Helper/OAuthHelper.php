<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Templating\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Templating\Helper\Helper;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * OAuthHelper
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OAuthHelper extends Helper
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
    public function getLoginUrl($name, $connect = false)
    {
        $resourceOwner = $this->ownerMap->getResourceOwnerByName($name);
        if (!$resourceOwner instanceof ResourceOwnerInterface) {
            throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
        }

        $checkPath = $this->ownerMap->getResourceOwnerCheckPath($name);

        return $this->getAuthorizationUrl($resourceOwner, $name, $checkPath, $connect);
    }

    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $name
     * @param string                 $checkPath
     * @param boolean                $connect
     *
     * @return mixed
     */
    private function getAuthorizationUrl(ResourceOwnerInterface $resourceOwner, $name, $checkPath, $connect)
    {
        return $resourceOwner->getAuthorizationUrl(
            $connect
                ? $this->generateUrl('hwi_oauth_connect_service', array('service' => $name), true)
                : $this->generateUri($checkPath)
        );
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

    /**
     * Returns the name of the helper.
     *
     * @return string The helper name
     */
    public function getName()
    {
        return 'hwi_oauth';
    }
}
