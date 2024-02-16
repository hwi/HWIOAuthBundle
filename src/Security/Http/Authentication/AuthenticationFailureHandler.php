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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Twig\Environment;

final class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly Environment $twig,
        private readonly bool $connect
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
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

            return new RedirectResponse(
                $this->router->generate('hwi_oauth_connect_registration', ['key' => $key], UrlGeneratorInterface::ABSOLUTE_PATH)
            );
        }

        if ($error instanceof AuthenticationException) {
            $error = $error->getMessageKey();
        } else {
            $error = $exception->getMessageKey();
        }

        return new Response(
            $this->twig->render('@HWIOAuth/Connect/login.html.twig', ['error' => $error])
        );
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
