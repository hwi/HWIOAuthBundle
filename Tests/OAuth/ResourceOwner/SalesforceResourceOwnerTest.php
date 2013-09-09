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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SalesforceResourceOwner;

class SalesforceResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "user_id": "1",
    "nick_name": "bar",
    "email": "baz"
}
json;

    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'nick_name',
        'realname'   => 'nick_name',
        'email'      => 'email',
    );

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token','id'=>"someuser"));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
    
    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz();
        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token','id'=>'test'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
    
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://login.salesforce.com/services/oauth2/authorize',
                'access_token_url'    => 'https://login.salesforce.com/services/oauth2/token',
            ),
            $options
        );

        return new SalesforceResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
    
}
