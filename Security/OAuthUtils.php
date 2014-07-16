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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * OAuthUtils
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class OAuthUtils
{
    const SIGNATURE_METHOD_HMAC      = 'HMAC-SHA1';
    const SIGNATURE_METHOD_RSA       = 'RSA-SHA1';
    const SIGNATURE_METHOD_PLAINTEXT = 'PLAINTEXT';

    /**
     * @var boolean
     */
    protected $connect;

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var ResourceOwnerMap
     */
    protected $ownerMap;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param HttpUtils                $httpUtils
     * @param SecurityContextInterface $securityContext
     * @param boolean                  $connect
     */
    public function __construct(HttpUtils $httpUtils, SecurityContextInterface $securityContext, $connect)
    {
        $this->httpUtils       = $httpUtils;
        $this->securityContext = $securityContext;
        $this->connect         = $connect;
    }

    /**
     * @param ResourceOwnerMap $ownerMap
     */
    public function setResourceOwnerMap(ResourceOwnerMap $ownerMap)
    {
        $this->ownerMap = $ownerMap;
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
     * @param Request $request
     * @param string  $name
     * @param string  $redirectUrl     Optional
     * @param array   $extraParameters Optional
     *
     * @return string
     */
    public function getAuthorizationUrl(Request $request, $name, $redirectUrl = null, array $extraParameters = array())
    {
        $resourceOwner = $this->getResourceOwner($name);
        if (null === $redirectUrl) {
            if (!$this->connect || !$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $redirectUrl = $this->httpUtils->generateUri($request, $this->ownerMap->getResourceOwnerCheckPath($name));
            } else {
                $redirectUrl = $this->getServiceAuthUrl($request, $resourceOwner);
            }
        }

        return $resourceOwner->getAuthorizationUrl($redirectUrl, $extraParameters);
    }

    /**
     * @param Request                $request
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return string
     */
    public function getServiceAuthUrl(Request $request, ResourceOwnerInterface $resourceOwner)
    {
        if ($resourceOwner->getOption('auth_with_one_url')) {
            $redirectUrl = $this->httpUtils->generateUri($request, $this->ownerMap->getResourceOwnerCheckPath($resourceOwner->getName())).'?authenticated=true';
        } else {
            $request->attributes->set('service', $resourceOwner->getName());
            $redirectUrl = $this->httpUtils->generateUri($request, 'hwi_oauth_connect_service');
        }

        return $redirectUrl;
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function getLoginUrl(Request $request, $name)
    {
        // Just to check that this resource owner exists
        $this->getResourceOwner($name);

        $request->attributes->set('service', $name);

        return $this->httpUtils->generateUri($request, 'hwi_oauth_service_redirect');
    }

    /**
     * Sign the request parameters
     *
     * @param string $method          Request method
     * @param string $url             Request url
     * @param array  $parameters      Parameters for the request
     * @param string $clientSecret    Client secret to use as key part of signing
     * @param string $tokenSecret     Optional token secret to use with signing
     * @param string $signatureMethod Optional signature method used to sign token
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function signRequest($method, $url, $parameters, $clientSecret, $tokenSecret = '', $signatureMethod = self::SIGNATURE_METHOD_HMAC)
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

        // Parse & add query params as base string parameters if they exists
        $url = parse_url($url);
        if (isset($url['query'])) {
            parse_str($url['query'], $queryParams);
            $parameters += $queryParams;
        }

        // Remove default ports
        // Ref: Spec: 9.1.2
        $explicitPort = isset($url['port']) ? $url['port'] : null;
        if (('https' === $url['scheme'] && 443 === $explicitPort) || ('http' === $url['scheme'] && 80 === $explicitPort)) {
            $explicitPort = null;
        }

        // Remove query params from URL
        // Ref: Spec: 9.1.2
        $url = sprintf('%s://%s%s%s', $url['scheme'], $url['host'], ($explicitPort ? ':'.$explicitPort : ''), isset($url['path']) ? $url['path'] : '');

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($parameters, 'strcmp');

        // http_build_query should use RFC3986
        $parts = array(
            // HTTP method name must be uppercase
            // Ref: Spec: 9.1.3 (1)
            strtoupper($method),
            rawurlencode($url),
            rawurlencode(str_replace(array('%7E', '+'), array('~', '%20'), http_build_query($parameters, '', '&'))),
        );

        $baseString = implode('&', $parts);

        switch ($signatureMethod) {
            case self::SIGNATURE_METHOD_HMAC:
                $keyParts = array(
                    rawurlencode($clientSecret),
                    rawurlencode($tokenSecret),
                );

                $signature = hash_hmac('sha1', $baseString, implode('&', $keyParts), true);
                break;

            case self::SIGNATURE_METHOD_RSA:
                if (!function_exists('openssl_pkey_get_private')) {
                    throw new \RuntimeException('RSA-SHA1 signature method requires the OpenSSL extension.');
                }

                $privateKey = openssl_pkey_get_private(file_get_contents($clientSecret), $tokenSecret);
                $signature  = false;

                openssl_sign($baseString, $signature, $privateKey);
                openssl_free_key($privateKey);
                break;

            case self::SIGNATURE_METHOD_PLAINTEXT:
                $signature = $baseString;
                break;

            default:
                throw new \RuntimeException(sprintf('Unknown signature method selected %s.', $signatureMethod));
        }

        return base64_encode($signature);
    }

    /**
     * @param string $name
     *
     * @return ResourceOwnerInterface
     *
     * @throws \RuntimeException
     */
    protected function getResourceOwner($name)
    {
        $resourceOwner = $this->ownerMap->getResourceOwnerByName($name);
        if (!$resourceOwner instanceof ResourceOwnerInterface) {
            throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
        }

        return $resourceOwner;
    }
}
