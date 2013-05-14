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

    public function testGetBaseUrlOption()
    {
        $this->assertEquals('www.fake.url', $this->resourceOwner->getOption('base_url'));
    }

    public function testGetUserInformation()
    {
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
        $this->assertEquals($accessToken, $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getOAuthToken());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth1', array('user_response_class' => $class));

        $this
            ->buzzClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')))
        ;
        $this->buzzResponse = '';
        $this->buzzResponseContentType = 'text/plain';

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => '{base_url}/plugins/servlet/oauth/authorize',
                'request_token_url'   => '{base_url}/plugins/servlet/oauth/request-token',
                'access_token_url'    => '{base_url}/plugins/servlet/oauth/access-token',
                'infos_url'           => '{base_url}/rest/api/2/user',

                'signature_method'    => 'RSA-SHA1',

                'base_url'            => 'www.fake.url',
            ),
            $options
        );

        return new JiraResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
