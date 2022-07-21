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

namespace HWI\Bundle\OAuthBundle\Tests\Functional\Security\Http\Firewall;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Tests\App\AppKernel;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use HWI\Bundle\OAuthBundle\Tests\Functional\AuthenticationHelperTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RefreshTokenListenerTest extends WebTestCase
{
    use AuthenticationHelperTrait;

    private string $tokenResponse = <<<json
{
    "access_token": "valid-access-token",
    "refresh_token": "valid-refresh-token",
    "expires_in": 666
}
json;

    private string $userResponse = <<<json
{
    "response": {
        "user": {
            "id": "1",
            "firstName": "bar",
            "lastName": "foo"
        }
    }
}
json;

    private MockHttpClient $httpClient;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new MockHttpClient([
            new MockResponse($this->tokenResponse, [
                'response_headers' => ['content-type' => 'application/json'],
            ]),
            new MockResponse($this->userResponse, [
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $this->client = self::createClient();
        $this->client->getContainer()->set('hwi_oauth.http_client', $this->httpClient);
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testExpiredTokenWillNotBeRefreshed(): void
    {
        // refresh_on_expire not set
        $session = $this->createExpiredTokenAndStoreToSession('google');

        $this->client->request('GET', '/');

        $this->assertAmountOfHttpCalls(0);

        $this->assertResponseIsSuccessful();

        $securityContext = $session->get('_security_hwi_context');

        $this->assertNotNull($securityContext);
        $newToken = unserialize($securityContext);
        $this->assertInstanceOf(OAuthToken::class, $newToken);
        $this->assertTrue($newToken->isExpired());
        // same old expired token
        $this->assertEquals(1000, $newToken->getCreatedAt());
    }

    public function testExpiredTokenWillBeRefreshed(): void
    {
        // refresh_on_expire: true
        $session = $this->createExpiredTokenAndStoreToSession('yahoo');

        $this->client->request('GET', '/');

        $this->assertAmountOfHttpCalls(2);

        $this->assertResponseIsSuccessful();

        $securityContext = $session->get('_security_hwi_context');

        $this->assertNotNull($securityContext);
        $newToken = unserialize($securityContext);
        $this->assertInstanceOf(OAuthToken::class, $newToken);
        $this->assertFalse($newToken->isExpired());
    }

    private function createExpiredTokenAndStoreToSession(string $resourceOwnerName): SessionInterface
    {
        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => 666,
            'oauth_token_secret' => 'secret',
        ];

        $user = new User();
        $oauthToken = new OAuthToken($expectedToken, $user->getRoles());
        $oauthToken->setUser($user);
        $oauthToken->setResourceOwnerName($resourceOwnerName);
        $oauthToken->setCreatedAt(1000);

        $this->assertTrue($oauthToken->isExpired());

        $session = $this->getSession($this->client);
        $session->set('_security_hwi_context', serialize($oauthToken));
        $this->saveSession($this->client, $session);

        return $session;
    }

    private function assertAmountOfHttpCalls(int $amount): void
    {
        if (!method_exists($this->httpClient, 'getRequestsCount')) {
            return;
        }

        $this->assertSame($amount, $this->httpClient->getRequestsCount());
    }
}
