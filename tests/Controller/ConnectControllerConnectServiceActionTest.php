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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ConnectControllerConnectServiceActionTest extends AbstractConnectControllerTest
{
    public function testNotEnabled(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $controller = $this->createConnectController(false);
        $controller->connectServiceAction($this->request, 'facebook');
    }

    public function testAlreadyConnected(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot connect an account.');

        $this->mockAuthorizationCheck(false);

        $controller = $this->createConnectController();
        $controller->connectServiceAction($this->request, 'facebook');
    }

    public function testUnknownResourceOwner(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->mockAuthorizationCheck();

        $controller = $this->createConnectController(true, true);
        $controller->connectServiceAction($this->request, 'unknown');
    }

    public function testConnectConfirm(): void
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

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_INITIALIZE);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_confirm.html.twig')
        ;

        $controller = $this->createConnectController();
        $controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectSuccess(): void
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

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_CONFIRMED],
                [$this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::CONNECT_COMPLETED]
            );

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_success.html.twig')
        ;

        $controller = $this->createConnectController();
        $controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectNoConfirmation(): void
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

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(GetResponseUserEvent::class), HWIOAuthEvents::CONNECT_CONFIRMED],
                [$this->isInstanceOf(FilterUserResponseEvent::class), HWIOAuthEvents::CONNECT_COMPLETED]
            );

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/connect_success.html.twig')
        ;

        $controller = $this->createConnectController(true, false);
        $controller->connectServiceAction($this->request, 'facebook');
    }

    public function testResourceOwnerHandle(): void
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

        $controller = $this->createConnectController();
        $controller->connectServiceAction($this->request, 'facebook');
    }
}
