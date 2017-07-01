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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ConnectControllerRedirectToServiceActionTest extends AbstractConnectControllerTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('hwi_oauth.target_path_parameter', null);
        $this->container->setParameter('hwi_oauth.use_referer', false);
        $this->container->setParameter('hwi_oauth.failed_use_referer', false);

        $this->oAuthUtils->expects($this->any())
            ->method('getAuthorizationUrl')
            ->willReturn('http://domain.com/oauth/v2/auth')
        ;
    }

    public function test()
    {
        $response = $this->controller->redirectToServiceAction($this->request, 'facebook');

        $this->assertInstanceOf(RedirectResponse::class, $response);
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

    public function testFailedUseReferer()
    {
        $this->container->setParameter('hwi_oauth.failed_use_referer', true);
        $this->request->headers->set('Referer', 'https://google.com');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.failed_target_path', 'https://google.com')
        ;

        $this->controller->redirectToServiceAction($this->request, 'facebook');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testUnknownResourceOwner()
    {
        $this->oAuthUtils->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with(
                $this->isInstanceOf(Request::class),
                'unknown'
            )
            ->will($this->throwException(new \RuntimeException()))
        ;

        $this->controller->redirectToServiceAction($this->request, 'unknown');
    }
}
