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
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;

class AppleResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = AppleResourceOwner::class;
    protected $userResponse = <<<json
{
    "sub": "1", 
    "email": "localhost@gmail.com"
}
json;

    protected $paths = [
        'identifier' => 'sub',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'email',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=name+email';
    protected $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F&response_mode=form_post';

    public function testHandleRequest()
    {
        $request = new Request(['test' => 'test']);

        $this->assertFalse($this->resourceOwner->handles($request));

        $request = new Request(['code' => 'test']);

        $this->assertFalse($this->resourceOwner->handles($request));

        $request = new Request([], ['code' => 'test']);

        $this->assertTrue($this->resourceOwner->handles($request));

        $request = new Request([], ['code' => 'test', 'test' => 'test']);

        $this->assertTrue($this->resourceOwner->handles($request));
    }

    public function testGetAccessTokenFailedResponse()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('{"error": {"message": "invalid"}}', 'application/json; charset=utf-8');
        $request = new Request(['code' => 'code']);

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['display' => 'popup']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=name+email&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&response_mode=form_post',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $this->httpResponseHttpCode = 200;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->httpResponseHttpCode = 401;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['user_response_class' => $class]);

        $token = '.'.base64_encode($this->userResponse);

        /** @var CustomUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'id_token' => $token]);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo', $userResponse->getFirstName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertEquals('BAR', $userResponse->getLastName());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformation()
    {
        $token = '.'.base64_encode($this->userResponse);

        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation([
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

        $userResponse = $this->resourceOwner->getUserInformation(['access_token' => 'token', 'id_token' => $token]);
        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('localhost@gmail.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getFirstName());
        $this->assertNull($userResponse->getLastName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $exception = new \Exception('Undefined index id_token');

        try {
            $this->resourceOwner->getUserInformation(['access_token' => 'token']);
            $this->fail('An exception should have been raised');
        } catch (\Exception $e) {
            $this->assertSame($exception->getMessage(), $e->getMessage());
        }
    }
}
