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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use Symfony\Component\HttpFoundation\Request;

class FacebookResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockBuzz('{"error": {"message": "invalid"}}', 'application/json; charset=utf-8');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner('facebook', array('display' => 'popup'));
        $this->assertEquals('popup', $resourceOwner->getOption('display'));
        $this->assertEquals(
            $this->options['authorization_url'] . '&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $this->mockBuzz('true', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->mockBuzz('false', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }
    
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://www.facebook.com/dialog/oauth',
                'access_token_url'    => 'https://graph.facebook.com/oauth/access_token',
                'infos_url'           => 'https://graph.facebook.com/me',
            ),
            $options
        );

        return new FacebookResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
