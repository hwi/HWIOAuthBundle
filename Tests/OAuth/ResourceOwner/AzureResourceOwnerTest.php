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

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AzureResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;

class AzureResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = AzureResourceOwner::class;
    protected bool $csrf = true;

    protected $userResponse = <<<json
{
    "sub": "1",
    "given_name": "Dummy",
    "family_name": "Tester",
    "name": "Dummy Tester",
    "unique_name": "dummy123"
}
json;

    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'unique_name',
        'realname' => ['given_name', 'family_name'],
        'email' => ['upn', 'email'],
        'profilepicture' => null,
    ];

    protected $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F&resource=https%3A%2F%2Fgraph.windows.net';
    protected $authorizationUrlParams = ['resource' => 'https://graph.windows.net'];

    public function testGetUserInformation()
    {
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
            'id_token' => '.'.base64_encode($this->userResponse),
        ]);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('Dummy Tester', $userResponse->getRealName());
        $this->assertEquals('Dummy', $userResponse->getFirstName());
        $this->assertEquals('Tester', $userResponse->getLastName());
        $this->assertEquals('dummy123', $userResponse->getNickname());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
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
            'id_token' => '.'.base64_encode($this->userResponse),
        ]);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('foo', $userResponse->getFirstName());
        $this->assertEquals('BAR', $userResponse->getLastName());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $this->expectException(HttpTransportException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('', null, 401),
            ]
        );
        $resourceOwner->getUserInformation(['access_token' => 'token', 'id_token' => '.'.base64_encode($this->userResponse)]);
    }
}
