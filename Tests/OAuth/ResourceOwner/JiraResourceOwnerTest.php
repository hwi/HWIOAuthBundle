<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use Http\Discovery\MessageFactoryDiscovery;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\JiraResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\Security\Http\HttpUtils;

class JiraResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $resourceOwnerClass = JiraResourceOwner::class;
    protected $userResponse = '{"name": "asm89", "displayName": "Alexander"}';
    protected $paths = array(
        'identifier' => 'name',
        'nickname' => 'name',
        'realname' => 'displayName',
    );

    public function testGetUserInformation()
    {
        $this
            ->httpClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(function ($method, $uri, array $headers = [], $body = null) {
                $headers += array(
                    'Content-Type' => 'application/json; charset=utf-8',
                );

                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $headers,
                        $this->userResponse
                    )
                ;
            }));

        $accessToken = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('asm89', $userResponse->getUsername());
        $this->assertEquals('asm89', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this
            ->httpClient->expects($this->at(0))
            ->method('send')
            ->will($this->returnCallback(function ($method, $uri, array $headers = [], $body = null) {
                $headers += array(
                    'Content-Type' => 'application/json; charset=utf-8',
                );

                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $headers,
                        $this->userResponse
                    )
                ;
            }))
        ;
        $this
            ->httpClient->expects($this->at(1))
            ->method('send')
            ->will($this->returnCallback(function ($method, $uri, array $headers = [], $body = null) {
                $headers += array(
                    'Content-Type' => 'text/plain',
                );

                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $headers,
                        ''
                    )
                ;
            }))
        ;

        /** @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                array(
                    // Used in option resolver to adjust all URLs that could be called
                    'base_url' => 'http://localhost/',

                    // This is to prevent errors with not existing .pem file
                    'signature_method' => 'PLAINTEXT',
                ),
                $options
            )
        );
    }
}
