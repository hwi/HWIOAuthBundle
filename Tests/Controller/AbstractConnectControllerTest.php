<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\Controller\ConnectController;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class AbstractConnectControllerTest extends TestCase
{
    /**
     * @var ConnectController
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templating;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceOwnerMap;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceOwner;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountConnector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $oAuthUtils;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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

        if (interface_exists('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')) {
            $this->authorizationChecker = $this->getMockBuilder('\Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')
                ->disableOriginalConstructor()
                ->getMock();
            $this->container->set('security.authorization_checker', $this->authorizationChecker);

            $this->tokenStorage = $this->getMockBuilder('\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
                ->disableOriginalConstructor()
                ->getMock();
            $this->container->set('security.token_storage', $this->tokenStorage);
        } else {
            $this->securityContext = $this->getMockBuilder('\Symfony\Component\Security\Core\SecurityContextInterface')
                ->disableOriginalConstructor()
                ->getMock();
            $this->container->set('security.context', $this->securityContext);
        }

        $this->templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('templating', $this->templating);

        $this->router = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('router', $this->router);

        $this->resourceOwner = $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwner->expects($this->any())
            ->method('getUserInformation')
            ->willReturn(new CustomUserResponse())
        ;
        $this->resourceOwnerMap = $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceOwnerMap->expects($this->any())
            ->method('getResourceOwnerByName')
            ->withAnyParameters()
            ->willReturn($this->resourceOwner);
        $this->container->set('hwi_oauth.resource_ownermap.default', $this->resourceOwnerMap);

        $this->accountConnector = $this->getMockBuilder('HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.account.connector', $this->accountConnector);

        $this->oAuthUtils = $this->getMockBuilder('HWI\Bundle\OAuthBundle\Security\OAuthUtils')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.security.oauth_utils', $this->oAuthUtils);

        $this->userChecker = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserCheckerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('hwi_oauth.user_checker', $this->userChecker);

        $this->eventDispatcher = $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('event_dispatcher', $this->eventDispatcher);

        $this->formFactory = $this->getMockBuilder('\Symfony\Component\Form\FormFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->set('form.factory', $this->formFactory);

        $this->session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
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
     * Symfony <2.6 BC. To be removed.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthorizationChecker()
    {
        return $this->authorizationChecker ?: $this->securityContext;
    }

    /**
     * Symfony <2.6 BC. To be removed.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTokenStorage()
    {
        return $this->tokenStorage ?: $this->securityContext;
    }

    /**
     * Symfony <2.6 BC. To be removed.
     *
     * @return string
     */
    protected function getAuthenticationErrorKey()
    {
        return class_exists('Symfony\Component\Security\Core\Security')
            ? Security::AUTHENTICATION_ERROR : SecurityContextInterface::AUTHENTICATION_ERROR;
    }
}
