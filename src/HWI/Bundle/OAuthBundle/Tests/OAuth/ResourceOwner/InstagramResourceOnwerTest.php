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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\InstagramResourceOwner;

class InstagramResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "data": {
        "id":  "1",
        "username": "bar"
    }
}
json;
    protected $paths = array(
        'identifier'      => 'data.id',
        'nickname'        => 'data.username',
    );

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        /* @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new InstagramResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
