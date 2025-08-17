<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Controller\Connect;

use Exception;
use HWI\Bundle\OAuthBundle\Controller\Connect\RegisterController;
use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Tests\App\Form\RegistrationFormType;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final class RegistrationControllerTest extends AbstractConnectControllerTestCase
{
    public function testNotEnabled(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $controller = $this->createConnectController(false);
        $controller->registrationAction($this->request, (string) time());
    }

    public function testAlreadyConnected(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot connect already registered account.');

        $this->mockAuthorizationCheck();

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, (string) time());
    }

    public function testCannotRegisterBadError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot register an account.');

        $key = (string) time();

        $this->mockAuthorizationCheck(false);

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn(new Exception())
        ;

        $this->session->expects($this->once())
            ->method('remove')
            ->with('_hwi_oauth.registration_error.'.$key)
        ;

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, $key);
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testFailedProcess(): void
    {
        $key = (string) time();

        $this->mockAuthorizationCheck(false);

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
        ;

        $this->session->expects($this->once())
            ->method('remove')
            ->with('_hwi_oauth.registration_error.'.$key)
        ;

        $this->makeRegistrationForm();

        $this->registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::REGISTRATION_INITIALIZE);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/registration.html.twig')
        ;

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, $key);
    }

    public function testProcessWorks(): void
    {
        $key = (string) time();

        $this->mockAuthorizationCheck(false);

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
        ;

        $this->makeRegistrationForm();

        $this->registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $this->accountConnector->expects($this->once())
            ->method('connect')
        ;

        $capturedDispatches = [];
        $this->eventDispatcher->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function ($event, $eventName) use (&$capturedDispatches) {
                $capturedDispatches[] = [$event, $eventName];

                return $event;
            });

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/registration_success.html.twig')
        ;

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, $key);

        $this->assertCount(3, $capturedDispatches);
        $this->assertInstanceOf(FormEvent::class, $capturedDispatches[0][0]);
        $this->assertSame(HWIOAuthEvents::REGISTRATION_SUCCESS, $capturedDispatches[0][1]);
        $this->assertInstanceOf(InteractiveLoginEvent::class, $capturedDispatches[1][0]);
        $this->assertSame(SecurityEvents::INTERACTIVE_LOGIN, $capturedDispatches[1][1]);
        $this->assertInstanceOf(FilterUserResponseEvent::class, $capturedDispatches[2][0]);
        $this->assertSame(HWIOAuthEvents::REGISTRATION_COMPLETED, $capturedDispatches[2][1]);
    }

    private function makeRegistrationForm(): void
    {
        $registrationForm = $this->createMock(Form::class);
        $registrationForm->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(new User());

        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($registrationForm);
    }

    private function createConnectController(
        bool $connectEnabled = true,
    ): RegisterController {
        return new RegisterController(
            $this->resourceOwnerMapLocator,
            $this->createMock(RequestStack::class),
            $this->eventDispatcher,
            $this->tokenStorage,
            $this->userChecker,
            $this->authorizationChecker,
            $this->formFactory,
            $this->twig,
            'IS_AUTHENTICATED_REMEMBERED',
            RegistrationFormType::class,
            $connectEnabled ? $this->accountConnector : null,
            $connectEnabled ? $this->registrationFormHandler : null
        );
    }
}
