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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait AuthenticationHelperTrait
{
    protected function getSession(KernelBrowser $client): SessionInterface
    {
        if ($client->getContainer()->has('session')) {
            /** @var SessionInterface $session */
            $session = $client->getContainer()->get('session');
        } else {
            $session = $client->getContainer()->get('session.factory')->createSession();
        }

        return $session;
    }

    protected function saveSession(KernelBrowser $client, SessionInterface $session): void
    {
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    protected function logIn(KernelBrowser $client, SessionInterface $session): void
    {
        $session->set('_security_hwi_context', serialize(CustomOAuthToken::createLoggedIn()));

        $this->saveSession($client, $session);
    }
}
