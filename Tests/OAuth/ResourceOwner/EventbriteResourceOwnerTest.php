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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\EventbriteResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;

class EventbriteResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = EventbriteResourceOwner::class;
    protected $userResponse = <<<json
{
    "user": {
        "user_id": "1",
        "first_name": "bar",
        "last_name": "foo"
    }
}
json;

    protected $paths = [
        'identifier' => 'user.user_id',
        'nickname' => 'user.first_name',
        'firstname' => 'user.first_name',
        'lastname' => 'user.last_name',
        'realname' => ['user.first_name', 'user.last_name'],
        'email' => 'email',
    ];

    public function testGetUserInformationFirstAndLastName()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('bar', $userResponse->getFirstName());
        $this->assertEquals('foo', $userResponse->getLastName());
    }
}
