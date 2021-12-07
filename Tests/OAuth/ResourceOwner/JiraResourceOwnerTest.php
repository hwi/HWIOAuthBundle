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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\JiraResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth1ResourceOwnerTestCase;
use Symfony\Component\Security\Http\HttpUtils;

final class JiraResourceOwnerTest extends GenericOAuth1ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = JiraResourceOwner::class;
    protected string $userResponse = '{"name": "asm89", "displayName": "Alexander"}';
    protected array $paths = [
        'identifier' => 'name',
        'nickname' => 'name',
        'realname' => 'displayName',
    ];

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
                $this->createMockResponse($this->userResponse),
            ]
        );

        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'];
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('asm89', $userResponse->getUsername());
        $this->assertEquals('asm89', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
                $this->createMockResponse('', 'text/plain'),
            ]
        );

        /** @var CustomUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                [
                    // Used in option resolver to adjust all URLs that could be called
                    'base_url' => 'http://localhost/',

                    // This is to prevent errors with not existing .pem file
                    'signature_method' => 'PLAINTEXT',
                ],
                $options
            ),
            $responses
        );
    }
}
