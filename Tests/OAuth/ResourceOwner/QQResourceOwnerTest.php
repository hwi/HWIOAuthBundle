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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\QQResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

final class QQResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = QQResourceOwner::class;
    protected string $userResponse = <<<json
{
    "ret": 0,
    "openid": "1",
    "nickname": "bar",
    "figureurl_qq_1": "http://q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/40"
}
json;
    protected array $paths = [
        'identifier' => 'openid',
        'nickname' => 'nickname',
        'realname' => 'nickname',
        'profilepicture' => 'figureurl_qq_1',
    ];

    /**
     * {@inheritdoc}
     */
    public function testGetUserInformation(): void
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
        $userResponse = $resourceOwner->getUserInformation($this->tokenData, ['openid' => '1']);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    /**
     * {@inheritdoc}
     */
    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse('{"ret": 0}'),
            ]
        );

        /** @var CustomUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData, ['openid' => '1']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure(): void
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', null, 401),
            ]
        );

        $resourceOwner->getUserInformation($this->tokenData, ['openid' => '1']);
    }

    /**
     * QQ returns access token in jsonp format.
     */
    public function testGetAccessTokenJsonpResponse(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('callback({"access_token": "code"});'),
            ]
        );

        $request = new Request(['code' => 'somecode']);

        $this->assertEquals(
            ['access_token' => 'code'],
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    /**
     * QQ returns errors in jsonp format.
     */
    public function testGetAccessTokenErrorResponse(): void
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('callback({"error": 1, "msg": "error"})'),
            ]
        );

        $request = new Request(['code' => 'code']);

        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                [
                    'authorization_url' => 'https://graph.qq.com/oauth2.0/authorize?format=json',
                    'access_token_url' => 'https://graph.qq.com/oauth2.0/token',
                    'infos_url' => 'https://graph.qq.com/user/get_user_info',
                    'me_url' => 'https://graph.qq.com/oauth2.0/me',
                ],
                $options
            ),
            $responses
        );
    }
}
