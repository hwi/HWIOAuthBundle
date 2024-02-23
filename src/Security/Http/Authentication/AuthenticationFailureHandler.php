<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Authentication;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;

final class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private array $defaultOptions = [
        'failure_path' => 'hwi_oauth_connect_registration',
        'failure_forward' => false,
        'login_path' => '/login',
        'failure_path_parameter' => '_failure_path',
    ];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly bool $connect,
    ) {
    }

    public function setOptions(array $options): void
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $options = $this->defaultOptions;

        $failureUrl = ParameterBagUtils::getRequestParameterValue($request, $options['failure_path_parameter']);

        if (\is_string($failureUrl) && (str_starts_with($failureUrl, '/') || str_starts_with($failureUrl, 'http'))) {
            $options['failure_path'] = $failureUrl;
        }

        $options['failure_path'] ??= $options['login_path'];

        $error = $exception->getPrevious();

        if ($this->connect && $error instanceof AccountNotLinkedException) {
            $key = time();
            $session = $request->hasSession() ? $request->getSession() : $this->getSession();
            if ($session) {
                if (!$session->isStarted()) {
                    $session->start();
                }

                $session->set('_hwi_oauth.registration_error.'.$key, $error);
            }

            if ('/' === $options['failure_path'][0]) {
                $failurePath = $request->getUriForPath($options['failure_path'][0]);
            } else {
                $failurePath = $this->router->generate($options['failure_path'], ['key' => $key]);
            }

            return new RedirectResponse($failurePath);
        }

        if ($error instanceof AuthenticationException) {
            $error = $error->getMessageKey();
        } else {
            $error = $exception->getMessageKey();
        }

        if ('/' === $options['login_path'][0]) {
            $loginPath = $request->getUriForPath($options['login_path'][0]);
        } else {
            $loginPath = $this->router->generate($options['login_path'], ['error' => $error]);
        }

        return new RedirectResponse($loginPath);
    }

    private function getSession(): ?SessionInterface
    {
        if (method_exists($this->requestStack, 'getSession')) {
            return $this->requestStack->getSession();
        }

        if ((null !== $request = $this->requestStack->getCurrentRequest()) && $request->hasSession()) {
            return $request->getSession();
        }

        return null;
    }
}
