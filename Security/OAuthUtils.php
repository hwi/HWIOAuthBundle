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
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class OAuthUtils
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap
     */
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
     *
     * @return string
     */
    public function getAuthorizationUrl($name)
    {
        $hasUser = $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $connect = $this->container->getParameter('hwi_oauth.connect');

        $resourceOwner = $this->getResourceOwner($name);
        $checkPath = $this->ownerMap->getResourceOwnerCheckPath($name);

        return $resourceOwner->getAuthorizationUrl(
            $connect && $hasUser
                ? $this->generateUrl('hwi_oauth_connect_service', array('service' => $name), true)
                : $this->generateUri($checkPath)
        );
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getLoginUrl($name)
    {
        // Just to check that this resource owner exists
        $this->getResourceOwner($name);

        return $this->generateUrl('hwi_oauth_service_redirect', array('service' => $name));
    }

    /**
     * Sign the request parameters
     *
     * @param string $method       Request method
     * @param string $url          Request url
     * @param array  $parameters   Parameters for the request
     * @param string $clientSecret Client secret to use as key part of signing
     * @param string $tokenSecret  Optional token secret to use with signing
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function signRequest($method, $url, $parameters, $clientSecret, $tokenSecret = '')
    {
        // Validate required parameters
        foreach (array('oauth_consumer_key', 'oauth_timestamp', 'oauth_nonce', 'oauth_version', 'oauth_signature_method') as $parameter) {
            if (!isset($parameters[$parameter])) {
                throw new \RuntimeException(sprintf('Parameter "%s" must be set.', $parameter));
            }
        }

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($parameters['oauth_signature'])) {
            unset($parameters['oauth_signature']);
        }

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($parameters, 'strcmp');

        // http_build_query should use RFC3986
        $parts = array(
            $method,
            rawurlencode($url),
            rawurlencode(str_replace(array('%7E','+'), array('~','%20'), http_build_query($parameters))),
        );

        $baseString = implode('&', $parts);

        $keyParts = array(
            rawurlencode($clientSecret),
            rawurlencode($tokenSecret),
        );

        $key = implode('&', $keyParts);

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
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

        return $this->generateUrl($path, array(), true);
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
