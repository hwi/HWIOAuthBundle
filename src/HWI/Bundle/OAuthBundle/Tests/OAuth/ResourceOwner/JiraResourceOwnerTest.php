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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\JiraResourceOwner;

class JiraResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = '{"name": "asm89", "displayName": "Alexander"}';
    protected $paths        = array(
        'identifier' => 'name',
        'nickname'   => 'name',
        'realname'   => 'displayName',
    );

    public function testGetUserInformation()
    {
        $this->markTestSkipped('This tests needs to be fixed.');

        $this
            ->buzzClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')))
        ;
        $this->buzzResponse = $this->userResponse;
        $this->buzzResponseContentType = 'application/json; charset=utf-8';

        $accessToken  = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('asm89', $userResponse->getUsername());
        $this->assertEquals('asm89', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $this->markTestSkipped('This tests needs to be fixed.');

        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this
            ->buzzClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')))
        ;
        $this->buzzResponse = '';
        $this->buzzResponseContentType = 'text/plain';

        /** @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                // Used in option resolver to adjust all URLs that could be called
                'base_url'         => 'http://localhost/',

                // This is to prevent errors with not existing .pem file
                'signature_method' => 'PLAINTEXT',
            ),
            $options
        );

        return new JiraResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
