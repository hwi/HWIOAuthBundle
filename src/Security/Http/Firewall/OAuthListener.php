<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Firewall;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @internal
 */
class OAuthListener extends AbstractAuthenticationListener
{
    private ResourceOwnerMapInterface $resourceOwnerMap;

    /**
     * @var array<int, string>
     */
    private array $checkPaths;

    public function setResourceOwnerMap(ResourceOwnerMapInterface $resourceOwnerMap): void
    {
        $this->resourceOwnerMap = $resourceOwnerMap;
    }

    public function setCheckPaths(array $checkPaths): void
    {
        $this->checkPaths = $checkPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresAuthentication(Request $request): bool
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
     * @return TokenInterface|Response|null
     */
    protected function attemptAuthentication(Request $request)
    {
        /* @var ResourceOwnerInterface $resourceOwner */
        [$resourceOwner, $checkPath] = $this->resourceOwnerMap->getResourceOwnerByRequest($request);

        if (!$resourceOwner) {
            throw new AuthenticationException('No resource owner match the request.');
        }

        if (!$resourceOwner->handles($request)) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        // If resource owner supports only one url authentication, call redirect
        if ($request->query->has('authenticated') && $resourceOwner->getOption('auth_with_one_url')) {
            $request->attributes->set('service', $resourceOwner->getName());

            return new RedirectResponse(sprintf('%s?code=%s&authenticated=true', $this->httpUtils->generateUri($request, 'hwi_oauth_connect_service'), $request->query->get('code')));
        }

        $resourceOwner->isCsrfTokenValid(
            $this->extractCsrfTokenFromState($request->get('state'))
        );

        $accessToken = $resourceOwner->getAccessToken(
            $request,
            $this->httpUtils->createRequest($request, $checkPath)->getUri()
        );

        $token = new OAuthToken($accessToken);
        $token->setResourceOwnerName($resourceOwner->getName());

        return $this->authenticationManager->authenticate($token);
    }

    private function extractCsrfTokenFromState(?string $stateParameter): ?string
    {
        $state = new State($stateParameter);

        return $state->getCsrfToken() ?: $stateParameter;
    }
}
