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

use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConnectControllerConnectActionTest extends AbstractConnectControllerTest
{
    public function testLoginPage()
    {
        $this->container->setParameter('hwi_oauth.connect', true);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig')
        ;

        $this->controller->connectAction($this->request);
    }

    public function testRegistrationRedirect()
    {
        $this->request->attributes = new ParameterBag(array(
            $this->getAuthenticationErrorKey() => $this->createAccountNotLinkedException(),
        ));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->mockAuthorizationCheck(false);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('hwi_oauth_connect_registration')
            ->willReturn('/')
        ;

        $this->controller->connectAction($this->request);
    }

    public function testRegistrationRedirectWithoutTokenStorage()
    {
        $this->request->attributes = new ParameterBag(array(
            $this->getAuthenticationErrorKey() => $this->createAccountNotLinkedException(),
        ));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted')
        ;

        $this->router->expects($this->once())
            ->method('generate')
            ->with('hwi_oauth_connect_registration')
            ->willReturn('/')
        ;

        $this->controller->connectAction($this->request);
    }

    public function testRequestError()
    {
        $this->request->attributes = new ParameterBag(array(
            $this->getAuthenticationErrorKey() => new AccessDeniedException('You shall not pass the request.'),
        ));

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig', array('error' => 'You shall not pass the request.'))
        ;

        $this->controller->connectAction($this->request);
    }

    public function testSessionError()
    {
        $this->session->expects($this->once())
            ->method('has')
            ->with($this->getAuthenticationErrorKey())
            ->willReturn(true)
        ;

        $this->session->expects($this->once())
            ->method('get')
            ->with($this->getAuthenticationErrorKey())
            ->willReturn(new AccessDeniedException('You shall not pass the session.'))
        ;

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig', array('error' => 'You shall not pass the session.'))
        ;

        $this->controller->connectAction($this->request);
    }
}
