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

use FOS\UserBundle\Form\Factory\FactoryInterface;
use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ConnectControllerRegistrationActionTest extends AbstractConnectControllerTest
{
    public function testNotEnabled()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->container->setParameter('hwi_oauth.connect', false);

        $this->controller->registrationAction($this->request, time());
    }

    public function testAlreadyConnected()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot connect already registered account.');

        $this->mockAuthorizationCheck();

        $this->controller->registrationAction($this->request, time());
    }

    public function testCannotRegisterBadError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot register an account.');

        $key = time();

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

        $this->controller->registrationAction($this->request, $key);
    }

    public function testFailedProcess()
    {
        $key = time();

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

        $registrationFormHandler = $this->createMock(RegistrationFormHandlerInterface::class);
        $registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(false)
        ;
        $this->container->set('hwi_oauth.registration.form.handler', $registrationFormHandler);

        $this->eventDispatcher->expects($this->once())->method('dispatch');

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::REGISTRATION_INITIALIZE);
        } else {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(HWIOAuthEvents::REGISTRATION_INITIALIZE);
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/registration.html.twig')
        ;

        $this->controller->registrationAction($this->request, $key);
    }

    public function test()
    {
        $key = time();

        $this->mockAuthorizationCheck(false);

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
        ;

        $this->makeRegistrationForm();

        $registrationFormHandler = $this->createMock(RegistrationFormHandlerInterface::class);
        $registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(true)
        ;
        $this->container->set('hwi_oauth.registration.form.handler', $registrationFormHandler);

        $this->accountConnector->expects($this->once())
            ->method('connect')
        ;

        $this->eventDispatcher->expects($this->exactly(3))->method('dispatch');

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with($this->isInstanceOf(FormEvent::class), HWIOAuthEvents::REGISTRATION_SUCCESS)
            ;

            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with($this->isInstanceOf(InteractiveLoginEvent::class), SecurityEvents::INTERACTIVE_LOGIN)
            ;

            $this->eventDispatcher->expects($this->at(2))
                ->method('dispatch')
                ->with($this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::REGISTRATION_COMPLETED)
            ;
        } else {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(HWIOAuthEvents::REGISTRATION_SUCCESS)
            ;

            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with(SecurityEvents::INTERACTIVE_LOGIN)
            ;

            $this->eventDispatcher->expects($this->at(2))
                ->method('dispatch')
                ->with(HWIOAuthEvents::REGISTRATION_COMPLETED)
            ;
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/registration_success.html.twig')
        ;

        $this->controller->registrationAction($this->request, $key);
    }

    private function makeRegistrationForm()
    {
        $registrationForm = $this->createMock(Form::class);
        $registrationForm->expects($this->any())
            ->method('getData')
            ->willReturn(new User());

        $this->container->setParameter('hwi_oauth.fosub_enabled', true);

        if (!class_exists(FactoryInterface::class)) {
            $this->markTestSkipped('FOSUserBundle not installed.');
        }

        $registrationFormFactory = $this->createMock(FactoryInterface::class);
        $registrationFormFactory->expects($this->any())
            ->method('createForm')
            ->willReturn($registrationForm);

        $this->container->set('hwi_oauth.registration.form.factory', $registrationFormFactory);
    }
}
