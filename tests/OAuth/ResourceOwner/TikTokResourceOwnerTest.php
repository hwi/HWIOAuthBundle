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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TikTokResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Request;

final class TikTokResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = TikTokResourceOwner::class;
    protected string $userResponse = <<<json
{
    "data": {
        "user": {
            "open_id": "723f24d7-e717-40f8-a2b6-cb8464cd23b4",
            "union_id": "c9c60f44-a68e-4f5d-84bb-ce53e8ea7b5e",
            "avatar_url": "https://p16-sign.tiktokcdn-us.com/tos-useast5-avt-0068-tx/avatar.jpeg",
            "display_name": "Nelly"
        }
    },
    "error": {
        "code": "ok",
        "message": "",
        "log_id": "20261102120000010C6A00E9D4FE2A2EEC"
    }
}
json;

    protected array $paths = [
        'identifier' => 'data.user.open_id',
        'nickname' => 'data.user.display_name',
        'realname' => 'data.user.display_name',
        'profilepicture' => 'data.user.avatar_url',
    ];

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_key=clientid&scope=user.info.basic';

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var AbstractUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('723f24d7-e717-40f8-a2b6-cb8464cd23b4', $userResponse->getUserIdentifier());
        $this->assertEquals('Nelly', $userResponse->getNickname());
        $this->assertEquals('Nelly', $userResponse->getRealName());
        $this->assertEquals('https://p16-sign.tiktokcdn-us.com/tos-useast5-avt-0068-tx/avatar.jpeg', $userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationRequestsTheFields(): void
    {
        $this->httpClient = new MockHttpClient(function (string $method, string $url, array $options): \Symfony\Component\HttpClient\Response\MockResponse {
            $this->assertEquals('GET', $method);
            $this->assertEquals('http://user.info/?test=1&fields=open_id%2Cunion_id%2Cavatar_url%2Cdisplay_name', $url);
            $this->assertContains('Authorization: Bearer token', $options['headers']);

            return $this->createMockResponse($this->userResponse);
        });

        $this->createResourceOwner()->getUserInformation($this->tokenData);
    }

    public function testGetAccessTokenSendsTheClientKey(): void
    {
        $this->httpClient = new MockHttpClient(function (string $method, string $url, array $options): \Symfony\Component\HttpClient\Response\MockResponse {
            $this->assertEquals('POST', $method);
            $this->assertEquals('http://user.access/?test=3', $url);
            $this->assertEquals(
                'code=somecode&grant_type=authorization_code&redirect_uri=http%3A%2F%2Fredirect.to%2F&client_secret=clientsecret&client_key=clientid',
                $options['body']
            );
            $this->assertNotContains('Authorization: Basic '.base64_encode('clientid:clientsecret'), $options['headers']);

            return $this->createMockResponse('{"access_token": "code", "open_id": "723f24d7-e717-40f8-a2b6-cb8464cd23b4"}');
        });

        $this->assertEquals(
            ['access_token' => 'code', 'open_id' => '723f24d7-e717-40f8-a2b6-cb8464cd23b4'],
            $this->createResourceOwner()->getAccessToken(new Request(['code' => 'somecode']), 'http://redirect.to/')
        );
    }

    public function testRevokeToken(): void
    {
        $this->httpClient = new MockHttpClient(function (string $method, string $url, array $options): \Symfony\Component\HttpClient\Response\MockResponse {
            $this->assertEquals('POST', $method);
            $this->assertEquals('https://open.tiktokapis.com/v2/oauth/revoke/', $url);
            $this->assertEquals('client_key=clientid&client_secret=clientsecret&token=token', $options['body']);

            return $this->createMockResponse('{}');
        });

        $this->assertTrue($this->createResourceOwner()->revokeToken('token'));
    }
}
