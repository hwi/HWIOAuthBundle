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

namespace HWI\Bundle\OAuthBundle\Tests\Controller\Connect;

use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Twig\Environment;

abstract class AbstractConnectControllerTest extends TestCase
{
    protected ResourceOwnerMapLocator $resourceOwnerMapLocator;
    protected Request $request;
    protected OAuthUtils $oAuthUtils;

    /**
     * @var MockObject&AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var MockObject&TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject&Environment
     */
    protected $twig;

    /**
     * @var MockObject&RouterInterface
     */
    protected $router;

    /**
     * @var MockObject&ResourceOwnerMapInterface
     */
    protected $resourceOwnerMap;

    /**
     * @var MockObject&ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @var MockObject&AccountConnectorInterface
     */
    protected $accountConnector;

    /**
     * @var MockObject&UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var MockObject&FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var MockObject&SessionInterface
     */
    protected $session;

    /**
     * @var RegistrationFormHandlerInterface&MockObject
     */
    protected $registrationFormHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->accountConnector = $this->createMock(AccountConnectorInterface::class);
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->registrationFormHandler = $this->createMock(RegistrationFormHandlerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->twig = $this->createMock(Environment::class);
        $this->twig->expects($this->any())
            ->method('render')
            ->willReturn('')
        ;

        $this->router = $this->createMock(RouterInterface::class);

        $this->resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->resourceOwner->expects($this->any())
            ->method('getUserInformation')
            ->willReturn(new CustomUserResponse())
        ;
        $this->resourceOwnerMap = $this->createMock(ResourceOwnerMapInterface::class);
        $this->resourceOwnerMap->expects($this->any())
            ->method('getResourceOwnerByName')
            ->willReturnCallback(function ($owner) {
                return 'facebook' === $owner ? $this->resourceOwner : null;
            });

        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils->method('generateUri')
            ->willReturn('https://fake-url');

        $this->oAuthUtils = new OAuthUtils(
            $httpUtils,
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createFirewallMapMock(),
            true,
            'IS_AUTHENTICATED_REMEMBERED'
        );

        $this->resourceOwnerMapLocator = new ResourceOwnerMapLocator();
        $this->resourceOwnerMapLocator->set('default', $this->resourceOwnerMap);

        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->request = Request::create('/');
        $this->request->setSession($this->session);
    }

    protected function createAccountNotLinkedException(): AccountNotLinkedException
    {
        $accountNotLinked = new AccountNotLinkedException();
        $accountNotLinked->setResourceOwnerName('facebook');
        $accountNotLinked->setToken(new CustomOAuthToken());

        return $accountNotLinked;
    }

    protected function mockAuthorizationCheck(bool $granted = true): void
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn($granted)
        ;
    }

    protected function createFirewallMapMock(): FirewallMap
    {
        $firewallMap = $this->createMock(FirewallMap::class);

        $firewallMap
            ->expects($this->any())
            ->method('getFirewallConfig')
            ->willReturn(new FirewallConfig('main', '/path/a'))
        ;

        return $firewallMap;
    }
}
