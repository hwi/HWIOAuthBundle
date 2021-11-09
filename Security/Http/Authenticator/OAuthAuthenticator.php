<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Authenticator;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\LazyResponseException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Vadim Borodavko <vadim.borodavko@gmail.com>
 */
final class OAuthAuthenticator implements AuthenticatorInterface
{
    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var OAuthAwareUserProviderInterface
     */
    private $userProvider;

    /**
     * @var ResourceOwnerMapInterface
     */
    private $resourceOwnerMap;

    /**
     * @var string[]
     */
    private $checkPaths;

    /**
     * @var AuthenticationSuccessHandlerInterface
     */
    private $successHandler;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    private $failureHandler;

    /**
     * @var mixed[]
     */
    private $rawToken;

    /**
     * @var string
     */
    private $resourceOwnerName;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var string
     */
    private $createdAt;

    public function __construct(
        HttpUtils $httpUtils,
        OAuthAwareUserProviderInterface $userProvider,
        ResourceOwnerMapInterface $resourceOwnerMap,
        array $checkPaths,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler
    ) {
        $this->failureHandler = $failureHandler;
        $this->successHandler = $successHandler;
        $this->checkPaths = $checkPaths;
        $this->resourceOwnerMap = $resourceOwnerMap;
        $this->userProvider = $userProvider;
        $this->httpUtils = $httpUtils;
    }

    public function supports(Request $request): bool
    {
        foreach ($this->checkPaths as $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws AuthenticationException
     * @throws LazyResponseException
     */
    public function authenticate(Request $request): PassportInterface
    {
        [$resourceOwner, $checkPath] = $this->resourceOwnerMap->getResourceOwnerByRequest($request);

        if (!$resourceOwner instanceof ResourceOwnerInterface) {
            throw new AuthenticationException('No resource owner match the request.');
        }

        if (!$resourceOwner->handles($request)) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        // If resource owner supports only one url authentication, call redirect
        if ($request->query->has('authenticated') && $resourceOwner->getOption('auth_with_one_url')) {
            $request->attributes->set('service', $resourceOwner->getName());

            throw new LazyResponseException(new RedirectResponse(sprintf('%s?code=%s&authenticated=true', $this->httpUtils->generateUri($request, 'hwi_oauth_connect_service'), $request->query->get('code'))));
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

        if ($token->isExpired()) {
            $token = $this->refreshToken($token, $resourceOwner);
        }

        $userResponse = $resourceOwner->getUserInformation($token->getRawToken());

        try {
            $user = $this->userProvider->loadUserByOAuthUserResponse($userResponse);
        } catch (OAuthAwareExceptionInterface $e) {
            $e->setToken($token);
            $e->setResourceOwnerName($token->getResourceOwnerName());

            throw $e;
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('loadUserByOAuthUserResponse() must return a UserInterface.');
        }

        $this->rawToken = $token->getRawToken();
        $this->resourceOwnerName = $resourceOwner->getName();
        $this->refreshToken = $token->getRefreshToken();
        $this->createdAt = $token->getCreatedAt();

        return new SelfValidatingPassport(
            class_exists(UserBadge::class)
                ? new UserBadge(
                    method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername(),
                    static function () use ($user) { return $user; }
                )
                : $user
        );
    }

    public function createToken(PassportInterface $passport, string $firewallName): TokenInterface
    {
        return $this->createAuthenticatedToken($passport, $firewallName);
    }

    public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface
    {
        $token = new OAuthToken($this->rawToken, $passport->getUser()->getRoles());
        $token->setResourceOwnerName($this->resourceOwnerName);
        $token->setUser($passport->getUser());
        $token->setRefreshToken($this->refreshToken);
        $token->setCreatedAt($this->createdAt);

        $this->rawToken = null;
        $this->resourceOwnerName = null;
        $this->refreshToken = null;
        $this->createdAt = null;

        return $token;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    private function refreshToken(OAuthToken $expiredToken, ResourceOwnerInterface $resourceOwner): OAuthToken
    {
        if (!$expiredToken->getRefreshToken()) {
            return $expiredToken;
        }

        $token = new OAuthToken($resourceOwner->refreshAccessToken($expiredToken->getRefreshToken()));
        $token->setRefreshToken($expiredToken->getRefreshToken());

        return $token;
    }

    private function extractCsrfTokenFromState(?string $stateParameter): ?string
    {
        $state = new State($stateParameter);

        return $state->getCsrfToken() ?: $stateParameter;
    }
}
