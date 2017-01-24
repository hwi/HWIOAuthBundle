<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Controller\ConnectController;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

abstract class AbstractConnectControllerTest extends TestCase
{
    /**
     * @var ConnectController
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    protected $templating;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceOwnerMap
     */
    protected $resourceOwnerMap;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccountConnectorInterface
     */
    protected $accountConnector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OAuthUtils
     */
    protected $oAuthUtils;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected $session;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->setParameter('hwi_oauth.templating.engine', 'twig');
        $this->container->setParameter('hwi_oauth.connect', true);
        $this->container->setParameter('hwi_oauth.firewall_names', array('default'));
        $this->container->setParameter('hwi_oauth.connect.confirmation', true);
        $this->container->setParameter('hwi_oauth.grant_rule', 'IS_AUTHENTICATED_REMEMBERED');

        $this->authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('security.authorization_checker', $this->authorizationChecker);

        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('security.token_storage', $this->tokenStorage);

        $this->templating = $this->getMockBuilder(EngineInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('templating', $this->templating);

        $this->router = $this->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('router', $this->router);

        $this->resourceOwner = $this->getMockBuilder(ResourceOwnerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwner->expects($this->any())
            ->method('getUserInformation')
            ->willReturn(new CustomUserResponse())
        ;
        $this->resourceOwnerMap = $this->getMockBuilder(ResourceOwnerMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwnerMap->expects($this->any())
            ->method('getResourceOwnerByName')
            ->withAnyParameters()
            ->willReturn($this->resourceOwner);
        $this->container->set('hwi_oauth.resource_ownermap.default', $this->resourceOwnerMap);

        $this->accountConnector = $this->getMockBuilder(AccountConnectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.account.connector', $this->accountConnector);

        $this->oAuthUtils = $this->getMockBuilder(OAuthUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.security.oauth_utils', $this->oAuthUtils);

        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.user_checker', $this->userChecker);

        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('event_dispatcher', $this->eventDispatcher);

        $this->formFactory = $this->getMockBuilder(FormFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('form.factory', $this->formFactory);

        $this->session = $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = Request::create('/');
        $this->request->setSession($this->session);

        $this->controller = new ConnectController();
        $this->controller->setContainer($this->container);
    }

    /**
     * @return AccountNotLinkedException
     */
    protected function createAccountNotLinkedException()
    {
        $accountNotLinked = new AccountNotLinkedException();
        $accountNotLinked->setResourceOwnerName('facebook');
        $token = new CustomOAuthToken();
        $accountNotLinked->setToken($token);

        return $accountNotLinked;
    }

    /**
     * @return string
     */
    protected function getAuthenticationErrorKey()
    {
        return Security::AUTHENTICATION_ERROR;
    }

    protected function mockAuthorizationCheck($granted = true)
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn($granted)
        ;
    }
}
