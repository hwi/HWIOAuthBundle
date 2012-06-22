<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OauthBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\Exception\AuthenticationException;


use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;

/**
 * OAuthListener
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var ResourceOwnerMap
     */
    private $resourceOwnerMap;

    /**
     * @var array
     */
    private $checkPaths;

    /**
     * @var ResourceOwnerMap $resourceOwnerMap
     */
    public function setResourceOwnerMap(ResourceOwnerMap $resourceOwnerMap)
    {
        $this->resourceOwnerMap = $resourceOwnerMap;
    }

    /**
     * @param array $checkPaths
     */
    public function setCheckPaths(array $checkPaths)
    {
        $this->checkPaths = $checkPaths;
    }

    /**
     * {@inheritDoc}
     */
    public function requiresAuthentication(Request $request)
    {
        // Check if the route matches one of the check paths
        foreach ($this->checkPaths as $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        list($resourceOwner, $checkPath) = $this->resourceOwnerMap->getResourceOwnerByRequest($request);

        $code = $request->query->get('code');
        // We have more than one resource registered probably, let's try to get code instead dying in loop
        if (null === $code) {
            $authorizationUrl = $resourceOwner->getAuthorizationUrl(
                $this->httpUtils->createRequest($request, $checkPath)->getUri()
            );

            return $this->httpUtils->createRedirectResponse($request, $authorizationUrl);
        }

        $accessToken = $resourceOwner->getAccessToken(
            $code,
            $this->httpUtils->createRequest($request, $checkPath)->getUri()
        );

        $token = new OAuthToken($accessToken);
        $token->setResourceOwnerName($resourceOwner->getName());

        return $this->authenticationManager->authenticate($token);
    }
}
