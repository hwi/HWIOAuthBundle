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
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @internal
 */
final class ConnectController extends AbstractController
{
    private OAuthUtils $oauthUtils;
    private AuthorizationCheckerInterface $authorizationChecker;
    private FormFactoryInterface $formFactory;
    private RouterInterface $router;
    private string $grantRule;
    private bool $failedUseReferer;
    private string $failedAuthPath;
    private bool $enableConnectConfirmation;

    public function __construct(
        OAuthUtils $oauthUtils,
        ResourceOwnerMapLocator $resourceOwnerMapLocator,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        Environment $twig,
        RouterInterface $router,
        string $grantRule,
        bool $failedUseReferer,
        string $failedAuthPath,
        bool $enableConnectConfirmation,
        ?AccountConnectorInterface $accountConnector
    ) {
        parent::__construct(
            $resourceOwnerMapLocator,
            $requestStack,
            $dispatcher,
            $tokenStorage,
            $userChecker,
            $twig,
            $accountConnector
        );

        $this->oauthUtils = $oauthUtils;
        $this->grantRule = $grantRule;
        $this->failedUseReferer = $failedUseReferer;
        $this->failedAuthPath = $failedAuthPath;
        $this->enableConnectConfirmation = $enableConnectConfirmation;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * Connects a user to a given account if the user is logged in and connect is enabled.
     *
     * @param string $service name of the resource owner to connect to
     *
     * @throws \Exception
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if no user is authenticated
     */
    public function connectServiceAction(Request $request, string $service): Response
    {
        if (!$this->accountConnector) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->authorizationChecker->isGranted($this->grantRule);
        if (!$hasUser) {
            throw new AccessDeniedException('Cannot connect an account.');
        }

        // Get the data from the resource owner
        $resourceOwner = $this->getResourceOwnerByName($service);

        $session = $request->hasSession() ? $request->getSession() : $this->getSession();
        if ($session && !$session->isStarted()) {
            $session->start();
        }

        $key = $request->query->get('key', (string) time());

        $accessToken = null;
        if ($resourceOwner->handles($request)) {
            $accessToken = $resourceOwner->getAccessToken(
                $request,
                $this->oauthUtils->getServiceAuthUrl($request, $resourceOwner)
            );

            if ($session) {
                // save in session
                $session->set('_hwi_oauth.connect_confirmation.'.$key, $accessToken);
            }
        } elseif ($session) {
            $accessToken = $session->get('_hwi_oauth.connect_confirmation.'.$key);
        }

        // Redirect to the login path if the token is empty (Eg. User cancelled auth)
        if (null === $accessToken) {
            if ($this->failedUseReferer && $targetPath = $this->getTargetPath($session)) {
                return new RedirectResponse($targetPath);
            }

            return new RedirectResponse($this->router->generate($this->failedAuthPath));
        }

        // Show confirmation page?
        if (!$this->enableConnectConfirmation) {
            return $this->getConfirmationResponse($request, $accessToken, $service);
        }

        $form = $this->formFactory->create();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->getConfirmationResponse($request, $accessToken, $service);
        }

        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        $event = new GetResponseUserEvent($token->getUser(), $request);

        $this->dispatch($event, HWIOAuthEvents::CONNECT_INITIALIZE);

        if ($response = $event->getResponse()) {
            return $response;
        }

        return new Response($this->twig->render('@HWIOAuth/Connect/connect_confirm.html.twig', [
            'key' => $key,
            'service' => $service,
            'form' => $form->createView(),
            'userInformation' => $resourceOwner->getUserInformation($accessToken),
        ]));
    }
}
