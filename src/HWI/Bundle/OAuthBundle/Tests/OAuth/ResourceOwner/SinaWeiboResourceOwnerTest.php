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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SinaWeiboResourceOwner;

class SinaWeiboResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "screen_name": "bar",
    "profile_image_url": "http://tp1.sinaimg.cn/1404376560/50/0/1"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'screen_name',
        'realname' => 'screen_name',
        'profilepicture' => 'profile_image_url',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new SinaWeiboResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse);

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token', 'uid' => '1'));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
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
            $this->resourceOwner->getUserInformation(array('access_token' => 'token', 'uid' => 'someuser'));
            $this->fail('An exception should have been raised');
        } catch (HttpTransportException $e) {
            $this->assertSame($exception, $e->getPrevious());
        }
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token', 'uid' => '1'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
