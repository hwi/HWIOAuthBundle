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
use HWI\Bundle\OAuthBundle\Security\Http\Authenticator\Passport\SelfValidatedOAuthPassport;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\LazyResponseException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Vadim Borodavko <vadim.borodavko@gmail.com>
 */
final class OAuthAuthenticator implements AuthenticatorInterface, AuthenticationEntryPointInterface, InteractiveAuthenticatorInterface
{
    private HttpUtils $httpUtils;
    private OAuthAwareUserProviderInterface $userProvider;
    private ResourceOwnerMapInterface $resourceOwnerMap;
    private AuthenticationSuccessHandlerInterface $successHandler;
    private AuthenticationFailureHandlerInterface $failureHandler;
    private HttpKernelInterface $httpKernel;

    /**
     * @var string[]
     */
    private array $checkPaths;

    private array $options;

    public function __construct(
        HttpUtils $httpUtils,
        OAuthAwareUserProviderInterface $userProvider,
        ResourceOwnerMapInterface $resourceOwnerMap,
        array $checkPaths,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        HttpKernelInterface $kernel,
        array $options
    ) {
        $this->failureHandler = $failureHandler;
        $this->successHandler = $successHandler;
        $this->checkPaths = $checkPaths;
        $this->resourceOwnerMap = $resourceOwnerMap;
        $this->userProvider = $userProvider;
        $this->httpUtils = $httpUtils;
        $this->httpKernel = $kernel;
        $this->options = $options;
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

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if ($this->options['use_forward'] ?? false) {
            $subRequest = $this->httpUtils->createRequest($request, $this->options['login_path']);

            $iterator = $request->query->getIterator();
            $subRequest->query->add(iterator_to_array($iterator));

            $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            if (200 === $response->getStatusCode()) {
                $response->headers->set('X-Status-Code', '401');
            }

            return $response;
        }

        return new RedirectResponse($this->httpUtils->generateUri($request, $this->options['login_path']));
    }

    /**
     * @throws AuthenticationException
     * @throws LazyResponseException
     */
    public function authenticate(Request $request): Passport
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

        return new SelfValidatedOAuthPassport($this->refreshToken($token), [new RememberMeBadge()]);
    }

    /**
     * This function can be used for refreshing an expired token
     * or for custom "password grant" authenticator, if site owner also owns oauth instance.
     *
     * @template T of OAuthToken
     *
     * @param T $token
     *
     * @return T
     */
    public function refreshToken(OAuthToken $token): OAuthToken
    {
        if (!$token->isExpired() && null !== $token->getUser()) {
            return $this->recreateToken($token, $token->getUser());
        }

        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());
        if (!$resourceOwner) {
            throw new AuthenticationServiceException('Unknown resource owner set on token: '.$token->getResourceOwnerName());
        }

        if ($token->isExpired()) {
            $expiredToken = $token;
            if ($refreshToken = $expiredToken->getRefreshToken()) {
                $tokenClass = \get_class($expiredToken);
                $token = new $tokenClass($resourceOwner->refreshAccessToken($refreshToken));
                $token->setResourceOwnerName($expiredToken->getResourceOwnerName());
                if (!$token->getRefreshToken()) {
                    $token->setRefreshToken($expiredToken->getRefreshToken());
                }
                $token->copyPersistentDataFrom($expiredToken);
            } else {
                // if you cannot refresh token, you do not need to make user_info request to oauth-resource
                if (null !== $expiredToken->getUser()) {
                    return $expiredToken;
                }
            }
            unset($expiredToken);
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

        return $this->recreateToken($token, $user);
    }

    /**
     * @template T of OAuthToken
     *
     * @param T              $token
     * @param ?UserInterface $user
     *
     * @return T
     */
    public function recreateToken(OAuthToken $token, UserInterface $user = null): OAuthToken
    {
        $user = $user instanceof UserInterface ? $user : $token->getUser();

        $tokenClass = \get_class($token);
        if ($user) {
            $newToken = new $tokenClass(
                $token->getRawToken(),
                method_exists($user, 'getRoles') ? $user->getRoles() : []
            );
            $newToken->setUser($user);
        } else {
            $newToken = new $tokenClass($token->getRawToken());
        }

        $newToken->setResourceOwnerName($token->getResourceOwnerName());
        $newToken->setRefreshToken($token->getRefreshToken());
        $newToken->setCreatedAt($token->getCreatedAt());
        $newToken->setTokenSecret($token->getTokenSecret());
        $newToken->setAttributes($token->getAttributes());

        // required for compatibility with Symfony 5.4
        if (method_exists($newToken, 'setAuthenticated')) {
            $newToken->setAuthenticated((bool) $user, false);
        }

        $newToken->copyPersistentDataFrom($token);

        return $newToken;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return $this->createAuthenticatedToken($passport, $firewallName);
    }

    /**
     * @param Passport|SelfValidatedOAuthPassport $passport
     */
    public function createAuthenticatedToken($passport, string $firewallName): TokenInterface
    {
        if ($passport instanceof SelfValidatedOAuthPassport) {
            return $passport->getToken();
        }

        throw new \LogicException(sprintf('The first argument of "%s" must be instance of "%s", "%s" provided.', __METHOD__, SelfValidatedOAuthPassport::class, \get_class($passport)));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    private function extractCsrfTokenFromState(?string $stateParameter): ?string
    {
        $state = new State($stateParameter);

        return $state->getCsrfToken() ?: $stateParameter;
    }
}
