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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SensioConnectResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse;

class SensioConnectResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = SensioConnectResourceOwner::class;

    public function setUp()
    {
        parent::setUp();

        $this->userResponse = file_get_contents(__DIR__.'/../../Fixtures/sensioconnect_response.xml');
    }

    public function testGetUserInformation()
    {
        $class = SensioConnectUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this->mockHttpClient($this->userResponse);

        /**
         * @var SensioConnectUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('aa5e22b0-6189-4113-9c68-91d4a3c32b7c', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('Fake Guy', $userResponse->getRealName());
        $this->assertEquals('fake@email.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
    }
}
