<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

class ConnectControllerConnectActionTest extends AbstractConnectControllerTest
{
    public function testLoginPage()
    {
        $this->container->setParameter('hwi_oauth.connect', true);

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:login.html.twig')
        ;

        $this->controller->connectAction($this->request);
    }

    public function testRegistrationRedirect()
    {
        $this->request->attributes = new ParameterBag(array(
            $this->getAuthenticationErrorKey() => $this->createAccountNotLinkedException()
        ));

        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(false)
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
            $this->getAuthenticationErrorKey() => new AccessDeniedException('You shall not pass the request.')
        ));

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:login.html.twig', array('error' => 'You shall not pass the request.'))
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

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:login.html.twig', array('error' => 'You shall not pass the session.'))
        ;

        $this->controller->connectAction($this->request);
    }
}
