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

use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
final class ConnectController extends AbstractController
{
    /**
     * @var OAuthUtils
     */
    private $oauthUtils;

    /**
     * @var ResourceOwnerMapLocator
     */
    private $resourceOwnerMapLocator;

    public function __construct(OAuthUtils $oauthUtils, ResourceOwnerMapLocator $resourceOwnerMapLocator)
    {
        $this->oauthUtils = $oauthUtils;
        $this->resourceOwnerMapLocator = $resourceOwnerMapLocator;
    }

    /**
     * Shows a registration form if there is no user logged in and connecting
     * is enabled.
     *
     * @param Request $request a request
     * @param string  $key     key used for retrieving the right information for the registration form
     *
     * @return Response
     *
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if any user is authenticated
     * @throws \RuntimeException
     */
    public function registrationAction(Request $request, $key)
    {
        $connect = $this->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->isGranted($this->getParameter('hwi_oauth.grant_rule'));
        if ($hasUser) {
            throw new AccessDeniedException('Cannot connect already registered account.');
        }

        $error = null;
        $session = $request->hasSession() ? $request->getSession() : $this->get('session');
        if ($session) {
            if (!$session->isStarted()) {
                $session->start();
            }
            $error = $session->get('_hwi_oauth.registration_error.'.$key);
            $session->remove('_hwi_oauth.registration_error.'.$key);
        }

        if (!$error instanceof AccountNotLinkedException) {
            throw new \RuntimeException('Cannot register an account.', 0, $error instanceof \Exception ? $error : null);
        }

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken())
        ;

        /* @var $form FormInterface */
        $form = $this->get('hwi_oauth.registration.form.factory')->createForm();

        $formHandler = $this->get('hwi_oauth.registration.form.handler');
        if ($formHandler->process($request, $form, $userInformation)) {
            $event = new FormEvent($form, $request);
            $this->dispatch($event, HWIOAuthEvents::REGISTRATION_SUCCESS);

            $this->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);

            // Authenticate the user
            $this->authenticateUser($request, $form->getData(), $error->getResourceOwnerName(), $error->getAccessToken());

            if (null === $response = $event->getResponse()) {
                if ($targetPath = $this->getTargetPath($session)) {
                    $response = $this->redirect($targetPath);
                } else {
                    $response = $this->render('@HWIOAuth/Connect/registration_success.html.twig', [
                        'userInformation' => $userInformation,
                    ]);
                }
            }

            $event = new FilterUserResponseEvent($form->getData(), $request, $response);
            $this->dispatch($event, HWIOAuthEvents::REGISTRATION_COMPLETED);

