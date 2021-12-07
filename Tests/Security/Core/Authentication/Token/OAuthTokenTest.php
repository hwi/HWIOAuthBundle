<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Core\Authentication\Token;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class OAuthTokenTest extends TestCase
{
    private OAuthToken $token;

    protected function setUp(): void
    {
        $this->token = new OAuthToken('access_token', ['ROLE_TEST']);
        $this->token->setResourceOwnerName('github');
        $this->token->setUser(new User());
    }

    public function testGets(): void
    {
        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
        ];
        $token = new OAuthToken($expectedToken, ['ROLE_TEST']);
        $token->setResourceOwnerName('github');

        $this->assertEquals($expectedToken, $token->getRawToken());
        $this->assertEquals($expectedToken['access_token'], $token->getAccessToken());
        $this->assertEquals($expectedToken['refresh_token'], $token->getRefreshToken());
        $this->assertEquals($expectedToken['expires_in'], $token->getExpiresIn());
        $this->assertEquals('github', $token->getResourceOwnerName());
    }

    public function testIsAuthenticated(): void
    {
        $this->assertInstanceOf(UserInterface::class, $this->token->getUser());
    }

    public function testGetSetResourceOwnerName(): void
    {
        $this->assertEquals('github', $this->token->getResourceOwnerName());
        $this->token->setResourceOwnerName('foobar');
        $this->assertEquals('foobar', $this->token->getResourceOwnerName());
    }

    public function testSerialization(): void
    {
        /**
         * @var OAuthToken
         */
        $token = unserialize(serialize($this->token));

        $this->assertEquals('access_token', $token->getAccessToken());
        $this->assertEquals('github', $token->getResourceOwnerName());
    }

    public function testSerializationOfOAuth1Token(): void
    {
        $oauth1Token = new OAuthToken([
            'oauth_token' => 'oauth1_access_token',
            'oauth_token_secret' => 'oauth1_token_secret',
        ], ['ROLE_TEST']);

        $oauth1Token->setResourceOwnerName('twitter');

        $oauth1Token = unserialize(serialize($oauth1Token));

        $this->assertEquals('oauth1_access_token', $oauth1Token->getAccessToken());
        $this->assertEquals('oauth1_token_secret', $oauth1Token->getTokenSecret());
        $this->assertEquals('twitter', $oauth1Token->getResourceOwnerName());
    }

    public function testIsExpired(): void
    {
        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
        ];
        $token = new OAuthToken($expectedToken, ['ROLE_TEST']);

        $this->assertFalse($token->isExpired());

        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '29',
        ];
        $token = new OAuthToken($expectedToken, ['ROLE_TEST']);
        $this->assertTrue($token->isExpired());
    }

    public function testSerializeTokenInException(): void
    {
        $resourceOwnerName = 'github';

        $exception = new AccountNotLinkedException();
        $exception->setToken($this->token);
        $exception->setResourceOwnerName($resourceOwnerName);

        $processed = new AccountNotLinkedException();
        $processed->__unserialize($exception->__serialize());

        $this->assertEquals($this->token, $processed->getToken());
        $this->assertEquals($resourceOwnerName, $processed->getResourceOwnerName());
    }
}
