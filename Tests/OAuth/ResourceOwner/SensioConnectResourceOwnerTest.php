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

class SensioConnectResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    public function setUp()
    {
        parent::setUp();

        $this->userResponse = file_get_contents(__DIR__.'/../../Fixtures/sensioconnect_response.xml');
    }

    public function testGetUserInformation()
    {
        $class         = '\HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz($this->userResponse);

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation('access_token');

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('aa5e22b0-6189-4113-9c68-91d4a3c32b7c', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('Fake Guy', $userResponse->getRealName());
        $this->assertEquals('fake@email.com', $userResponse->getEmail());
        $this->assertEquals('access_token', $userResponse->getAccessToken());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://connect.sensiolabs.com/oauth/authorize',
                 'access_token_url'    => 'https://connect.sensiolabs.com/oauth/access_token',
                 'infos_url'           => 'https://connect.sensiolabs.com/api',

                 'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse',
            ),
            $options
        );

        return new SensioConnectResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
