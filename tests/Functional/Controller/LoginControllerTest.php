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
use HWI\Bundle\OAuthBundle\Tests\Functional\AuthenticationHelperTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class LoginControllerTest extends WebTestCase
{
    use AuthenticationHelperTrait;

    public function testLoginPage(): void
    {
        $client = self::createClient();
        $client->getContainer()->set('hwi_oauth.http_client', $this->createMock(HttpClientInterface::class));

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('google', $crawler->filter('a:nth-child(1)')->text(), $response->getContent());
        $this->assertSame('yahoo', $crawler->filter('a:nth-child(3)')->text(), $response->getContent());
        $this->assertSame('oauth1', $crawler->filter('a:nth-child(5)')->text(), $response->getContent());
        $this->assertSame('oauth2', $crawler->filter('a:nth-child(7)')->text(), $response->getContent());
        $this->assertSame('custom', $crawler->filter('a:nth-child(9)')->text(), $response->getContent());
    }

    public function testRedirectingToRegistrationFormWithError(): void
    {
        $client = self::createClient();

        $session = $this->getSession($client);
        $session->set($this->getSecurityErrorKey(), new AccountNotLinkedException());

        $this->saveSession($client, $session);

        $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame(0, strpos($response->headers->get('Location'), '/connect/registration/'), $response->headers->get('Location'));
    }

    public function testLoginPageWithError(): void
    {
        $httpClient = new MockHttpClient();

        $client = self::createClient();
        $client->getContainer()->set('hwi_oauth.http_client', $httpClient);

        $exception = new UserNotFoundException();

        $session = $this->getSession($client);
        $session->set($this->getSecurityErrorKey(), $exception);

        $this->logIn($client, $session);

        $crawler = $client->request('GET', '/login_hwi/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame($exception->getMessageKey(), $crawler->filter('span')->text(), $response->getContent());
    }

    private function getSecurityErrorKey(): string
    {
        return SecurityRequestAttributes::AUTHENTICATION_ERROR;
    }
}
