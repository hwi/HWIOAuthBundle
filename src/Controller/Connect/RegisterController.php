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
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @internal
 */
final class RegisterController extends AbstractController
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private FormFactoryInterface $formFactory;
    private ?RegistrationFormHandlerInterface $formHandler;
    private string $grantRule;
    private string $registrationForm;

    public function __construct(
        ResourceOwnerMapLocator $resourceOwnerMapLocator,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        Environment $twig,
        string $grantRule,
        string $registrationForm,
        ?AccountConnectorInterface $accountConnector,
        ?RegistrationFormHandlerInterface $formHandler
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

        $this->grantRule = $grantRule;
        $this->registrationForm = $registrationForm;
        $this->formHandler = $formHandler;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
    }

    /**
     * Shows a registration form if there is no user logged in and connecting
     * is enabled.
     *
     * @param string $key key used for retrieving the right information for the registration form
     *
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if any user is authenticated
     * @throws \RuntimeException
     */
    public function registrationAction(Request $request, string $key): Response
    {
        if (!$this->accountConnector || !$this->formHandler) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->authorizationChecker->isGranted($this->grantRule);
        if ($hasUser) {
            throw new AccessDeniedException('Cannot connect already registered account.');
        }

        $error = null;
        $session = $request->hasSession() ? $request->getSession() : $this->getSession();
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

        $form = $this->formFactory->create($this->registrationForm);

        if ($this->formHandler->process($request, $form, $userInformation)) {
            $event = new FormEvent($form, $request);
            $this->dispatch($event, HWIOAuthEvents::REGISTRATION_SUCCESS);

            /** @var UserInterface $user */
            $user = $form->getData();

            $this->accountConnector->connect($user, $userInformation);

            // Authenticate the user
            $this->authenticateUser($request, $user, $error->getResourceOwnerName(), $error->getAccessToken());

            if (null === $response = $event->getResponse()) {
                if ($targetPath = $this->getTargetPath($session)) {
                    $response = new RedirectResponse($targetPath);
                } else {
                    $response = new Response($this->twig->render('@HWIOAuth/Connect/registration_success.html.twig', [
                        'userInformation' => $userInformation,
                    ]));
                }
            }

            $event = new FilterUserResponseEvent($user, $request, $response);
            $this->dispatch($event, HWIOAuthEvents::REGISTRATION_COMPLETED);

            return $event->getResponse();
        }

        if ($session) {
            // reset the error in the session
            $session->set('_hwi_oauth.registration_error.'.$key, $error);
        }

        /** @var UserInterface $user */
        $user = $form->getData();

        $event = new GetResponseUserEvent($user, $request);
        $this->dispatch($event, HWIOAuthEvents::REGISTRATION_INITIALIZE);

        if ($response = $event->getResponse()) {
            return $response;
        }

        return new Response($this->twig->render('@HWIOAuth/Connect/registration.html.twig', [
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ]));
    }
}
