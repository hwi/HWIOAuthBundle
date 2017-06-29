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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FoursquareResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;

class FoursquareResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = FoursquareResourceOwner::class;
    protected $userResponse = <<<json
{
    "response": {
        "user": {
            "id": "1",
            "firstName": "bar",
            "lastName": "foo"
        }
    }
}
json;

    protected $paths = array(
        'identifier' => 'response.user.id',
        'firstname' => 'response.user.firstName',
        'lastname' => 'response.user.lastName',
        'nickname' => 'response.user.firstName',
        'realname' => array('response.user.firstName', 'response.user.lastName'),
    );

    public function testGetUserInformationFirstAndLastName()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('bar', $userResponse->getFirstName());
        $this->assertEquals('foo', $userResponse->getLastName());
    }
}
