<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final class ConnectControllerRegistrationActionTest extends AbstractConnectControllerTest
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot register an account.');

        $key = (string) time();

        $this->mockAuthorizationCheck(false);

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn(new \Exception())
        ;

        $this->session->expects($this->once())
            ->method('remove')
            ->with('_hwi_oauth.registration_error.'.$key)
        ;

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, $key);
    }

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

        $this->eventDispatcher->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(FormEvent::class), HWIOAuthEvents::REGISTRATION_SUCCESS],
                [$this->isInstanceOf(InteractiveLoginEvent::class), SecurityEvents::INTERACTIVE_LOGIN],
                [$this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::REGISTRATION_COMPLETED]
            );

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/registration_success.html.twig')
        ;

        $controller = $this->createConnectController();
        $controller->registrationAction($this->request, $key);
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
}
