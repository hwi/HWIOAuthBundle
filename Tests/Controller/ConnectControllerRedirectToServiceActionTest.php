<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;


class ConnectConnectControllerRedirectToServiceActionTest extends AbstractConnectControllerTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('hwi_oauth.target_path_parameter', null);
        $this->container->setParameter('hwi_oauth.use_referer', false);

        $this->oAuthUtils->expects($this->any())
            ->method('getAuthorizationUrl')
            ->willReturn('http://domain.com/oauth/v2/auth')
        ;
    }

    public function test()
    {
        $response = $this->controller->redirectToServiceAction($this->request, 'facebook');

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://domain.com/oauth/v2/auth', $response->getTargetUrl());
    }

    public function testTargetPathParameter()
    {
        $this->container->setParameter('hwi_oauth.target_path_parameter', 'target_path');
        $this->request->attributes->set('target_path', '/target/path');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.target_path', '/target/path')
        ;

        $this->controller->redirectToServiceAction($this->request, 'facebook');
    }

    public function testUseReferer()
    {
        $this->container->setParameter('hwi_oauth.use_referer', true);
        $this->request->headers->set('Referer', 'https://google.com');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.target_path', 'https://google.com')
        ;

        $this->controller->redirectToServiceAction($this->request, 'facebook');
    }
}
