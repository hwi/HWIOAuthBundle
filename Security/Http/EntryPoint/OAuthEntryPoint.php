<?php

namespace Knp\OAuthBundle\Security\Http\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;

use Knp\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    private $authorizationUrl, $clientId, $scope, $secret, $checkPath;

    public function __construct(HttpUtils $httpUtils, OAuthProviderInterface $oauthProvider, $checkPath)
    {
        $this->httpUtils        = $httpUtils;
        $this->oauthProvider    = $oauthProvider;
        $this->checkPath        = $checkPath;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (!$this->httpUtils->checkRequestPath($request, $this->checkPath)) {
            $loginCheckUrl = $this->httpUtils
                ->createRequest($request, $this->checkPath)
                ->getUri();

            $authorizationUrl = $this->oauthProvider->getAuthorizationUrl(array(
                'redirect_uri' => $loginCheckUrl.'?redirect_uri='.urlencode($request->getUri())
            ));

            return $this->httpUtils->createRedirectResponse($request, $authorizationUrl);
        }

        throw $authException;
    }
}