            return $event->getResponse();
        }

        if ($session) {
            // reset the error in the session
            $session->set('_hwi_oauth.registration_error.'.$key, $error);
        }

        $event = new GetResponseUserEvent($form->getData(), $request);
        $this->dispatch($event, HWIOAuthEvents::REGISTRATION_INITIALIZE);

        if ($response = $event->getResponse()) {
            return $response;
        }

        return $this->render('@HWIOAuth/Connect/registration.html.twig', [
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ]);
    }

    /**
     * Connects a user to a given account if the user is logged in and connect is enabled.
     *
     * @param Request $request the active request
     * @param string  $service name of the resource owner to connect to
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if no user is authenticated
     */
    public function connectServiceAction(Request $request, $service)
    {
        $connect = $this->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->isGranted($this->getParameter('hwi_oauth.grant_rule'));
        if (!$hasUser) {
            throw new AccessDeniedException('Cannot connect an account.');
        }

        // Get the data from the resource owner
        $resourceOwner = $this->getResourceOwnerByName($service);

        $session = $request->hasSession() ? $request->getSession() : $this->get('session');
        if ($session && !$session->isStarted()) {
            $session->start();
        }

        $key = $request->query->get('key', time());

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
            if ($this->getParameter('hwi_oauth.failed_use_referer') && $targetPath = $this->getTargetPath($session)) {
                return $this->redirect($targetPath);
            }

            return $this->redirectToRoute($this->getParameter('hwi_oauth.failed_auth_path'));
        }

        // Show confirmation page?
        if (!$this->getParameter('hwi_oauth.connect.confirmation')) {
            return $this->getConfirmationResponse($request, $accessToken, $service);
        }

        /** @var $form FormInterface */
        $form = $this->createForm(FormType::class);

        // Handle the form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->getConfirmationResponse($request, $accessToken, $service);
        }

        $event = new GetResponseUserEvent($this->getUser(), $request);

        $this->dispatch($event, HWIOAuthEvents::CONNECT_INITIALIZE);

        if ($response = $event->getResponse()) {
            return $response;
        }

        return $this->render('@HWIOAuth/Connect/connect_confirm.html.twig', [
            'key' => $key,
            'service' => $service,
            'form' => $form->createView(),
            'userInformation' => $resourceOwner->getUserInformation($accessToken),
        ]);
    }

    protected function getParameter(string $name)
    {
        // Symfony 3.4 compat
        return $this->container->getParameter($name);
    }

    /**
     * Get a resource owner by name.
     *
     * @param string $name
     *
     * @return ResourceOwnerInterface
     *
     * @throws NotFoundHttpException if there is no resource owner with the given name
     */
    private function getResourceOwnerByName($name)
    {
        foreach ($this->getParameter('hwi_oauth.firewall_names') as $firewall) {
            if (!$this->resourceOwnerMapLocator->has($firewall)) {
                continue;
            }

            $ownerMap = $this->resourceOwnerMapLocator->get($firewall);
            if ($resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
                return $resourceOwner;
            }
        }

        throw new NotFoundHttpException(sprintf("No resource owner with name '%s'.", $name));
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param Request       $request
     * @param UserInterface $user
     * @param string        $resourceOwnerName
     * @param string|array  $accessToken
     * @param bool          $fakeLogin
     */
    private function authenticateUser(Request $request, UserInterface $user, $resourceOwnerName, $accessToken, $fakeLogin = true)
    {
        try {
            $userChecker = $this->get('hwi_oauth.user_checker');
            $userChecker->checkPreAuth($user);
            $userChecker->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            return;
        }

        $token = new OAuthToken($accessToken, $user->getRoles());
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setUser($user);
        $token->setAuthenticated(true);

        $this->get('security.token_storage')->setToken($token);

        if ($fakeLogin) {
            // Since we're "faking" normal login, we need to throw our INTERACTIVE_LOGIN event manually
            $this->dispatch(
                new InteractiveLoginEvent($request, $token),
                SecurityEvents::INTERACTIVE_LOGIN
            );
        }
    }

    /**
     * @param SessionInterface $session
     *
     * @return string|null
     */
    private function getTargetPath(?SessionInterface $session)
    {
        if (!$session) {
            return null;
        }

        foreach ($this->getParameter('hwi_oauth.firewall_names') as $providerKey) {
            $sessionKey = '_security.'.$providerKey.'.target_path';
            if ($session->has($sessionKey)) {
                return $session->get($sessionKey);
            }
        }

        return null;
    }

    /**
     * @param Request $request     The active request
     * @param array   $accessToken The access token
     * @param string  $service     Name of the resource owner to connect to
     *
     * @return Response
     *
     * @throws NotFoundHttpException if there is no resource owner with the given name
     */
    private function getConfirmationResponse(Request $request, array $accessToken, $service)
    {
        /** @var $currentToken OAuthToken */
        $currentToken = $this->get('security.token_storage')->getToken();
        /** @var $currentUser UserInterface */
        $currentUser = $currentToken->getUser();

        /** @var $resourceOwner ResourceOwnerInterface */
        $resourceOwner = $this->getResourceOwnerByName($service);
        /** @var $userInformation UserResponseInterface */
        $userInformation = $resourceOwner->getUserInformation($accessToken);

        $event = new GetResponseUserEvent($currentUser, $request);
        $this->dispatch($event, HWIOAuthEvents::CONNECT_CONFIRMED);

        $this->get('hwi_oauth.account.connector')->connect($currentUser, $userInformation);

        if ($currentToken instanceof OAuthToken) {
            // Update user token with new details
            $newToken =
                \is_array($accessToken) &&
                (isset($accessToken['access_token']) || isset($accessToken['oauth_token'])) ?
                    $accessToken : $currentToken->getRawToken();

            $this->authenticateUser($request, $currentUser, $service, $newToken, false);
        }

        if (null === $response = $event->getResponse()) {
            if ($targetPath = $this->getTargetPath($request->getSession())) {
                $response = $this->redirect($targetPath);
            } else {
                $response = $this->render('@HWIOAuth/Connect/connect_success.html.twig', [
                    'userInformation' => $userInformation,
                    'service' => $service,
                ]);
            }
        }

        $event = new FilterUserResponseEvent($currentUser, $request, $response);
        $this->dispatch($event, HWIOAuthEvents::CONNECT_COMPLETED);

        return $response;
    }

    private function dispatch($event, string $eventName = null)
    {
        // LegacyEventDispatcherProxy exists in Symfony >= 4.3
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            // New Symfony 4.3 EventDispatcher signature
            $this->get('event_dispatcher')->dispatch($event, $eventName);
        } else {
            // Old EventDispatcher signature
            $this->get('event_dispatcher')->dispatch($eventName, $event);
        }
    }
}
