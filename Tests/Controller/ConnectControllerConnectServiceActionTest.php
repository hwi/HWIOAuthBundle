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
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Form\FormInterface;

class ConnectControllerConnectServiceActionTest extends AbstractConnectControllerTest
{
    public function testNotEnabled()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->container->setParameter('hwi_oauth.connect', false);

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testAlreadyConnected()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot connect an account.');

        $this->mockAuthorizationCheck(false);

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testUnknownResourceOwner()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->container->setParameter('hwi_oauth.firewall_names', []);

        $this->mockAuthorizationCheck();

        $this->controller->connectServiceAction($this->request, 'unknown');
    }

    public function testConnectConfirm()
    {
        $key = time();

        $this->request->query->set('key', $key);

        $this->mockAuthorizationCheck();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn([])
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $form = $this->createMock(FormInterface::class);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $this->eventDispatcher->expects($this->once())->method('dispatch');

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_INITIALIZE);
        } else {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(HWIOAuthEvents::CONNECT_INITIALIZE);
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_confirm.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectSuccess()
    {
        $key = time();

        $this->request->query->set('key', $key);
        $this->request->setMethod('POST');

        $this->mockAuthorizationCheck();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn([])
        ;

        $form = $this->createMock(FormInterface::class);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch');
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_CONFIRMED);
            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with($this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::CONNECT_COMPLETED);
        } else {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(HWIOAuthEvents::CONNECT_CONFIRMED);
            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with(HWIOAuthEvents::CONNECT_COMPLETED);
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_success.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectNoConfirmation()
    {
        $key = time();

        $this->request->query->set('key', $key);
        $this->container->setParameter('hwi_oauth.connect.confirmation', false);

        $this->mockAuthorizationCheck();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn([])
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch');

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_CONFIRMED)
            ;
            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with($this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::CONNECT_COMPLETED)
            ;
        } else {
            $this->eventDispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(HWIOAuthEvents::CONNECT_CONFIRMED)
            ;
            $this->eventDispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with(HWIOAuthEvents::CONNECT_COMPLETED)
            ;
        }

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_success.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testResourceOwnerHandle()
    {
        $key = time();

        $this->request->query->set('key', $key);

        $this->mockAuthorizationCheck();

        $this->resourceOwner->expects($this->once())
            ->method('handles')
            ->willReturn(true)
        ;

        $this->resourceOwner->expects($this->once())
            ->method('getAccessToken')
            ->willReturn([])
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $form = $this->createMock(FormInterface::class);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_confirm.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }
}
