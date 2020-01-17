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

use HWI\Bundle\OAuthBundle\Controller\LoginController;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class LoginControllerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    private $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    private $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->twig = $this->createMock(Environment::class);

        $this->router = $this->createMock(RouterInterface::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->request = Request::create('/');
        $this->request->setSession($this->session);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->authenticationUtils = new AuthenticationUtils($requestStack);
    }

    public function testLoginPage()
    {
        $this->mockAuthorizationCheck();

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig')
        ;

        $controller = $this->createController();

        $controller->connectAction($this->request);
    }

    public function testLoginPageWithoutToken()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willThrowException(new AuthenticationCredentialsNotFoundException())
        ;

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig')
        ;

        $controller = $this->createController();

        $controller->connectAction($this->request);
    }

    public function testRegistrationRedirect()
    {
        $this->request->attributes = new ParameterBag([
            $this->getAuthenticationErrorKey() => new AccountNotLinkedException(),
        ]);

        $this->mockAuthorizationCheck(false);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('hwi_oauth_connect_registration')
            ->willReturn('/')
        ;

        $controller = $this->createController();

        $controller->connectAction($this->request);
    }

    public function testRequestError()
    {
        $this->mockAuthorizationCheck();

        $authenticationException = new AuthenticationException();

        $this->request->attributes = new ParameterBag([
            $this->getAuthenticationErrorKey() => $authenticationException,
        ]);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig', ['error' => $authenticationException->getMessageKey()])
        ;

        $controller = $this->createController();

        $controller->connectAction($this->request);
    }

    public function testSessionError()
    {
        $this->mockAuthorizationCheck();

        $this->session->expects($this->once())
            ->method('has')
            ->with($this->getAuthenticationErrorKey())
            ->willReturn(true)
        ;

        $authenticationException = new AuthenticationException();

        $this->session->expects($this->once())
            ->method('get')
            ->with($this->getAuthenticationErrorKey())
            ->willReturn($authenticationException)
        ;

        $this->twig->expects($this->once())
            ->method('render')
            ->with('@HWIOAuth/Connect/login.html.twig', ['error' => $authenticationException->getMessageKey()])
        ;

        $controller = $this->createController();

        $controller->connectAction($this->request);
    }

    private function mockAuthorizationCheck($granted = true)
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn($granted)
        ;
    }

    private function getAuthenticationErrorKey(): string
    {
        return Security::AUTHENTICATION_ERROR;
    }

    private function createController(bool $connect = true, string $grantRule = 'IS_AUTHENTICATED_REMEMBERED'): LoginController
    {
        return new LoginController(
            $this->authenticationUtils,
            $this->twig,
            $this->router,
            $this->authorizationChecker,
            $this->session,
            $connect,
            $grantRule
        );
    }
}
