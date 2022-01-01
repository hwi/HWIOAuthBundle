<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Http;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use HWI\Bundle\OAuthBundle\Tests\Functional\AuthenticationHelperTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @group legacy
 */
class RefreshOnExpireTest extends WebTestCase
{
    use AuthenticationHelperTrait;

    private KernelBrowser $client;

    private MockObject $resourceOwnerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $container = $this->client->getContainer();

        $this->createResourceOwnerMock($container);
    }

    public function testTokenIsValid()
    {
        $token = $this->loginUserWithToken([
            'expires' => 666,
            'refresh_token' => 'refresh_token',
        ]);

        $this->resourceOwnerMock->expects($this->never())->method('refreshAccessToken');

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        /** @var CustomOAuthToken $newToken */
        $newToken = $this->getCurrentToken();
        $this->assertInstanceOf(CustomOAuthToken::class, $newToken);
        $this->assertEquals($token->getCreatedAt(), $newToken->getCreatedAt());
    }

    public function testTokenIsExpiredSuccessfulRefresh()
    {
        $token = $this->loginUserWithToken([
            'expires' => 20,
            'refresh_token' => 'refresh_token',
        ]);

        $this->resourceOwnerMock->expects($this->once())
            ->method('refreshAccessToken')
            ->willReturn([
               'access_token' => 'access_token',
               'expires' => 666,
               'refresh_token' => 'refresh_token',
           ]);

        $this->resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->willReturn(new PathUserResponse());

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        /** @var CustomOAuthToken $newToken */
        $newToken = $this->getCurrentToken();
        $this->assertInstanceOf(CustomOAuthToken::class, $newToken);
        $this->assertNotEquals($token->getCreatedAt(), $newToken->getCreatedAt()); // token refreshed
    }

    public function testTokenIsExpiredForwardToLogin()
    {
        $this->loginUserWithToken([
            'expires' => 20, // expired
            'refresh_token' => 'refresh_token',
        ]);

        $this->resourceOwnerMock->expects($this->once())
            ->method('refreshAccessToken')
            ->willThrowException(new AuthenticationException());

        $this->resourceOwnerMock->expects($this->never())
            ->method('getUserInformation');

        $this->client->request('GET', '/private');

        $this->assertResponseRedirects('http://localhost/login');
        $newToken = $this->getCurrentToken();
        $this->assertUserNotLogged($newToken);
    }

    public function testTokenIsExpiredSeamlessLogout()
    {
        $this->loginUserWithToken([
            'expires' => 20, // expired
            'refresh_token' => 'refresh_token',
        ]);

        $this->resourceOwnerMock->expects($this->once())
            ->method('refreshAccessToken')
            ->willThrowException(new AuthenticationException());

        $this->resourceOwnerMock->expects($this->never())
            ->method('getUserInformation');

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $newToken = $this->getCurrentToken();
        $this->assertUserNotLogged($newToken);
    }

    public function testDoNotTryRefreshIfTokenDoesNotContainsIt()
    {
        $token = $this->loginUserWithToken([
            'expires' => 20, // expired
            'refresh_token' => null,
        ]);

        $this->resourceOwnerMock->expects($this->never())
            ->method('refreshAccessToken');

        $this->resourceOwnerMock->expects($this->never())
            ->method('getUserInformation');

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        /** @var CustomOAuthToken $newToken */
        $newToken = $this->getCurrentToken();
        $this->assertInstanceOf(CustomOAuthToken::class, $newToken);
        $this->assertEquals($token->getCreatedAt(), $newToken->getCreatedAt());
    }

    private function createResourceOwnerMock(ContainerInterface $container)
    {
        $this->resourceOwnerMock = $this->createMock(GenericOAuth2ResourceOwner::class);
        $this->resourceOwnerMock->method('shouldRefreshOnExpire')->willReturn(true);

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);
        $serviceLocatorMock->method('get')->willReturn($this->resourceOwnerMock);

        $resourceOwnerMap = new ResourceOwnerMap(
            $this->getHttpUtilsMock(),
            ['google' => '/fake'],
            ['google' => '/fake'],
            $serviceLocatorMock
        );

        $container->set('hwi_oauth.resource_ownermap.main', $resourceOwnerMap);
    }

    private function loginUserWithToken(array $tokenData): CustomOAuthToken
    {
        $session = $this->getSession($this->client);

        $token = CustomOAuthToken::createLoggedIn($tokenData);
        $token->setResourceOwnerName('google');
        if (method_exists($token, 'setAuthenticated')) {
            $token->setAuthenticated(true, false);
        }
        // if createdAt of 2 tokens is the same, the token was not refreshed either
        $token->setCreatedAt(time() - 1);

        $session->set('_security_hwi_context', serialize($token));
        $this->saveSession($this->client, $session);

        return $token;
    }

    private function getHttpUtilsMock(): HttpUtils
    {
        return $this->createMock(HttpUtils::class);
    }

    /**
     * @return TokenInterface|CustomOAuthToken|null
     */
    private function getCurrentToken(): ?TokenInterface
    {
        return $this->client->getContainer()->get('security.token_storage')->getToken();
    }

    private function assertUserNotLogged(?TokenInterface $newToken, string $message = ''): void
    {
        $this->assertTrue(
            null === $newToken || class_exists(AnonymousToken::class) && $newToken instanceof AnonymousToken,
            $message
        );
    }
}
