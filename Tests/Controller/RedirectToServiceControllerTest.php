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

use HWI\Bundle\OAuthBundle\Controller\RedirectToServiceController;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RedirectToServiceControllerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuthUtils
     */
    private $oAuthUtils;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $firewallNames = ['default'];

    /**
     * @var string
     */
    private $targetPathParameter = 'target_path';

    protected function setUp(): void
    {
        parent::setUp();

        $this->oAuthUtils = $this->createMock(OAuthUtils::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->request = Request::create('/');
        $this->request->setSession($this->session);

        $this->oAuthUtils->expects($this->any())
            ->method('getAuthorizationUrl')
            ->willReturn('http://domain.com/oauth/v2/auth')
        ;
    }

    public function test()
    {
        $controller = $this->createController();

        $response = $controller->redirectToServiceAction($this->request, 'facebook');

        $this->assertEquals('http://domain.com/oauth/v2/auth', $response->getTargetUrl());
    }

    public function testTargetPathParameter()
    {
        $this->request->attributes->set($this->targetPathParameter, '/target/path');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.target_path', '/target/path')
        ;

        $controller = $this->createController();

        $controller->redirectToServiceAction($this->request, 'facebook');
    }

    public function testUseReferer()
    {
        $this->request->headers->set('Referer', 'https://google.com');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.target_path', 'https://google.com')
        ;

        $controller = $this->createController(false, true);

        $controller->redirectToServiceAction($this->request, 'facebook');
    }

    public function testFailedUseReferer()
    {
        $this->request->headers->set('Referer', 'https://google.com');

        $this->session->expects($this->once())
            ->method('set')
            ->with('_security.default.failed_target_path', 'https://google.com')
        ;

        $controller = $this->createController(true, false);

        $controller->redirectToServiceAction($this->request, 'facebook');
    }

    public function testUnknownResourceOwner()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->oAuthUtils->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with(
                $this->isInstanceOf(Request::class),
                'unknown'
            )
            ->will($this->throwException(new \RuntimeException()))
        ;

        $controller = $this->createController();

        $controller->redirectToServiceAction($this->request, 'unknown');
    }

    private function createController(bool $failedUseReferer = false, bool $useReferer = false): RedirectToServiceController
    {
        return new RedirectToServiceController($this->oAuthUtils, $this->firewallNames, $this->targetPathParameter, $failedUseReferer, $useReferer);
    }
}
