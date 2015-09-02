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

use Buzz\Exception\RequestException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SalesforceResourceOwner;

class SalesforceResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "user_id": "1",
    "nick_name": "bar",
    "email": "baz",
    "photos": {
        "picture": "url"
    }
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
        $this->assertEquals('url', $userResponse->getProfilePicture());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $exception = new RequestException();

        $this->buzzClient->expects($this->once())
            ->method('send')
            ->will($this->throwException($exception));

        try {
            $this->resourceOwner->getUserInformation(array('access_token' => 'token', 'id' => 'someuser'));
            $this->fail('An exception should have been raised');
        } catch (HttpTransportException $e) {
            $this->assertSame($exception, $e->getPrevious());
        }
    }

    public function testCustomResponseClass()
    {
        /* not necessary for salesforce */
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new SalesforceResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
