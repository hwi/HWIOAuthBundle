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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\MailruResourceOwner;
use Symfony\Component\HttpFoundation\Request;

/**
 * MailruResourceOwnerTest
 *
 * @author Gregory <gridsane@gmail.com>
 */
class MailruResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "uid": "1",
    "first_name": "",
    "last_name": "1",
    "nick": "bar",
    "email": "foobar@mail.ru",
    "profilepicture": "http://mail.ru/picture.png"
}
json;

    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'nick',
        'realname'   => array('last_name', 'first_name'),
    );

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'x_mailru_vid' => 1
        ));

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
        $userResponse = $resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'x_mailru_vid' => 1
        ));

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
                'authorization_url'   => 'https://connect.mail.ru/oauth/authorize',
                'access_token_url'    => 'https://connect.mail.ru/oauth/token',
                'infos_url'           => 'http://www.appsmail.ru/platform/api?method=users.getInfo',
                'client_private'      => 'private_key',
            ),
            $options
        );

        return new MailruResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
