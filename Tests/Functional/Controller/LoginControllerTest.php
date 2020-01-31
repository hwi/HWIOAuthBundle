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

namespace HWI\Bundle\OAuthBundle\Tests\Functional\Controller;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use HWI\Bundle\OAuthBundle\Tests\Functional\WebTestCase;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;

/**
 * uses FOSUserBundle which itself contains lots of deprecations.
 *
 * @group legacy
 */
final class LoginControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(\FOS\UserBundle\Model\User::class)) {
            $this->markTestSkipped('FOSUserBundle not installed.');
        }
    }

    public function testLoginPage(): void
    {
        $client = static::createClient();
        $httpClient = $this->prophesize(ClientInterface::class);
        $client->getContainer()->set(ClientInterface::class, $httpClient->reveal());

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('google', $crawler->filter('a')->text(), $response->getContent());
    }

    public function testRedirectingToRegistrationFormWithError(): void
    {
        $client = static::createClient();
        $session = $client->getContainer()->get('session');
        $session->set(Security::AUTHENTICATION_ERROR, new AccountNotLinkedException());

        $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame(0, strpos($response->headers->get('Location'), '/connect/registration/'), $response->headers->get('Location'));
    }

    public function testLoginPageWithError(): void
    {
        $client = static::createClient();
        $httpClient = $this->prophesize(ClientInterface::class);
        $client->getContainer()->set(ClientInterface::class, $httpClient->reveal());
        $session = $client->getContainer()->get('session');

        $this->logIn($client, $session);
        $exception = new UsernameNotFoundException();
        $session->set(Security::AUTHENTICATION_ERROR, $exception);

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame($exception->getMessageKey(), $crawler->filter('span')->text(), $response->getContent());
    }

    private function logIn($client, SessionInterface $session): void
    {
        $firewallContext = 'hwi_context';

        $token = new CustomOAuthToken();
        $session->set('_security_'.$firewallContext, serialize($token));

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
