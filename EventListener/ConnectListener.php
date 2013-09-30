<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\EventListener;

use HWI\Bundle\OAuthBundle\Event\GetResponseConnectEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotConnectedException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ConnectListener implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param UrlGeneratorInterface    $router
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(UrlGeneratorInterface $router, SecurityContextInterface $securityContext)
    {
        $this->router = $router;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            HWIOAuthEvents::USER_CONNECT_INITIALIZE => 'initialize',
            HWIOAuthEvents::USER_CONNECT_VALIDATE   => 'validate',
            HWIOAuthEvents::USER_CONNECT_COMPLETE   => 'finish',
        );
    }

    /**
     * @param GetResponseConnectEvent $event
     */
    public function initialize(GetResponseConnectEvent $event)
    {
        /** @var $error OAuthAwareExceptionInterface */
        $error   = $event['error'];
        $hasUser = $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
        if (!$hasUser && $error instanceof AccountNotLinkedException) {
            $key     = time();
            $request = $event->getSubject();
            $request->getSession()->set('_hwi_oauth.registration_error.'.$key, $error);

            $event->setResponse(new RedirectResponse($this->router->generate('hwi_oauth_connect_registration', array('key' => $key))));
        } elseif ($hasUser && $error instanceof AccountNotConnectedException) {
            $event->setResponse(new RedirectResponse($this->router->generate('hwi_oauth_connect_service', array('service' => $error->getResourceOwnerName()))));
        }
    }
}