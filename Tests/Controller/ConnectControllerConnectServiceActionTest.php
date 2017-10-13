<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Symfony\Component\Form\FormInterface;

class ConnectControllerConnectServiceActionTest extends AbstractConnectControllerTest
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotEnabled()
    {
        $this->container->setParameter('hwi_oauth.connect', false);

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Cannot connect an account.
     */
    public function testAlreadyConnected()
    {
        $this->mockAuthorizationCheck(false);

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testUnknownResourceOwner()
    {
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
            ->willReturn(array())
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->eventDispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(HWIOAuthEvents::CONNECT_INITIALIZE)
        ;

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
            ->willReturn(array())
        ;

        $form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->eventDispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(HWIOAuthEvents::CONNECT_CONFIRMED)
        ;
        $this->eventDispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(HWIOAuthEvents::CONNECT_COMPLETED)
        ;

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
            ->willReturn(array())
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->eventDispatcher->expects($this->exactly(2))->method('dispatch');
        $this->eventDispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(HWIOAuthEvents::CONNECT_CONFIRMED)
        ;
        $this->eventDispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(HWIOAuthEvents::CONNECT_COMPLETED)
        ;

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
            ->willReturn(array())
        ;

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
