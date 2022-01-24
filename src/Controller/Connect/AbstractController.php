<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Controller\Connect;

use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use Symfony\Component\EventDispatcher\Event as DeprecatedEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @internal
 */
abstract class AbstractController
{
    protected ResourceOwnerMapLocator $resourceOwnerMapLocator;
    protected RequestStack $requestStack;
    protected EventDispatcherInterface $dispatcher;
    protected TokenStorageInterface $tokenStorage;
    protected UserCheckerInterface $userChecker;
    protected Environment $twig;
    protected ?AccountConnectorInterface $accountConnector;

    public function __construct(
        ResourceOwnerMapLocator $resourceOwnerMapLocator,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        Environment $twig,
        ?AccountConnectorInterface $accountConnector
    ) {
        $this->resourceOwnerMapLocator = $resourceOwnerMapLocator;
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->twig = $twig;
        $this->accountConnector = $accountConnector;
    }

    /**
     * Get a resource owner by name.
     *
     * @throws NotFoundHttpException if there is no resource owner with the given name
     */
    protected function getResourceOwnerByName(string $name): ResourceOwnerInterface
    {
        foreach ($this->resourceOwnerMapLocator->getResourceOwnerMaps() as $ownerMap) {
            if ($resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
                return $resourceOwner;
            }
        }

        throw new NotFoundHttpException(sprintf("No resource owner with name '%s'.", $name));
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param string|array $accessToken
     */
    protected function authenticateUser(Request $request, UserInterface $user, string $resourceOwnerName, $accessToken, bool $fakeLogin = true): void
    {
        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            return;
        }

        $token = new OAuthToken($accessToken, $user->getRoles());
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setUser($user);

        // required for compatibility with Symfony 5.4
        if (method_exists($token, 'setAuthenticated')) {
            $token->setAuthenticated(true, false);
        }

        $this->tokenStorage->setToken($token);

        if ($fakeLogin) {
            // Since we're "faking" normal login, we need to throw our INTERACTIVE_LOGIN event manually
            $this->dispatch(
                new InteractiveLoginEvent($request, $token),
                SecurityEvents::INTERACTIVE_LOGIN
            );
        }
    }

    /**
     * @param string $service name of the resource owner to connect to
     *
     * @throws NotFoundHttpException if there is no resource owner with the given name
     */
    protected function getConfirmationResponse(Request $request, array $accessToken, string $service): Response
    {
        /** @var OAuthToken $currentToken */
        $currentToken = $this->tokenStorage->getToken();
        /** @var UserInterface $currentUser */
        $currentUser = $currentToken->getUser();

        $resourceOwner = $this->getResourceOwnerByName($service);
        $userInformation = $resourceOwner->getUserInformation($accessToken);

        $event = new GetResponseUserEvent($currentUser, $request);
        $this->dispatch($event, HWIOAuthEvents::CONNECT_CONFIRMED);

        $this->accountConnector->connect($currentUser, $userInformation);

        if ($currentToken instanceof OAuthToken) {
            // Update user token with new details
            $newToken =
                (isset($accessToken['access_token']) || isset($accessToken['oauth_token'])) ?
                    $accessToken : $currentToken->getRawToken();

            $this->authenticateUser($request, $currentUser, $service, $newToken, false);
        }

        if (null === $response = $event->getResponse()) {
            if ($targetPath = $this->getTargetPath($request->getSession())) {
                $response = new RedirectResponse($targetPath);
            } else {
                $response = new Response($this->twig->render('@HWIOAuth/Connect/connect_success.html.twig', [
                    'userInformation' => $userInformation,
                    'service' => $service,
                ]));
            }
        }

        $event = new FilterUserResponseEvent($currentUser, $request, $response);
        $this->dispatch($event, HWIOAuthEvents::CONNECT_COMPLETED);

        return $event->getResponse();
    }

    /**
     * @param Event|DeprecatedEvent $event
     */
    protected function dispatch($event, string $eventName = null): void
    {
        $this->dispatcher->dispatch($event, $eventName);
    }

    protected function getSession(): ?SessionInterface
    {
        if (method_exists($this->requestStack, 'getSession')) {
            return $this->requestStack->getSession();
        }

        if ((null !== $request = $this->requestStack->getCurrentRequest()) && $request->hasSession()) {
            return $request->getSession();
        }

        return null;
    }

    protected function getTargetPath(?SessionInterface $session): ?string
    {
        if (!$session) {
            return null;
        }

        foreach ($this->resourceOwnerMapLocator->getFirewallNames() as $firewallName) {
            $sessionKey = '_security.'.$firewallName.'.target_path';
            if ($session->has($sessionKey)) {
                return $session->get($sessionKey);
            }
        }

        return null;
    }
}
