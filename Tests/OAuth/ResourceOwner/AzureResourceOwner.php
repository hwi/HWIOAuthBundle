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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AzureResourceOwner;

class AzureResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $csrf = true;

    protected $userResponse = <<<json
{
    "sub": "1",
    "given_name": "Dummy",
    "family_name": "Tester",
    "name": "Dummy Tester",
    "unique_name": "dummy123"
}
json;

    protected $paths = array(
        'identifier'     => 'sub',
        'nickname'       => 'unique_name',
        'realname'       => array('given_name', 'family_name'),
        'email'          => array('upn', 'email'),
        'profilepicture' => null,
    );

    protected $expectedUrls = array(
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&resource=https%3A%2F%2Fgraph.windows.net',
    );

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'] .'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&resource=https%3A%2F%2Fgraph.windows.net',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetUserInformation()
    {
        $token = '.' . base64_encode($this->userResponse);
        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'id_token' =>  $token
        ));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('Dummy Tester', $userResponse->getRealName());
        $this->assertEquals('dummy123', $userResponse->getNickname());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
    
    public function testCustomResponseClass()
    {
        $this->markTestSkipped('Can\' test custom response because of the way the id_token value is set; is always returning null');

        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this->mockBuzz();

        $token = base64_encode($this->userResponse) . '.';

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'id_token' =>  $token
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
                'resource' => 'https://graph.windows.net'
            ),
            $options
        );
        return new AzureResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
