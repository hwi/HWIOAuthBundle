<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Http\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils,
    Symfony\Component\HttpFoundation\Request;

use Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

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
     * @var Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface
     */
    private $oauthProvider;

    /**
     * @var string
     */
    private $checkPath;

    /**
     * @param Symfony\Component\Security\Http\HttpUtils $httpUtils
     * @param Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface $oauthProvider
     * @param string $checkPath
     */
    public function __construct(HttpUtils $httpUtils, OAuthProviderInterface $oauthProvider, $checkPath, $loginPath)
    {
        $this->httpUtils        = $httpUtils;
        $this->oauthProvider    = $oauthProvider;
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

            $authorizationUrl = $this->oauthProvider->getAuthorizationUrl($request);

            return $this->httpUtils->createRedirectResponse($request, $authorizationUrl);
        }

        throw $authException;
    }
}