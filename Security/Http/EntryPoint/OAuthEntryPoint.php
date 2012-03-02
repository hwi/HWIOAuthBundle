<?php

/*
 * This file is part of the HWiOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils,
    Symfony\Component\HttpFoundation\Request;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * OAuthEntryPoint
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var Symfony\Component\Security\Http\HttpUtils
     */
    private $httpUtils;

    /**
     * @var HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    private $resourceOwner;

    /**
     * @var string
     */
    private $checkPath;

    /**
     * @param Symfony\Component\Security\Http\HttpUtils $httpUtils
     * @param HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $resourceOwner
     * @param string $checkPath
     */
    public function __construct(HttpUtils $httpUtils, ResourceOwnerInterface $resourceOwner, $checkPath, $loginPath)
    {
        $this->httpUtils        = $httpUtils;
        $this->resourceOwner    = $resourceOwner;
        $this->checkPath        = $checkPath;
        $this->loginPath        = $loginPath;
    }

    /**
     * {@inheritDoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (!$this->httpUtils->checkRequestPath($request, $this->checkPath)) {
            if ($this->httpUtils->checkRequestPath($request, $this->loginPath)) {
                $request->getSession()->remove('_security.target_path');
            }

            $authorizationUrl = $this->resourceOwner->getAuthorizationUrl(
                $this->httpUtils->createRequest($request, $this->checkPath)->getUri()
            );

            return $this->httpUtils->createRedirectResponse($request, $authorizationUrl);
        }

        throw $authException;
    }
}
