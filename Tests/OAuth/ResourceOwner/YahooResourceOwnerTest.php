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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YahooResourceOwner;

class YahooResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "profile": {
        "guid": "1",
        "nickname": "bar"
    }
}
json;
    protected $paths = array(
        'identifier' => 'profile.guid',
        'nickname'   => 'profile.nickname',
        'realname'   => 'profile.givenName',
    );

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        $accessToken  = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'xoauth_yahoo_guid' => 1);
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken, $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getOAuthToken());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth1', array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'xoauth_yahoo_guid' => 1));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://api.login.yahoo.com/oauth/v2/request_auth',
                'request_token_url'   => 'https://api.login.yahoo.com/oauth/v2/get_request_token',
                'access_token_url'    => 'https://api.login.yahoo.com/oauth/v2/get_token',
                'infos_url'           => 'http://social.yahooapis.com/v1/user/{guid}/profile',
                'realm'               => 'yahooapis.com',
            ),
            $options
        );

        return new YahooResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
