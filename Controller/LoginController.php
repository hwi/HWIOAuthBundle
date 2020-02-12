<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Controller;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
final class LoginController extends AbstractController
{
    /**
     * @var bool
     */
    private $connect;

    /**
     * @var string
     */
    private $grantRule;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        bool $connect,
        string $grantRule
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->session = $session;
        $this->connect = $connect;
        $this->grantRule = $grantRule;
    }

    /**
     * Action that handles the login 'form'. If connecting is enabled the
     * user will be redirected to the appropriate login urls or registration forms.
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return Response
     */
    public function connectAction(Request $request): Response
    {
        try {
            $hasUser = $this->authorizationChecker->isGranted($this->grantRule);
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            $hasUser = false;
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        // if connecting is enabled and there is no user, redirect to the registration form
        if ($this->connect && !$hasUser && $error instanceof AccountNotLinkedException) {
            $key = time();
            $session = $request->hasSession() ? $request->getSession() : $this->session;
            if ($session) {
                if (!$session->isStarted()) {
                    $session->start();
                }

                $session->set('_hwi_oauth.registration_error.'.$key, $error);
            }

            return new RedirectResponse($this->router->generate('hwi_oauth_connect_registration', ['key' => $key], UrlGeneratorInterface::ABSOLUTE_PATH));
        }

        if (null !== $error) {
            $error = $error->getMessageKey();
        }

        return $this->render('@HWIOAuth/Connect/login.html.twig', [
            'error' => $error,
        ]);
    }
}
