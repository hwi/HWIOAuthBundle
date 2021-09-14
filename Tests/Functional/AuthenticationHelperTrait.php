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

namespace HWI\Bundle\OAuthBundle\Tests\Functional;

use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait AuthenticationHelperTrait
{
    protected function getSession(KernelBrowser $client): ?SessionInterface
    {
        /** @var RequestStack $requestStack */
        $requestStack = $client->getContainer()->get('request_stack');

        $session = null;
        if (method_exists($requestStack, 'getSession')) {
            try {
                $session = $requestStack->getSession();
            } catch (SessionNotFoundException $e) {
                // Ignore & fallback to service
            }
        } elseif ((null !== $request = $requestStack->getCurrentRequest()) && $request->hasSession()) {
            $session = $request->getSession();
        }

        if (!$session) {
            $session = $client->getContainer()->get($client->getContainer()->has('session') ? 'session' : 'session_factory');
        }

        return $session;
    }

    protected function logIn(KernelBrowser $client, ?SessionInterface $session): void
    {
        $firewallContext = 'hwi_context';

        if (method_exists($client, 'loginUser')) {
            $client->loginUser(new User(), $firewallContext);

            return;
        }

        if (null === $session) {
            throw new \RuntimeException('Session object must be passed for testing Symfony <5.3!');
        }

        $session->set('_security_'.$firewallContext, serialize(new CustomOAuthToken()));

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }
}
