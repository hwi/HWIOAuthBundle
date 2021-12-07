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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AppleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AppleResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = AppleResourceOwner::class;
    protected string $userResponse = <<<json
{
    "sub": "1",
    "email": "localhost@gmail.com"
}
json;

    protected array $paths = [
        'identifier' => 'sub',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'email',
    ];

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=name+email';
    protected string $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F&response_mode=form_post';

    public function testHandleRequest(): void
    {
        $resourceOwner = $this->createResourceOwner();

        $request = new Request(['test' => 'test']);

        $this->assertFalse($resourceOwner->handles($request));

        $request = new Request(['code' => 'test']);

        $this->assertFalse($resourceOwner->handles($request));

        $request = new Request([], ['code' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));

        $request = new Request([], ['code' => 'test', 'test' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));
    }

    public function testGetAccessTokenFailedResponse(): void
    {
        $this->expectException(AuthenticationException::class);

        $request = new Request(['code' => 'code']);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"error": {"message": "invalid"}}', 'application/json; charset=utf-8'),
            ]
        );
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testDisplayPopup(): void
    {
        $resourceOwner = $this->createResourceOwner(['display' => 'popup']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=name+email&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&response_mode=form_post',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
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

        /** @var CustomUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'id_token' => '.'.base64_encode($this->userResponse)]);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo', $userResponse->getFirstName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertEquals('BAR', $userResponse->getLastName());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformation(): void
    {
        $token = '.'.base64_encode($this->userResponse);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation([
            'access_token' => 'token',
            'id_token' => $token,
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('localhost@gmail.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('Test', $userResponse->getFirstName());
        $this->assertEquals('User', $userResponse->getLastName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());

        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'id_token' => $token]);
        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('localhost@gmail.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getFirstName());
        $this->assertNull($userResponse->getLastName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined index id_token');

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );
        $resourceOwner->getUserInformation(['access_token' => 'token']);
    }
}
