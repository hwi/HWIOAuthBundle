<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Http\Authentication;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AuthenticationFailureHandlerTest extends TestCase
{
    public function testRedirectsToLoginPathWhenNotSet()
    {
        $request = Request::create('/login');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new AuthenticationFailureHandler(
            $requestStack,
            $router = $this->createMock(RouterInterface::class),
            false
        );

        $router->expects($this->never())
            ->method('generate');

        $this->assertInstanceOf(
            Response::class,
            $handler->onAuthenticationFailure($request, new AuthenticationException())
        );
    }

    public function testRedirectsToLoginRoute()
    {
        $request = Request::create('/login');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new AuthenticationFailureHandler(
            $requestStack,
            $router = $this->createMock(RouterInterface::class),
            false,
        );
        $handler->setOptions(['login_path' => 'route_name']);

        $router->expects($this->once())
            ->method('generate')
            ->with('route_name', ['error' => 'An authentication exception occurred.'], 1)
            ->willReturn('/path');

        $this->assertInstanceOf(
            Response::class,
            $handler->onAuthenticationFailure($request, new AuthenticationException())
        );
    }

    public function testDoesNothingWhenConnectIsDisabled()
    {
        $request = Request::create('/login');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new AuthenticationFailureHandler(
            $requestStack,
            $router = $this->createMock(RouterInterface::class),
            false
        );

        $router->expects($this->never())
            ->method('generate');

        $this->assertInstanceOf(
            Response::class,
            $handler->onAuthenticationFailure($request, new AuthenticationException())
        );
    }

    public function testDoesNothingWhenExceptionIsNotOAuthOne()
    {
        $request = Request::create('/login');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new AuthenticationFailureHandler(
            $requestStack,
            $router = $this->createMock(RouterInterface::class),
            true
        );

        $router->expects($this->never())
            ->method('generate');

        $this->assertInstanceOf(
            Response::class,
            $handler->onAuthenticationFailure($request, new AuthenticationException())
        );
    }

    public function testRedirectsToRegistrationPage()
    {
        $request = Request::create('/login');
        $request->setSession($this->createMock(SessionInterface::class));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $handler = new AuthenticationFailureHandler(
            $requestStack,
            $router = $this->createMock(RouterInterface::class),
            true
        );

        $router->expects($this->once())
            ->method('generate')
            ->with('hwi_oauth_connect_registration', ['key' => $key = time()])
            ->willReturn('https://localhost/register/'.$key);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $handler->onAuthenticationFailure($request, new AuthenticationException(previous: new AccountNotLinkedException()))
        );
    }
}
