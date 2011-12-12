<?php

namespace Knp\OAuthBundle\Security\Http\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;

class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    private $authorizationUrl, $clientId, $scope, $secret, $checkPath;

    public function __construct(HttpUtils $httpUtils, $authorizationUrl, $clientId, $scope, $secret, $checkPath)
    {
        $this->httpUtils        = $httpUtils;
        $this->authorizationUrl = $authorizationUrl;
        $this->clientId         = $clientId;
        $this->scope            = $scope;
        $this->secret           = $secret;
        $this->checkPath        = $checkPath;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (!$this->httpUtils->checkRequestPath($request, $this->checkPath)) {
            return $this->httpUtils->createRedirectResponse($request, $this->getAuthorizationUrl($request));
        }

        throw $authException;
    }

    private function getAuthorizationUrl(Request $request)
    {
        $loginCheckUrl = $this->httpUtils->createRequest($request, $this->checkPath)->getUri();

        return $this->authorizationUrl.'?'.http_build_query(array(
            'response_type' => 'code',
            'redirect_uri'  => $loginCheckUrl.'?redirect_uri='.urlencode($request->getUri()),
            'client_id'     => $this->clientId,
            'scope'         => $this->scope,
        ));
    }
}