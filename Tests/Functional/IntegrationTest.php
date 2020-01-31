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

use Prophecy\Argument;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class IntegrationTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\FOS\UserBundle\Model\User::class)) {
            $this->markTestSkipped('FOSUserBundle not installed.');
        }
    }

    public function testRequestRedirect(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame('http://localhost/login', $response->headers->get('Location'));

        $crawler = $client->request('GET', $response->headers->get('Location'));

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode(), 'No landing, got redirect to '.$response->headers->get('Location'));

        $client->disableReboot();
        $client->getContainer()->set(ClientInterface::class, $this->prophesize(ClientInterface::class)->reveal());

        $client->click($crawler->selectLink('Login')->link());

        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $expectedRedirectUrl = 'https://accounts.google.com/o/oauth2/auth?'
            .http_build_query([
                'response_type' => 'code',
                'client_id' => 'google_client_id',
                'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
                'redirect_uri' => 'http://localhost/check-login/google',
            ]);
        $this->assertSame($expectedRedirectUrl, $response->headers->get('Location'));
    }

    public function testRequestCheck(): void
    {
        $redirectLoginFromService = 'http://localhost/check-login/google?'
            .http_build_query([
                'code' => 'sOmeRand0m-code',
                'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
                'authuser' => '0',
                'session_state' => 'abcde123456789..8787',
                'prompt' => 'none',
            ]);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()
            ->willReturn(json_encode(['access_token' => 'valid-access-token']));

        $httpClient = $this->prophesize(ClientInterface::class);
        $httpClient->sendRequest(Argument::type(RequestInterface::class))
            ->shouldBeCalled()
            ->willReturn($response->reveal());

        $client = static::createClient();
        $client->disableReboot();
        $client->getContainer()->set(ClientInterface::class, $httpClient->reveal());

        $client->request('GET', $redirectLoginFromService);

        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertSame('http://localhost/', $response->headers->get('Location'));
    }
}
