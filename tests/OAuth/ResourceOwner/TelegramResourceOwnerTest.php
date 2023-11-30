<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TelegramResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\LazyResponseException;

/**
 * @author zorn-v
 */
final class TelegramResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = TelegramResourceOwner::class;

    protected array $options = [
        'client_id' => 'clientid',
        'client_secret' => 'client:secret',

        'authorization_url' => 'http://user.auth/?test=2',
    ];

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
    ];

    protected array $tokenData = ['access_token' => 'eyJpZCI6MSwidXNlcm5hbWUiOiJiYXIifQ=='];

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&bot_id=client&origin=http%3A%2F%2Fredirect.to%2F';
    protected string $redirectUrlPart = '&return_to=http%3A%2F%2Fredirect.to%2F';

    public function testHandleRequest(): void
    {
        $resourceOwner = $this->createResourceOwner();

        $request = new Request(['code' => 'test']);
        $this->assertTrue($resourceOwner->handles($request));

        $request = new Request(['code' => 'test', 'test' => 'test']);
        $this->assertTrue($resourceOwner->handles($request));

        $request = new Request(['test' => 'test']);
        $this->expectException(LazyResponseException::class);
        $resourceOwner->handles($request);
    }

    public function testGetAccessToken(string $response = '', string $contentType = ''): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response, $contentType),
            ]
        );
        $token = $this->getAuthToken(['id' => 1, 'auth_date' => time()], $this->options['client_secret']);

        $request = new Request(['code' => $token]);

        $this->assertEquals(
            $token,
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenExpired(string $response = '', string $contentType = ''): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response, $contentType),
            ]
        );
        $token = $this->getAuthToken(['id' => 1, 'auth_date' => 123], $this->options['client_secret']);

        $request = new Request(['code' => $token]);

        $this->expectExceptionMessage('Telegram auth data expired');
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenInvalidHash(string $response = '', string $contentType = ''): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response, $contentType),
            ]
        );
        $token = $this->getAuthToken(['id' => 1, 'auth_date' => time()], 'invalid');

        $request = new Request(['code' => $token]);

        $this->expectExceptionMessage('Telegram auth data check failed');
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testRefreshAccessToken($response = '', $contentType = ''): void
    {
        $this->markTestSkipped('There is no refresh tokens');
    }

    public function testRefreshAccessTokenInvalid(string $response = '', string $exceptionClass = ''): void
    {
        $this->markTestSkipped('There is no refresh tokens');
    }

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var AbstractUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($this->tokenData['access_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testInvalidOptionValueThrowsException(): void
    {

    }

    public function testGetUserInformationFailure(): void
    {
        $this->markTestSkipped('There is no extra http request for get user information');
    }

    public function testGetAuthorizationUrlWithEnabledCsrf(): void
    {
        $this->markTestSkipped('No CSRF is available for this Resource Owner.');
    }

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals($this->tokenData['access_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    private function getAuthToken(array $authData, string $secret)
    {
        ksort($authData);
        $dataStr = '';
        foreach ($authData as $k => $v) {
            $dataStr .= sprintf("\n%s=%s", $k, $v);
        }
        $dataStr = substr($dataStr, 1);
        $secretKey = hash('sha256', $secret, true);
        $authData['hash'] = hash_hmac('sha256', $dataStr, $secretKey);

        return base64_encode(json_encode($authData));
    }
}
