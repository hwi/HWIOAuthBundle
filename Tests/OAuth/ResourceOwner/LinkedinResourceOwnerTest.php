<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;

class LinkedinResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = LinkedinResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "firstName": {
      "localized": {
        "en_US": "John"
      },
      "preferredLocale": {
        "country": "US",
        "language": "en"
      }
    },
    "lastName": {
      "localized": {
        "en_US": "Smith"
      },
      "preferredLocale": {
        "country": "US",
        "language": "en"
      }
    },
    "emailAddress": "example@website.com"
}
json;
    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'emailAddress',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'emailAddress',
        'profilepicture' => 'profilePicture',
    ];
    protected $csrf = true;

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=r_liteprofile+r_emailaddress';

    protected $httpClientCalls = 1;

    public function testCustomResponseClass()
    {
        $this->httpClientCalls = 2;

        parent::testCustomResponseClass();

        $this->httpClientCalls = 1;
    }

    public function testGetUserInformation()
    {
        $this->httpClientCalls = 2;

        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /** @var $userResponse AbstractUserResponse */
        $userResponse = $this->resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('example@website.com', $userResponse->getNickname());
        $this->assertEquals('John', $userResponse->getFirstName());
        $this->assertEquals('Smith', $userResponse->getLastName());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());

        $this->httpClientCalls = 1;
    }
}
